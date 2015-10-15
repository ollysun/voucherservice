<?php namespace Voucher\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherJobParamMetadata extends Model
{
    /**
     * Get the VoucherJob record associated.
     *
     */
    public function voucherJob()
    {
        return $this->belongsTo('Voucher\Models\VoucherJob');
    }
}
