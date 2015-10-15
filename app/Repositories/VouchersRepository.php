<?php namespace Voucher\Repositories;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Voucher\Models\Voucher;
use Voucher\Models\VoucherJobParamMetadata;
use Voucher\Models\VoucherLog;
use Voucher\Models\VoucherCode;
use Voucher\Transformers\VoucherTransformer;
use Voucher\Models\VoucherJob;
use Voucher\Transformers\VoucherJobParamMetadataTransformer;
use Voucher\Transformers\VoucherJobTransformer;
use Voucher\Transformers\VoucherCodeTransformer;

class VouchersRepository extends AbstractRepository implements IVouchersRepository
{
    protected $model;

    protected $log_model;

    protected $voucherMetadata;

    protected $voucherCode;

    public function __construct(

        Voucher $voucher,

        VoucherLog $voucherLog,

        VoucherJobParamMetadata $voucherMetadataModel,

        VoucherCode $voucherCodeModel
    )
    {
        $this->model = $voucher;

        $this->log_model = $voucherLog;

        $this->voucherMetadata = $voucherMetadataModel;

        $this->voucherCode = $voucherCodeModel;
    }

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

    public function getVoucherById($id)
    {
        try {
            $voucher = $this->model->where($id, 'id')
                ->select([
                    DB::raw('vouchers.*'),
                    DB::raw('sum(case when `action` = \'success\' then 1 else 0 end) as `total_redeemed`')
                ])
                ->leftJoin('voucher_logs', 'voucher_logs.voucher_id', '=', 'vouchers.id')
                ->groupBy('voucher_id');

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

    public function updateVoucherCodeStatusByID($id)
    {
        try {
            $vouchersCodeObject = $this->voucherCode->find($id);
            $vouchersCodeObject->code_status = "used";
            $vouchersCodeObject->save();
            return $vouchersCodeObject;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function create($input)
    {
        try{

            $vouchersObject = $this->model;
            $vouchersObject->valid_from = (isset($input['valid_from']) ? $input['valid_from'] : '');
            $vouchersObject->valid_to = (isset($input['valid_to']) ? $input['valid_to'] : '');
            $vouchersObject->status = (isset($input['status']) ? $input['status'] : 'active');
            $vouchersObject->title = (isset($input['title']) ? $input['title'] : 'INTERNAL');
            $vouchersObject->description = (isset($input['description']) ? $input['description'] : '');
            $vouchersObject->location = (isset($input['location']) ? $input['location'] : '');
            //$vouchersObject->is_limited = $input['is_limited'];
            $vouchersObject->limit = (isset($input['limit']) ? $input['limit'] : 1);
            $vouchersObject->period = (isset($input['period']) ? $input['period'] : 'day');
            $vouchersObject->duration = (isset($input['duration']) ? $input['duration'] : '1');
            $vouchersObject->category = (isset($input['category']) ? $input['category'] : 'new');
            $vouchersObject->type = (isset($input['type']) ? $input['type'] : '');
            $vouchersObject->code = (isset($input['code']) ? $input['code'] : '');
            $vouchersObject->voucher_job_id = (isset($input['voucher_job_id']) ? $input['voucher_job_id'] : NULL);
            $vouchersObject->save();

            return self::transform( $vouchersObject, new VoucherTransformer());

        }catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    public function update($id, $input)
    {
        try{
            $vouchersObject = $this->model->find($id);
            $vouchersObject->valid_from = (isset($input['valid_from']) ? $input['valid_from'] : $vouchersObject->valid_from);
            $vouchersObject->valid_to = (isset($input['valid_to']) ? $input['valid_to'] : $vouchersObject->valid_to);
            $vouchersObject->status = (isset($input['status']) ? $input['status'] : $vouchersObject->status);
            $vouchersObject->title = (isset($input['title']) ? $input['title'] : $vouchersObject->title);
            $vouchersObject->description = (isset($input['description']) ? $input['description'] : $vouchersObject->description);
            $vouchersObject->location = (isset($input['location']) ? $input['location'] : $vouchersObject->location);
            //$vouchersObject->is_limited = $input['is_limited'];
            $vouchersObject->limit = (isset($input['limit']) ? $input['limit'] : $vouchersObject->limit);
            $vouchersObject->period = (isset($input['period']) ? $input['period'] : $vouchersObject->period);
            $vouchersObject->duration = (isset($input['duration']) ? $input['duration'] : $vouchersObject->duration);
            $vouchersObject->category = (isset($input['category']) ? $input['category'] : $vouchersObject->category);
            $vouchersObject->type = (isset($input['type']) ? $input['type'] : $vouchersObject->type);
            $vouchersObject->code = (isset($input['code']) ? $input['code'] : $vouchersObject->code);
            $vouchersObject->voucher_job_id = (isset($input['voucher_job_id']) ? $input['voucher_job_id'] : $vouchersObject->voucher_job_id);
            $vouchersObject->save();

            return self::transform( $vouchersObject, new VoucherTransformer());

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
            $vouchers = $this->model->where('voucher_job_id', '=', $params['job_id'])
                ->select([
                    DB::raw('vouchers.*'),
                    DB::raw('sum(case when `action` = \'success\' then 1 else 0 end) as `total_redeemed`')
                ])
                ->leftJoin('voucher_logs', 'voucher_logs.voucher_id', '=', 'vouchers.id')
                ->groupBy('voucher_id')
                ->skip($params['start'])
                ->take($params['limit'])
                ->get();

            return self::transform($vouchers, new VoucherTransformer());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Sets the voucher status a user uses in subscribing to claiming,
     * until subscription is processed.
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

    public function insertVoucherJob($status)
    {
        try {
            $voucherJob = new VoucherJob();
            if ($status == 'new') {
                $voucherJob->status = 'new';
                $voucherJob->comments = 'New VoucherJob Inserted';
            } elseif($status == 'processing') {
                $voucherJob->status = 'processing';
                $voucherJob->comments = 'Processing VoucherJob.........';
            } elseif($status == 'completed') {
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
