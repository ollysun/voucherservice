<?php namespace Voucher\Repositories;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Voucher\Models\Voucher;
use Voucher\Models\VoucherJobParamMetadata;
use Voucher\Models\VoucherLog;
use Voucher\Models\VoucherCode;
use Voucher\Transformers\VoucherTransformer;
use Voucher\Models\VoucherJob;
use Voucher\Transformers\VoucherJobTransformer;
use Voucher\Transformers\VoucherCodeTransformer;

class VouchersRepository extends AbstractRepository implements IVouchersRepository
{
    /**
     * voucher model
     *
     * @var Voucher
     */
    protected $model;

    /**
     * voucher log model
     *
     * @var
     */
    protected $log_model;

    /**
     * Voucher parameters metadata model
     *
     * @var
     */
    protected $voucherMetadata;

    /**
     * VoucherCode model
     *
     * @var
     */
    protected $voucherCode;

    /**
     * Creates a new vouchers repository instance.
     *
     * @param Voucher $voucher
     * @param VoucherLog $voucherLog
     * @param VoucherJobParamMetadata $voucherMetadataModel
     * @param VoucherCode $voucherCodeModel
     */
    public function __construct(
        Voucher $voucher,
        VoucherLog $voucherLog,
        VoucherJobParamMetadata $voucherMetadataModel,
        VoucherCode $voucherCodeModel
    ) {
        $this->model = $voucher;
        $this->log_model = $voucherLog;
        $this->voucherMetadata = $voucherMetadataModel;
        $this->voucherCode = $voucherCodeModel;
    }

    /**
     * Retrieves all available vouchers in the vouchers table.
     *
     * @param $data
     * @return null
     * @throws \Exception
     */
    public function getVouchers($data)
    {
        try {
            Paginator::currentPageResolver(
                function () use ($data) {
                    return $data['offset'];
                }
            );

            if (is_null($data['query'])) {
                $vouchers = $this->model
                    ->select([
                        DB::raw('vouchers.*'),
                        DB::raw('sum(case when `action` = \'success\' then 1 else 0 end) as `total_redeemed`')
                    ])
                    ->leftJoin('voucher_logs', 'voucher_logs.voucher_id', '=', 'vouchers.id')
                    ->groupBy('voucher_id')
                    ->orderBy($data['sort'], $data['order'])
                    ->paginate($data['limit']);
            } else {
                $vouchers = $this->model->where('code', 'like', '%'.$data['query'].'%')
                    ->select([
                        DB::raw('vouchers.*'),
                        DB::raw('sum(case when `action` = \'success\' then 1 else 0 end) as `total_redeemed`')
                    ])
                    ->leftJoin('voucher_logs', 'voucher_logs.voucher_id', '=', 'vouchers.id')
                    ->groupBy('voucher_id')
                    ->orderBy($data['sort'], $data['order'])
                    ->paginate($data['limit']);
            }

            if (!$vouchers) {
                return null;
            } else {
                $list_vouchers = self::setPaginationLinks($vouchers, $data);
                return self::transform($list_vouchers, new VoucherTransformer());
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Gets a voucher by id in the vouchers table.
     *
     * @param $id
     * @return null
     * @throws \Exception
     */
    public function getVoucherById($id)
    {
        try {
            $voucher = $this->model
                ->select([
                    DB::raw('vouchers.*'),
                    DB::raw('sum(case when `action` = \'success\' then 1 else 0 end) as `total_redeemed`')
                ])
                ->where('vouchers.id', $id)
                ->leftJoin('voucher_logs', 'voucher_logs.voucher_id', '=', 'vouchers.id')
                ->groupBy('voucher_id')
                ->first();
            if (!is_null($voucher)) {
                return self::transform($voucher, new VoucherTransformer());
            } else {
                return null;
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Retrieves a voucher and its attributes by its code.
     *
     * @param $code
     * @return bool
     * @throws \Exception
     */
    public function getVoucherByCode($code)
    {
        try {
            $voucher = $this->model->where('code', $code)
                ->select([
                    DB::raw('vouchers.*'),
                    DB::raw('sum(case when `action` = \'success\' then 1 else 0 end) as `total_redeemed`')
                ])
                ->leftJoin('voucher_logs', 'voucher_logs.voucher_id', '=', 'vouchers.id')
                ->groupBy('voucher_id')
                ->first();

            if (!is_null($voucher)) {
                return self::transform($voucher, new VoucherTransformer());
            } else {
                return false;
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Gets a any single voucher with status new.
     *
     * @param $status
     * @return bool
     * @throws \Exception
     */
    public function getVoucherCodeByStatus($status)
    {
        $voucherCodeByStatus = $this->voucherCode->where('code_status', $status)->first();
        try {
            if (!is_null($voucherCodeByStatus)) {
                return self::transform($voucherCodeByStatus, new VoucherCodeTransformer());
            } else {
                return false;
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Updates a used voucher code status by voucher id.
     *
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function updateVoucherCodeStatusByID($id)
    {
        try {
            $vouchersCodeObject = $this->voucherCode->find($id);
            $vouchersCodeObject->code_status = "used";
            $vouchersCodeObject->save();

            return self::transform($vouchersCodeObject, new VoucherCodeTransformer());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Creates a new voucher, by adding a new code to the vouchers table.
     *
     * @param $input
     * @return mixed
     * @throws \Exception
     */
    public function create($input)
    {
        try {
            $voucher = $this->model;
            $voucher->valid_from = (isset($input['valid_from']) ? $input['valid_from'] : '');
            $voucher->valid_to = (isset($input['valid_to']) ? $input['valid_to'] : '');
            $voucher->status = (isset($input['status']) ? $input['status'] : 'active');
            $voucher->title = (isset($input['title']) ? $input['title'] : 'INTERNAL');
            $voucher->description = (isset($input['description']) ? $input['description'] : '');
            $voucher->location = (isset($input['location']) ? $input['location'] : '');
            //$vouchersObject->is_limited = $input['is_limited'];
            $voucher->limit = (isset($input['limit']) ? $input['limit'] : 1);
            $voucher->period = (isset($input['period']) ? $input['period'] : 'day');
            $voucher->duration = (isset($input['duration']) ? $input['duration'] : '1');
            $voucher->category = (isset($input['category']) ? $input['category'] : 'new');
            $voucher->type = (isset($input['type']) ? $input['type'] : '');
            $voucher->code = (isset($input['code']) ? $input['code'] : '');
            $voucher->voucher_job_id = (isset($input['voucher_job_id']) ? $input['voucher_job_id'] : NULL);
            $voucher->save();

            return self::transform($voucher, new VoucherTransformer());
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    /**
     * Updates a voucher and its parameters.
     *
     * @param $id
     * @param $input
     * @return mixed
     * @throws \Exception
     */
    public function update($id, $input)
    {
        try {
            $voucher = $this->model->find($id);

            $voucher->valid_from = (
            isset($input['valid_from']) ? $input['valid_from'] : $voucher->valid_from
            );
            $voucher->valid_to = (
            isset($input['valid_to']) ? $input['valid_to'] : $voucher->valid_to
            );
            $voucher->status = (
            isset($input['status']) ? $input['status'] : $voucher->status
            );
            $voucher->title = (
            isset($input['title']) ? $input['title'] : $voucher->title
            );
            $voucher->description = (
            isset($input['description']) ? $input['description'] : $voucher->description
            );
            $voucher->location = (
            isset($input['location']) ? $input['location'] : $voucher->location
            );
            //$vouchersObject->is_limited = $input['is_limited'];
            $voucher->limit = (
            isset($input['limit']) ? $input['limit'] : $voucher->limit
            );
            $voucher->period = (
            isset($input['period']) ? $input['period'] : $voucher->period
            );
            $voucher->duration = (
            isset($input['duration']) ? $input['duration'] : $voucher->duration
            );
            $voucher->category = (
            isset($input['category']) ? $input['category'] : $voucher->category
            );
            $voucher->type = (
            isset($input['type']) ? $input['type'] : $voucher->type
            );
            $voucher->code = (
            isset($input['code']) ? $input['code'] : $voucher->code
            );
            $voucher->voucher_job_id = (
            isset($input['voucher_job_id']) ? $input['voucher_job_id'] : $voucher->voucher_job_id
            );
            $voucher->save();

            return self::transform($voucher, new VoucherTransformer());
        }catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    /**
     * Retrieves vouchers for csv generation by job id and limit
     *
     * @param $params
     * @return mixed
     * @throws \Exception
     */
    public function getVouchersByJobIdAndLimit($params)
    {
        try {
            $vouchers = $this->model->where('voucher_job_id', '=', $params['voucher_job_id'])
                ->skip($params['start'])
                ->take($params['limit'])
                ->get();

            return self::transform($vouchers, new VoucherTransformer());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Sets a voucher status after successful redeem.
     *
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function updateVoucherStatus($data)
    {
        try {
            $voucher = $this->model->findOrFail($data['voucher_id']);
            $voucher->status = $data['voucher_status'];
            $voucher->save();

            return true;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Adds a new voucher job to be processed.
     *
     * @param $status
     * @return mixed
     * @throws \Exception
     */
    public function insertVoucherJob($status)
    {
        try {
            $voucherJob = new VoucherJob();
            if ($status == 'new') {
                $voucherJob->status = 'new';
                $voucherJob->comments = 'New VoucherJob Inserted';
            } elseif ($status == 'processing') {
                $voucherJob->status = 'processing';
                $voucherJob->comments = 'Processing VoucherJob.........';
            } elseif ($status == 'completed') {
                $voucherJob->status = 'completed';
                $voucherJob->comments = 'VoucherJob Complete processing';
            } else {
                $voucherJob->status = 'error';
                $voucherJob->comments = 'Error processing the VoucherJob';
            }
            $voucherJob->save();
            return self::transform($voucherJob, new VoucherJobTransformer());
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    /**
     *  Adds list of parameters for a voucher job process.
     *
     * @param $data
     * @param $voucher_job_id
     * @return bool
     * @throws \Exception
     */
    public function insertVoucherJobParamMetadata($data, $voucher_job_id)
    {
        try{
            foreach($data as $key => $value)
            {
                $voucherMetadata = new VoucherJobParamMetadata();
                $voucherMetadata->voucher_job_id = $voucher_job_id;
                $voucherMetadata->key = trim($key);
                $voucherMetadata->value = trim($value);
                $voucherMetadata->save();
            }
            return true;
        }catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
}
