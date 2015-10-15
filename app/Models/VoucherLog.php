<?php namespace Voucher\Models;

use Illuminate\Database\Eloquent\Model;

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
