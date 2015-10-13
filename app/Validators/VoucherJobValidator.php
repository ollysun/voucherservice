<?php
/**
 * Created by PhpStorm.
 * User: Tech-1
 * Date: 10/12/15
 * Time: 12:38 PM
 */

namespace Voucher\Validators;

use Illuminate\Validation\Validator as IlluminateValidator;



class VoucherJobValidator extends IlluminateValidator  {

    public static function getBrandAndTotalRules()
    {
        return[
            'brand' => 'required|string',
            'total' => 'required|(^[0-9]+$)+/'
        ];
    }

}