<?php namespace Voucher\Repositories;

use Voucher\Models\VoucherJobParamMetadata;
use Voucher\Transformers\VoucherJobParamMetadataTransformer;

class VoucherJobParamMetadatasRepository extends AbstractRepository implements IVoucherJobParamMetadatasRepository
{
    /**
     *  Voucher job parameters model
     *
     * @var VoucherJobParamMetadata
     */
    protected $model;

    /**
     * Creates a new Voucher jobs parameters repository instance.
     *
     * @param VoucherJobParamMetadata $voucher_jobs_params_metadata
     */
    public function __construct(VoucherJobParamMetadata $voucher_jobs_params_metadata)
    {
        $this->model = $voucher_jobs_params_metadata;
    }

    /**
     * Retrieves all parameters for a specific voucher job id.
     *
     * @param $job
     * @return mixed
     * @throws \Exception
     */
    public function getJobParams($job)
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
