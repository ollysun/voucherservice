<?php namespace Voucher\Transformers;

use League\Fractal\TransformerAbstract;
use Voucher\Models\VoucherJobParamMetadata;

class VoucherJobParamMetadataTransformer extends TransformerAbstract
{
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
}
