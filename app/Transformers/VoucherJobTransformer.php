<?php namespace Voucher\Transformers;

use League\Fractal\TransformerAbstract;
use Voucher\Models\VoucherJob;

class VoucherJobTransformer extends TransformerAbstract
{
    public static function transform(VoucherJob $voucherJob)
    {
        return [
            'id' => (int) $voucherJob->id,
            'status' => (string) $voucherJob->status,
            'comments' => (string) $voucherJob->comments,
            '_links' => [
                [
                    'rel' => 'self',
                    'uri' => '/voucherJob/' . $voucherJob->id
                ],
                [
                    'rel' => 'voucherJobParamMetadata',
                    'uri' => '/voucherJob/' . $voucherJob->id . '/voucherJobParamMetadata'
                ]
            ],
            $voucherJob->voucher(),
            $voucherJob->voucherJobParamMetadata()
        ];
    }
}
