<?php
/**
 * Created by PhpStorm.
 * User: Tech-1
 * Date: 10/12/15
 * Time: 12:18 PM
 */

namespace Voucher\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherJobParamMetadata extends Model {

    public $table = 'voucher_jobs_params_metadata';

    /**
     * Get the VoucherJob record associated.
     *
     */
    public function voucherJob()
    {
        return $this->belongsTo('Voucher\Models\VoucherJob');
    }
}