<?php
/**
 * Created by PhpStorm.
 * User: Tech-1
 * Date: 10/12/15
 * Time: 1:49 PM
 */

namespace Voucher\Transformers;

use League\Fractal\TransformerAbstract;
use Voucher\Models\Voucher_jobs_params_metadata;

class VoucherJobParamMetadataTransformer extends TransformerAbstract {

    protected $availableIncludes = [
        'voucherJob'
    ];

    public static function transform(Voucher_jobs_params_metadata $voucherJobMetadata)
    {
        return [
            'id' => (int) $voucherJobMetadata->id,
            'voucher_job_id' => (int) $voucherJobMetadata->voucher_job_id,
            'key' => (string) $voucherJobMetadata->key,
            'value' => (int) $voucherJobMetadata->value,
            'created_at' => $voucherJobMetadata->created_at,
            'updated_at' => $voucherJobMetadata->updated_at,
            '_links' => [
                [
                    'rel' => 'self',
                    'uri' => '/paramMetadata/' . $voucherJobMetadata->id,
                ],
                [
                    'rel' => 'voucher',
                    'uri' => '/voucherJob/' . $voucherJobMetadata->voucher_job_id,
                ]
            ]
        ];
    }

    public function includeVoucherParamMetadata(Voucher_jobs_params_metadata $voucherJobMetadata)
    {
        $voucherParamMetadata = $voucherJobMetadata->voucherJob;
        return $this->collection($voucherParamMetadata, new VoucherJob); //@TODO write  transformer for parammetadata

    }
}