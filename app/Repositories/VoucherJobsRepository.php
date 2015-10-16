<?php namespace Voucher\Repositories;

use Voucher\Models\VoucherJob;
use Voucher\Transformers\VoucherJobTransformer;
use Illuminate\Support\Facades\DB;

class VoucherJobsRepository extends AbstractRepository implements IVoucherJobsRepository
{
    /**
     * Voucher job model.
     *
     * @var VoucherJob
     */
    protected $model;

    /**
     * Creates a new voucher Job repository instance.
     *
     * @param VoucherJob $model
     */
    public function __construct(VoucherJob $model)
    {
        $this->model = $model;
    }

    /**
     * Retrieves voucher issuing jobs.
     * only jobs with new or error status are retrieved to be processed.
     *
     * @return mixed
     * @throws \Exception
     */
    public function getJobs()
    {
        try {
            $jobs = $this->model->where('voucher_jobs.status', '=', 'new')
                ->orWhere('voucher_jobs.status', '=', 'error')
                ->get();

            if (!$jobs->isEmpty()) {
                return self::transform($jobs, new VoucherJobTransformer());
            } else {
                return false;
            }

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Updates voucher issuing job status (new, error or processing).
     *
     * @param $params
     * @return bool
     * @throws \Exception
     */
    public function updateJobStatus($params)
    {
        try {
            $job = $this->model->find($params['voucher_job_id']);
            $job->comments = (isset($params['comment'])? $params['comment'] : $job->comments);
            $job->status = (isset($params['status'])? $params['status'] : $job->status);
            $job->save();
            return true;

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Call the stored procedure, that will copy generated codes from
     * voucher_codes table to vouchers table.
     *
     * @param $params
     * @return mixed
     * @throws \Exception
     */
    public function addVouchers($params)
    {
        try {
            $vouchers = DB::statement(
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
                    $params["valid_to"] . '",' .
                    $params["limit"] . ',"' .
                    $params["brand"] . '",' .
                    $params["total"] . ',' .
                    $params["voucher_job_id"].
                    ')'
                )
            );

            return $vouchers;
        } catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }
}
