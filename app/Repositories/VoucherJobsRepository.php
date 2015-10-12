<?php namespace Voucher\Repositories;

use Voucher\Models\VoucherJob;
use Voucher\Transformers\VoucherJobTransformer;

class VoucherJobsRepository extends AbstractRepository
{
    protected $model;

    public function __construct(VoucherJob $model)
    {
        $this->model = $model;
    }

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

    public function updateJobStatus($job, $params)
    {
        try {
            $job = $this->model->find($job->id);
            $job->comment = $params['comments'];
            $job->status = $params['status'];
            $job->save();

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}

