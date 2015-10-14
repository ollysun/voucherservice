<?php namespace Voucher\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Voucher\Notification\VoucherNotification;
use Voucher\Validators\VoucherValidator;
use Log;
use Notification;
use Voucher\Repositories\VoucherJobsParamsMetaDataRepository;
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
        VoucherJobsParamsMetadataRepository $voucher_jobs_params_repo,
        VoucherCodesRepository $voucher_codes_repo
    ) {
        parent::__construct($request);

        $this->voucher_jobs_repo = $voucher_jobs_repo;

        $this->voucher_jobs_params_repo = $voucher_jobs_params_repo;

        $this->voucher_repo = $voucher_repo;

        $this->voucher_codes_repo = $voucher_codes_repo;
    }

    protected function generateVouchers()
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
            foreach ($jobs as $job) {
                $update_data = [
                    'job_id' => $job->id,
                    'status' => 'processing'
                ];

                $this->voucher_jobs_repo->updateJobStatus($update_data);
                $job_params = $this->voucher_jobs_params_repo->getJobParams($job);
                $params = [];

                foreach ($job_params as $job_param) {
                    $params[$job_param->key] = $job_param->value;
                }

                $this->voucher_repo->generateVoucherWithStoredProcedure($params);

                $loop_params = [
                    'limit' => 25000,
                    'start' => 1,
                    'job_id' => $job->id
                ];

                while ($loop_params['start'] > 0) {

                    $vouchers = $this->voucher_repo->getByJobIdAndLimit($loop_params);
                    $csv_file = $this->generateCsv($vouchers);
                    $this->uploadS3($csv_file);
                    $this->notify($vouchers);

                    if (count($vouchers) < $loop_params['limit']) {
                        $loop_params['start'] = 0;
                    } else {
                        $loop_params['start'] = $loop_params['start'] + $loop_params['limit'] + 1;
                    }
                }

                $update_data = [
                    'job_id' => $job->id,
                    'status' => 'completed'
                ];

                $this->voucher_jobs_repo->updateJobStatus($update_data);
            }

            Log::info(SELF::LOGTITLE, array_merge(
                ['success' => 'Successfully generated Voucher Codes.'],
                $this->log
            ));
            return $this->respondSuccess('Successfully generated Voucher Codes.');

        } catch (\Exception $e) {

            $update_data = [
                'job_id' => $job->id,
                'status' => 'error',
                'comment' => $e->getMessage()
            ];

            $this->voucher_jobs_repo->updateJobStatus($update_data);
            Log::error(SELF::LOGTITLE, array_merge(
                ['error' => $e->getMessage()],
                $this->log
            ));
            return $this->errorInternalError($e->getMessage());
        }
    }

    public function generateCsv($vouchers)
    {
        try {
            $voucher_file = $vouchers['data'][0]['title'].'_'.date('Y_m_d_H_i_s', time());

            $fp = fopen(storage_path('vouchers').'/'.$voucher_file.'.csv', 'w');

            fputcsv($fp, array('Voucher Code', 'Duration'));

            foreach ($vouchers as $voucher) {
                fputcsv($fp, array(
                        $voucher['code'], $voucher['duration']
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
            return $this->errorInternalError('error during CSV File generation : '.$e->getMessage());
        }
    }

    public function uploadS3($file_name)
    {
        try {
            $bucket = getenv('AWS_S3_BUCKET');

            $filepath = storage_path('vouchers');
            $keyname = getenv('AWS_S3_BUCKET_FOLDER').'/'.$file_name;


            $s3 = new S3Client(Config::get('s3'));
            // Upload a file.
            $result = $s3->putObject(array(
                'Bucket' => $bucket,
                'Key' => $keyname,
                'SourceFile' => $filepath
            ));

            echo $result['ObjectURL'];
            //@TODO remove file from storage path after s3 uploaded successfully - Chizzy

            unlink($filepath . '/' . $file_name . '.csv');
        }
        catch (\Exception $e) {
            return ('S3 Upload error:'. $e->getMessage());
        }
    }

    public function notify($s3_result)
    {
        try {
            //@TODO implement Voucher Notification Chizzy
            $notify = new VoucherNotification(1, 'Generate Voucher Initiated', [1]);
            $notify->__set('data', $s3_result);
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
                        $data = ['code' => $voucher_code, 'status' => 'new'];
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