<?php namespace Voucher\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Created by PhpStorm.
 * User: tech7
 * Date: 10/8/15
 * Time: 3:08 PM
 */

class Voucher extends Model
{
    /**
     * Get the Voucher_Log record associated with the Voucher.
     *
     * Voucher hasMany relationship with Voucher_Log
     */
    public function voucherLog()
    {
        return $this->hasMany('Voucher\Models\VoucherLog');
    }
}