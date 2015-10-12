<?php namespace Voucher\Repositories;

use Voucher\Models\VoucherJobsParamsMetadata;
use Voucher\Transformers\VoucherJobsParamsMetadataTransformer;

class VoucherJobsParamsMetadataRepository extends AbstractRepository
{
    protected $model;

    public function __construct(VoucherJobsParamsMetadata $voucher_jobs_params_metadata)
    {
        $this->model = $voucher_jobs_params_metadata;
    }

    public function getJobParams($job)
    {
        try {
            $params = $this->model->where('voucher_job_id', '=', $job->id)
                ->get();
            return self::transform($params, new VoucherJobsParamsMetadataTransformer());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
