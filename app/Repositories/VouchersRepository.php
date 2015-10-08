<?php namespace Voucher\Repositories;

use Illuminate\Pagination\Paginator;
use Voucher\Models\Voucher;
use Voucher\Models\VoucherLog;

use Voucher\Transformers\VoucherTransformer;
use Voucher\Transformers\VoucherLogTransformer;
use Voucher\Payment\Event;

class VouchersRepository extends AbstractRepository
{
    protected $model;
    protected $log_model;
    protected $error;

    public function __construct(Voucher $voucher, VoucherLog $voucherLog)
    {
        $this->model = $voucher;
        $this->log_model = $voucherLog;
    }


}
