<?php namespace Voucher\Repositories;

use Voucher\Models\VoucherLog;
use Voucher\Transformers\VoucherLogTransformer;

class VoucherLogsRepository extends AbstractRepository implements IVoucherLogsRepository
{
    /**
     * Voucher log model.
     *
     * @var
     */
    protected $model;

    /**
     * Creates a new voucher log repository instance.
     *
     * @param VoucherLog $voucher_log
     */
    public function __construct(VoucherLog $voucher_log)
    {
        $this->model = $voucher_log;
    }

    /**
     * Adds a voucher redeem event (success or attempt) to the voucher logs table.
     *
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    public function addVoucherLog($data)
    {
        try {
            $voucher_log = $this->model;
            $voucher_log->voucher_id = (isset($data['voucher_id']) ? $data['voucher_id'] : null);
            $voucher_log->user_id = (isset($data['user_id']) ? $data['user_id'] : null);
            $voucher_log->action = (isset($data['action']) ? $data['action'] : null);
            $voucher_log->platform = (isset($data['platform']) ? $data['platform'] : 'mobile');
            $voucher_log->comments = (isset($data['comments']) ? $data['comments'] : ' ');
            $voucher_log->save();

            return self::transform($voucher_log, new VoucherLogTransformer());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
