<<<<<<< HEAD
<?php namespace Voucher\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherJob extends Model
{

    public function voucherJobParamMetadata()
    {
        return $this->hasMany('Voucher\Models\Voucher_jobs_params_metadata');
    }

}