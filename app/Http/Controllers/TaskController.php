<?php namespace Voucher\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Voucher\Notification\VoucherNotification;
use Voucher\Validators\VoucherValidator;
use Log;
use Notification;
use Voucher\Repositories\VoucherJobParamMetaDatasRepository;
use Voucher\Repositories\VoucherJobsRepository;
use Voucher\Repositories\VouchersRepository;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Config;
use Voucher\Repositories\VoucherCodesRepository;

class TaskController extends Controller
{
    protected $sqs_worker;

    protected $voucher_jobs_repo;

    protected $voucher_jobs_params_repo;

    protected $voucher_repo;

    protected $voucher_codes_repo;

    public function __construct(

        Request $request,

        VouchersRepository $voucher_repo,

        VoucherJobsRepository $voucher_jobs_repo,

        VoucherJobParamMetadatasRepository $voucher_jobs_params_repo,

        VoucherCodesRepository $voucher_codes_repo
    )
    {
        parent::__construct($request);

        $this->voucher_jobs_repo = $voucher_jobs_repo;

        $this->voucher_jobs_params_repo = $voucher_jobs_params_repo;

        $this->voucher_repo = $voucher_repo;

        $this->voucher_codes_repo = $voucher_codes_repo;
    }

    /**
     * This method will be use to issue sets of voucher codes to providers.
     * It basically collects sets of codes from the voucher_codes table (and copy to vouchers table),
     * generates a csv file with the codes and uploads the csv to S3.
     *
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function issueVouchers()
    {
        try {
            $jobs = $this->voucher_jobs_repo->getJobs();

            if (count($jobs['data']) == 0) {
                Log::info(SELF::LOGTITLE, array_merge(
                    ['success' => 'No voucher jobs to process'],
                    $this->log
                ));
                return $this->respondSuccess('No voucher jobs to process');
            }
        } catch (\Exception $e) {
            Log::error(SELF::LOGTITLE, array_merge(
                ['error' => $e->getMessage()],
                $this->log
            ));
            return $this->errorInternalError($e->getMessage());
        }

        try {
            foreach ($jobs['data'] as $job) {

                $job_status = [
                    'job_id' => $job['id'],
                    'status' => 'processing'
                ];

                $this->voucher_jobs_repo->updateJobStatus($job_status);
                $job_params = $this->voucher_jobs_params_repo->getJobParameters($job);
                $params = [];

                foreach ($job_params as $job_param) {
                    $params[$job_param->key] = $job_param->value;
                }

                $this->voucher_jobs_repo->issueCodesFromVoucherCodesTableToVouchersTable($params);
                
                $loop_params = [
                    'limit' => 25000,
                    'start' => 0,
                    'job_id' => $job['id'],
                    'voucher_set' => 0
                ];

                while ($loop_params['start'] > 0) {

                    $vouchers = $this->voucher_repo->getVouchersByJobIdAndLimit($loop_params);
                    $csv_file = $this->generateCsvFromVouchers($vouchers, $loop_params['voucher_set']++);
                    $this->uploadS3($csv_file);
                    $this->notify($vouchers);

                    if (count($vouchers['data']) < $loop_params['limit']) {
                        $loop_params['start'] = -1;
                    } else {
                        $loop_params['start'] = ($loop_params['start'] + $loop_params['limit']) + 1;
                    }
                }

                $job_status = [
                    'job_id' => $job['id'],
                    'status' => 'completed'
                ];

                $this->voucher_jobs_repo->updateJobStatus($job_status);
            }

            Log::info(SELF::LOGTITLE, array_merge(
                ['success' => 'Successfully issued Voucher Codes.'],
                $this->log
            ));
            return $this->respondSuccess('Successfully issued Voucher Codes.');

        } catch (\Exception $e) {

            $job_status = [
                'job_id' => $job['id'],
                'status' => 'error',
                'comments' => $e->getMessage()
            ];

            $this->voucher_jobs_repo->updateJobStatus($job_status);
            Log::error(SELF::LOGTITLE, array_merge(
                ['error' => $e->getMessage()],
                $this->log
            ));
            return $this->errorInternalError($e->getMessage());
        }
    }

    /**
     * Generates a csv file from the issued codes.
     *
     * @param $vouchers
     * @return \Illuminate\Http\Response|string
     */
    public function generateCsvFromVouchers($vouchers, $set)
    {
        try {
            $voucher_file = $vouchers['data'][0]['title'].'_'.date('Y_m_d_H_i_s', time()). '_set_'. $set;
            $fp = fopen(storage_path('vouchers').'/'.$voucher_file.'.csv', 'w');
            fputcsv($fp, array('Voucher Code', 'Duration'));

            foreach ($vouchers['data'] as $voucher) {
                fputcsv($fp, array(
                        array(
                            $voucher['code'],
                            $voucher['duration']. ' '. $voucher['period']
                        )
                    )
                );
            }
            fclose($fp);
            return $voucher_file;
        }
        catch (\Exception $e) {
            Log::error(SELF::LOGTITLE, array_merge(
                ['error' => $e->getMessage()],
                $this->log
            ));
            return $this->errorInternalError('Error generating CSV file : '.$e->getMessage());
        }
    }

    public function uploadS3($file_name)
    {
        try {
            $bucket = getenv('AWS_S3_BUCKET');

            $file_path = storage_path('vouchers');
            $key_name = getenv('AWS_S3_BUCKET_FOLDER').'/'.$file_name;


            $s3 = new S3Client(Config::get('s3'));
            // Upload a file.
            $result = $s3->putObject(array(
                'Bucket' => $bucket,
                'Key' => $key_name,
                'SourceFile' => $file_path
            ));

            echo $result['ObjectURL'];
            //@TODO remove file from storage path after s3 uploaded successfully - Chizzy

            unlink($file_path . '/' . $file_name . '.csv');
        }
        catch (\Exception $e) {
            return ('S3 Upload error:'. $e->getMessage());
        }
    }

    public function notify($job)
    {
        try {
            //@TODO implement Voucher Notification Chizzy
            $notify = new VoucherNotification(1, 'Generate Voucher Initiated', [1]);
            $notify->__set('job_id', $job['data']['id']);
            $notify->__set('job_status', $job['data']['status']);

            Notification::send($notify);
        }
        catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function generateVoucherCodes()
    {
        $fields = $this->request->all();
        $rules = VoucherValidator::getVoucherCodeRules();
        $messages = VoucherValidator::getMessages();

        try {
            $validator = Validator::make($fields, $rules, $messages);

            if ($validator->fails()) {
                Log::error(SELF::LOGTITLE, array_merge(
                    [
                        'error' => $validator->errors()
                    ],
                    $this->log
                ));
                return $this->errorWrongArgs($validator->errors());

            } else {
                $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
                $i = 0;
                $voucher_code = '';
                while ($i < $fields['total']) {
                    for ($j = 0; $j < 8; $j++) {
                        $voucher_code .= $characters[rand(0, strlen($characters) - 1)];
                    }
                    $code = $this->voucher_codes_repo->isNotExistingVoucherCode($voucher_code);
                    if ($code) {
                        $data = ['voucher_code' => $voucher_code, 'voucher_status' => 'new'];
                        $this->voucher_codes_repo->insertVoucherCode($data);
                        $i += 1;
                    }
                    $voucher_code = '';
                }
                return $this->respondCreated(['Voucher Codes have been generated.']);
            }
        }
        catch (\Exception $e) {
            $notify = new VoucherNotification(1, 'Generate Voucher Initiated', [1]);
            $notify->error = $e->getMessage();

            Notification::send($notify);

            Log::error(SELF::LOGTITLE, array_merge(
                [
                    'error' => 'Could not generate voucher codes '. $e->getMessage()
                ],
                $this->log
            ));
        }
    }
}