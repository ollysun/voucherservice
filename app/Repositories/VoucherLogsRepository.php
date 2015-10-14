<?php namespace Voucher\Repositories;

use Voucher\Models\VoucherLog;
use Voucher\Transformers\VoucherLogTransformer;

class VoucherLogsRepository extends AbstractRepository
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
     * @param VoucherLog $voucherLog
     */
    public function __construct(VoucherLog $voucherLog)
    {
        $this->model = $voucherLog;
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
            $log = $this->model;
            $log->voucher_id = (isset($data['voucher_id']) ? $data['voucher_id'] : NULL);
            $log->user_id = (isset($data['user_id']) ? $data['user_id'] : NULL);
            $log->action = (isset($data['action']) ? $data['action'] : NULL);
            $log->platform = (isset($data['platform']) ? $data['platform'] : 'web');
            $log->comment = (isset($data['comment']) ? $data['comment'] : ' ');
            $log->save();

            return self::transform($log, new VoucherLogTransformer());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Retrieves the total count a voucher code was successfully redeemed.
     *
     * @param $voucher_id
     * @throws \Exception
     */
    public function getVoucherRedeemedCount($voucher_id)
    {
        try {
            $count = $this->model->where('voucher_id', $voucher_id)
                ->where('action', 'success')
                ->count();

            return $count;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
