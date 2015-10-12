<?php
/**
 * Created by PhpStorm.
<<<<<<< HEAD
 * User: Tech-1
 * Date: 10/12/15
 * Time: 12:59 PM
=======
 * User: tech4
 * Date: 10/12/15
 * Time: 11:28 AM
>>>>>>> task controller voucher generation refactor
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
                    'uri' => '/voucherJob/' . $voucherJob->id . '/voucherParamMetadata'
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