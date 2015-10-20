<?php
/**
 * Created by PhpStorm.
 * User: Tech-1
 * Date: 10/12/15
 * Time: 1:49 PM
 */

namespace Voucher\Transformers;

use League\Fractal\TransformerAbstract;
use Voucher\Models\VoucherJobParamMetadata;

class VoucherJobParamMetadataTransformer extends TransformerAbstract {

    protected $availableIncludes = [
        'voucherJob'
    ];

    public static function transform(VoucherJobParamMetadata $voucherJobMetadata)
    {
        return [
            'id' => (int) $voucherJobMetadata->id,
            'voucher_job_id' => (int) $voucherJobMetadata->voucherJob->id,
            'key' => (string) $voucherJobMetadata->key,
            'value' => (string) $voucherJobMetadata->value,
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

    public function includeVoucherJob(VoucherJobParamMetadata $voucherJobMetadata)
    {
        $voucherJob = $voucherJobMetadata->voucherJob;
        return $this->collection($voucherJob, new VoucherJobTransformer());
    }
}