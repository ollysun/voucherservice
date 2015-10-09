<?php namespace Voucher\Repositories;

use Voucher\Models\VoucherLog;
use Voucher\Transformers\VoucherLogTransformer;

class VoucherLogsRepository extends AbstractRepository
{
    protected $model;

    public function __construct(VoucherLog $voucherLog)
    {
        $this->model = $voucherLog;
    }

    public function addVoucherLog($data)
    {
        try {
            $log = $this->model;
            $log->voucher_id = (isset($data['voucher_id']) ? $data['voucher_id'] : NULL);
            $log->user_id = $data['user_id'];
            $log->action = $data['action'];
            $log->platform = $data['platform'];
            $log->comment = $data['comment'];
            $log->save();

<<<<<<< HEAD
            return self::transform($log, new VoucherLogTransformer());
=======
            return true;
>>>>>>> initial redeem feature setup
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
