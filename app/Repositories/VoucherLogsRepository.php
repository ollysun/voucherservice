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
            $log->user_id = (isset($data['user_id']) ? $data['user_id'] : NULL);
            $log->action = (isset($data['action']) ? $data['action'] : NULL);
            $log->platform = (isset($data['platform']) ? $data['platform'] : NULL);
            $log->comment = (isset($data['comment']) ? $data['comment'] : NULL);
            $log->save();
            return self::transform($log, new VoucherLogTransformer());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
