<?php namespace Voucher\Validators;

use Illuminate\Validation\Validator as IlluminateValidator;

class VoucherJobValidator extends IlluminateValidator
{
    public static function getBrandAndTotalRules()
    {
        return[
            'brand' => 'required|string',
            'total' => 'required|regex:/(^[0-9]+$)+/|max:3',
        ];
    }
}
