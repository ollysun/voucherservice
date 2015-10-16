<?php namespace Voucher\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Voucher\Notification\VoucherNotification;
use Voucher\Notification\VoucherBusinessNotification;
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

                $params = [];
                $params['voucher_job_id'] = $job['id'];
                $params['status'] = 'processing';

                $this->voucher_jobs_repo->updateJobStatus($params);
                $job_params = $this->voucher_jobs_params_repo->getJobParams($job);

                foreach ($job_params['data'] as $job_param) {
                    $params[$job_param['key']] = $job_param['value'];
                }

                $this->voucher_jobs_repo->addVouchers($params);
                
                $loop_params = [
                    'limit' => 25000,
                    'start' => 1,
                    'voucher_job_id' => $job['id'],
                    'voucher_set' => 0
                ];

                while ($loop_params['start'] > 0) {

                    $vouchers = $this->voucher_repo->getVouchersByJobIdAndLimit($loop_params);
                    $csv_file = $this->generateCsvFromVouchers($vouchers, $loop_params['voucher_set']++);
                    $s3 = $this->uploadS3($csv_file);
                    $this->notify($s3);

                    if (count($vouchers['data']) < $loop_params['limit']) {
                        $loop_params['start'] = -1;
                    } else {
                        $loop_params['start'] = ($loop_params['limit']) + 1;
                    }
                }

                $params['status'] = 'completed';
                $this->voucher_jobs_repo->updateJobStatus($params);
            }

            Log::info(SELF::LOGTITLE, array_merge(
                ['success' => 'Successfully issued Voucher Codes.'],
                $this->log
            ));
            return $this->respondSuccess('Successfully issued Voucher Codes.');

        } catch (\Exception $e) {
            $params = [
                'voucher_job_id' => $job['id'],
                'status' => 'error',
                'comments' => $e->getMessage()
            ];

            $this->voucher_jobs_repo->updateJobStatus($params);
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
     * @param $vouchers $set
     * @return \Illuminate\Http\Response|string
     * @throws \Exception
     */
    protected function generateCsvFromVouchers($vouchers, $set)
    {
        try {
            $voucher_file = $vouchers['data'][0]['title'].'_'.date('Y_m_d_H_i_s', time()). '_Batch_'. $set;
            $fp = fopen(storage_path('vouchers').'/'.$voucher_file.'.csv', 'w');
            fputcsv($fp, array('Voucher Code', 'Duration'));

            foreach ($vouchers['data'] as $voucher) {
                fputcsv($fp,
                        array(
                            $voucher['code'],
                            $voucher['duration']. ' '. $voucher['period']
                        )

                );
            }
            fclose($fp);
            return $voucher_file;
        }
        catch (\Exception $e) {
            throw new \Exception('Error generating CSV file : '.$e->getMessage());
        }
    }

    /**
     * Uploads generated csv files to S3 bucket.
     *
     * @param $file_name
     * @return string
     * @throws \Exception
     */
    protected function uploadS3($file_name)
    {
        try {

            $bucket = getenv('AWS_S3_BUCKET');

            $filepath = storage_path('vouchers').'/'.$file_name.'.csv';
            $keyname = getenv('AWS_S3_BUCKET_FOLDER').'/'.$file_name;

            $s3 = new S3Client(Config::get('s3'));

            $result = $s3->putObject(array(
                'Bucket' => $bucket,
                'Key' => $keyname,
                'SourceFile' => $filepath
            ));

            unlink($filepath);
            return $file_name;
        }
        catch (\Exception $e) {
            throw new \Exception('S3 Upload error:'. $e->getMessage());
        }
    }

    /**
     * Sends notification about a completed voucher job.
     *
     * @param $job
     * @return bool|string
     * @throws \Exception
     */
    public function notify($s3)
    {
        try {
            $notify = new VoucherBusinessNotification(1, 'Bulk Voucher Requested Processed', [1]);//send business users user_id's
            $notify->__set('file_name', $s3);
            //$notify->__set('s3_url', $s3['s3_url']);

            Notification::send($notify);
            return true;
        } catch (\Exception $e) {
            throw new \Exception('Failed while sending notification:'. $e->getMessage());
        }
    }

    /**
     * Generates voucher codes.
     *
     * @return array|\Illuminate\Http\Response
     */
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
                $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $i = 0;
                $voucher_code = '';
                while ($i < $fields['total']) {
                    for ($j = 0; $j < 8; $j++) {
                        $voucher_code .= $characters[rand(0, strlen($characters) - 1)];
                    }
                    $code = $this->voucher_codes_repo->isNotExistingVoucherCode($voucher_code);
                    if ($code) {
                        $data = [
                            'voucher_code' => $voucher_code,
                            'voucher_status' => 'new'
                        ];
                        $this->voucher_codes_repo->insertVoucherCode($data);
                        $i += 1;
                    }
                    $voucher_code = '';
                }
                return $this->respondCreated(['Voucher Codes have been generated.']);
            }
        } catch (\Exception $e) {
            $notify = new VoucherNotification(1, 'Generate Voucher Codes Initiated', [1374135]);
            $notify->error = $e->getMessage();
            Notification::send($notify);

            Log::error(SELF::LOGTITLE, array_merge(
                [
                    'error' => 'Could not generate voucher codes '. $e->getMessage()
                ],
                $this->log
            ));
            return $this->errorInternalError($e->getMessage());
        }
    }
}