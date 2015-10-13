<?php
/**
 * Created by PhpStorm.
 * User: Tech-1
 * Date: 10/12/15
 * Time: 12:59 PM
 */

namespace Voucher\Transformers;

use League\Fractal\TransformerAbstract;
use Voucher\Models\VoucherJob;

class VoucherJobTransformer extends TransformerAbstract{

    protected $availableIncludes = [
        'voucherJobsParamsMetadata'
    ];

    public static function transform(VoucherJob $voucherJob)
    {
        return [
            'id' => (int) $voucherJob->id,
            'status' => (int) $voucherJob->status,
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
            ]
        ];
    }

    public function includeVoucherJobsParamsMetadata(VoucherJob $voucherJob)
    {
        $voucherParamMetadata = $voucherJob->voucherJobParamMetadata;
        return $this->collection($voucherParamMetadata, new VoucherJobParamMetadataTransformer());

    }

}