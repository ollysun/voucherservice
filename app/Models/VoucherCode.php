<?php namespace Voucher\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherCode extends Model
{
    protected $fillable = ['voucher_code', 'code_status'];
}
