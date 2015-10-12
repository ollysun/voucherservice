<?php namespace Voucher\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    protected $sqs_worker;

    public function __construct(Request $request)
    {
        parent::__construct($request);

    }

    protected function generateVouchers()
    {
        try {
    //@TODO implement Repository and Transformers based for the below tables - Lawrence and refactor to repo format
            $get_jobs = DB::table('voucher_jobs')
                ->where('voucher_jobs.status', '=', 'new')
                ->orWhere('voucher_jobs.status', '=', 'error')
                ->get();
                // use repo and transform to return:

            if($get_jobs) {
                foreach ($get_jobs as $job) {
                    DB::table('voucher_jobs')
                        ->where('id', $job->id)
                        ->update(['status' => 'processing']);

                    $get_jobs_params = DB::table('voucher_jobs_params_metadata')
                        ->where('voucher_jobs_params_metadata.voucher_job_id', '=', $job->id)
                        ->get();

                    $params = array();
                    $params['job_id'] =  $job->id;

                    foreach ($get_jobs_params as $get_jobs_param) {
                        $params[$get_jobs_param->key] = $get_jobs_param->value;
                    }

                    $create_vouchers = DB::statement(
                                            DB::raw(
                                                'CALL generate_voucher("' .
                                                    $params["status"] . '","' .
                                                    $params["category"] . '","' .
                                                    $params["title"] . '","' .
                                                    $params["location"] . '","' .
                                                    $params["description"] . '",' .
                                                    $params["duration"] . ',"' .
                                                    $params["period"] . '","' .
                                                    $params["valid_from"] . '","' .
                                                    $params["valid_to"] . '","' .
                                                    $params["is_limited"] . '",' .
                                                    $params["limit"] . ',"' .
                                                    $params["brand"] . '",' .
                                                    $params["total"] . ',' .
                                                    $job->id.
                                                ')'
                                            ));

                    //DO in Batches limit 25000 per batch
                    // $get_vouchers = "select * from vouchers where voucher_job_id = 1 limit 10000"; // get this from repo and transformer

                    //@TODO Logic to loop through every limit 25000 - Loop Starts - Lawrence

                    $vouchers = DB::table('vouchers')
                        ->where('voucher_job_id', '=', $job->id)
                        ->limit(25000)
                        ->get();

                    $this->generateCsv($vouchers);
                    $this->uploadS3($vouchers);
                    $this->notify($vouchers);
                    //@TODO loop ends - Lawrence
                    DB::table('voucher_jobs')
                        ->where('id', $job->id)
                        ->update(['status' => 'completed']);


                }
            } else {
                //@TODO implement Log: ('No voucher jobs to process');
            }

        }
        catch (\Exception $e) {
            //@TODO update jobs voucher_jobs table-status =error and comment = e->getMessage - Lawrence
            return $e->getMessage();
        }
    }

    public function generateCsv($data)
    {
        try {

            $data['file_name'] = $data['title'].'_'.date("Y_m_d_H_i_s",time());

            //@TODO format $data similar to $list format - Lawrence
            $list = array (
                array('INgfhdg', '1 month'),
                array('INfty3', '1 month'),
                array("INfdgfh", "1 month")
            );

            $fp = fopen(storage_path('vouchers').'/'.$data['file_name'].'.csv', 'w');

            fputcsv($fp, array('Voucher Code', 'Duration'));

            foreach ($list as $fields) {
                fputcsv($fp, $fields);
            }

            fclose($fp);
            return $data;
        }
        catch (\Exception $e) {
            return ('error during CSV File generation : '.$e->getMessage());
        }
    }

    public function uploadS3($data)
    {
        try {
            $bucket = getenv('AWS_S3_BUCKET');

            $filepath = storage_path('vouchers');
            $keyname = getenv('AWS_S3_BUCKET_FOLDER').'/'.$data['file_name'];


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
