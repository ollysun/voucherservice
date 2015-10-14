<?php namespace Voucher\Repositories;

use Voucher\Models\VoucherJobParamMetadata;
use Voucher\Transformers\VoucherJobParamMetadataTransformer;

class VoucherJobsParamsMetadataRepository extends AbstractRepository implements IVoucherJobsParamsMetadataRepository
{
    protected $model;

    public function __construct(VoucherJobParamMetadata $voucher_jobs_params_metadata)
    {
        $this->model = $voucher_jobs_params_metadata;
    }

    /**
     * Retrieves voucher issuing job parameters by job id.
     *
     * @param $job
     * @return mixed
     * @throws \Exception
     */
    public function getJobParameters($job)
    {
        try {
            $params = $this->model->where('voucher_job_id', '=', $job['id'])
                ->get();
            return self::transform($params, new VoucherJobParamMetadataTransformer());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
