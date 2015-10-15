<?php namespace Voucher\Models;

use Illuminate\Database\Eloquent\Model;

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

    /**
     * Get the Voucher_Job record associated with the Voucher.
     *
     * Voucher belongsTo relationship with Voucher_Job
     */
    public function voucherJob()
    {
        return $this->belongsTo('Voucher\Models\VoucherJob');
    }
}
