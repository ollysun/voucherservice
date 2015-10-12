<?php namespace Voucher\Http\Controllers;

use Illuminate\Http\Request;
use Voucher\Repositories\VoucherJobsParamsMetaDataRepository;
use Voucher\Repositories\VoucherJobsRepository;
use Voucher\Repositories\VouchersRepository;
use log;
use Aws\S3\S3Client;
use Illuminate\Config;

class TaskController extends Controller
{
    protected $sqs_worker;

    protected $voucher_jobs_repo;

    protected $voucher_jobs_params_repo;

    protected $voucher_repo;

    public function __construct(Request $request, VouchersRepository $voucher_repo, VoucherJobsRepository $voucher_jobs_repo, VoucherJobsParamsMetadataRepository $voucher_jobs_params_repo)
    {
        parent::__construct($request);

        $this->voucher_jobs_repo = $voucher_jobs_repo;

        $this->voucher_jobs_params_repo = $voucher_jobs_params_repo;

        $this->voucher_repo = $voucher_repo;
    }

    protected function generateVouchers()
    {
        try {
            $jobs = $this->voucher_jobs_repo->getJobs();

            if ($jobs) {
                foreach ($jobs as $job) {

                    $this->voucher_jobs_repo->updateJobStatus($job, 'processing', null);
                    $job_params = $this->voucher_jobs_params_repo->getJobParams($job);

                    foreach ($job_params as $job_param) {
                        $params[$job_param->key] = $job_param->value;
                    }

                    $this->voucher_repo->generateVoucherWithStoredProcedure($params);

                    //@TODO Logic to loop through every limit 25000 - Loop Starts - Lawrence

                    $skip = 25000;

                    $vouchers = $this->voucher_repo->getByJobId($job, $skip, 25000);

                    $csv_file = $this->generateCsv($vouchers);
                    $this->uploadS3($csv_file);
                    $this->notify($vouchers);

                    //@TODO loop ends - Lawrence

                    $this->voucher_jobs_repo->updateJobStatus($job, 'completed', null);
                }

                Log::info(SELF::LOGTITLE, array_merge(
                    ['success' => 'Successfully generated Voucher Codes.'],
                    $this->log
                ));
                return $this->respondSuccess('Successfully generated Voucher Codes.');
            } else {
                Log::info(SELF::LOGTITLE, array_merge(
                    ['success' => 'No voucher jobs to process'],
                    $this->log
                ));
                return $this->respondSuccess('No voucher jobs to process');
            }
        }
        catch (\Exception $e) {
            $this->voucher_jobs_repo->updateJobStatus($job, 'error', $e->getMessage());
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
                        $voucher['code'], $voucher['duration'].' '.$vouchers['period']
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


        }
        catch (\Exception $e) {
            return ('S3 Upload error:'. $e->getMessage());
        }
    }

    public function notify($data)
    {
        try {
            //@TODO implement Voucher Notification Chizzy
        }
        catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    //This will be run by a Schedular cron jobs , vouchers codes will be generated in upfront but not assigned to any telcos yet
    //voucher code can be upto 6-8alphanum. I will talk to Ngozi to implement Salted Vouchers. But for now lets put algorithim

    public function generateVoucherCodes($data)
    {
        try {
            //@TODO Algorithm to generate unique voucher code - Chizzy
            // Create migration for new table - "voucher_codes" column "id","code","status"->enum (new,used)
        }
        catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
