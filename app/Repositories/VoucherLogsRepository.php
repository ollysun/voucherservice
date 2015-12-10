<?php namespace Voucher\Repositories;

use Voucher\Models\Voucher;
use Voucher\Models\VoucherLog;
use Voucher\Transformers\VoucherLogTransformer;
use Voucher\Transformers\VoucherTransformer;
use Illuminate\Pagination\Paginator;

class VoucherLogsRepository extends AbstractRepository implements IVoucherLogsRepository
{
    /**
     * Voucher log model.
     *
     * @var
     */
    protected $voucher_log_model;

    /**
     * Voucher model.
     *
     * @var
     */
    protected $voucher_model;

    /**
     * Creates a new voucher log repository instance.
     *
     * @param VoucherLog $voucher_log
     * @param Voucher $voucher
     */
    public function __construct(VoucherLog $voucher_log, Voucher $voucher)
    {
        $this->voucher_log_model = $voucher_log;
        $this->voucher_model = $voucher;
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
            $voucher_log = $this->voucher_log_model;
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

    /**
     * Gets the voucher log of a particular user and voucher code.
     * returns true if found or false if not found
     *
     * @param $user_id
     * @param $voucher_id
     * @return bool
     */
    public function redeemedByUser($user_id, $voucher_id)
    {
        $voucher_log = $this->voucher_log_model->where('user_id', $user_id)
            ->where('voucher_id', $voucher_id)
            ->where('action', 'success')
            ->first();
        if (is_null($voucher_log)) {
            return false;
        } else {
            return true;
        }
    }

    public function getVoucherClaimedByUserID($fields)
    {
        Paginator::currentPageResolver(
            function () use ($fields) {
                return $fields['offset'];
            }
        );

        $voucher_log = $this->voucher_model
            ->leftJoin('voucher_logs', 'vouchers.id', '=', 'voucher_logs.voucher_id')
            ->where('voucher_logs.user_id', $fields['user_id'])
            ->where('voucher_logs.action', 'success')
            ->orderBy('voucher_logs.created_at', $fields['order'])
            ->paginate($fields['limit']);

        if (!$voucher_log->isEmpty()) {
            return self::transform($voucher_log, new VoucherTransformer());
        } else {
            return false;
        }
    }
}
