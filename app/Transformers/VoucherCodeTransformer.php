<?php
/**
 * Created by PhpStorm.
 * User: Tech-1
 * Date: 10/14/15
 * Time: 10:45 AM
 */

namespace Voucher\Transformers;

use League\Fractal\TransformerAbstract;
use Voucher\Models\VoucherCode;

class VoucherCodeTransformer extends TransformerAbstract {

    public static function transform(VoucherCode $voucherCode)
    {
        return [
            'id' => (int) $voucherCode->id,
            'voucher_code' => (string) $voucherCode->voucher_code,
            'code_status' => (string) $voucherCode->code_status,
            'created_at' => $voucherCode->created_at,
            'updated_at' => $voucherCode->updated_at,
            '_links' => [
                [
                    'rel' => 'self',
                    'uri' => '/voucherCode/' . $voucherCode->id
                ]
            ]
        ];
    }

}