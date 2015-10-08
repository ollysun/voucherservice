<?php namespace Voucher\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Created by PhpStorm.
 * User: tech7
 * Date: 10/8/15
 * Time: 3:08 PM
 */

class VoucherLog extends Model
{
    /**
     * Get the Voucher record associated with the Voucher_Log.
     *
     * Voucher_Log belongsTo relationship with Voucher
     */
    public function voucher()
    {
        return $this->belongsTo('Voucher\Models\Voucher');
    }
}