<?php namespace Voucher\Repositories;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Voucher\Models\Voucher;
use Voucher\Models\VoucherJobParamMetadata;
use Voucher\Models\VoucherLog;
use Voucher\Transformers\VoucherTransformer;
use Voucher\Models\VoucherJob;
use Voucher\Transformers\VoucherJobParamMetadataTransformer;
use Voucher\Transformers\VoucherJobTransformer;
use Voucher\Voucher\Event;

class VouchersRepository extends AbstractRepository
{
    protected $model;

    protected $log_model;
    protected $error;
    protected $voucherMetadata;
    public function __construct(Voucher $voucher, VoucherLog $voucherLog, VoucherJobParamMetadata $voucherMetadataModel)
    {
        $this->model = $voucher;
        $this->log_model = $voucherLog;
        $this->voucherMetadata = $voucherMetadataModel;
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
                $vouchers = $this->model->orderBy($data['sort'], $data['order'])
                    ->paginate($data['limit']);

            } else {
                $vouchers = $this->model->where('code', 'like', '%'.$data['query'].'%')
                    ->orderBy($data['sort'], $data['order'])
                    ->paginate($data['limit']);
            }
            if ($vouchers->isEmpty()) {
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
            $voucher = $this->model->find($id);

            if (!is_null($voucher)) {
                return self::transform($voucher, new VoucherTransformer());
            } else {
                return null;
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getVoucherByCode($code)
    {
        try {
            $voucher = $this->model->where('code', $code)->get();

            if (!is_null($voucher)) {
                return self::transform($voucher, new VoucherTransformer());
            } else {
                return false;
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function createOrUpdate($id = null, $input)
    {
        try{
            if (is_null($id)) {
                $vouchersObject = $this->model;
            } else {
                $vouchersObject = $this->model->find($id);
            }
            $vouchersObject->valid_from = $input['valid_from'];
            $vouchersObject->valid_to = $input['valid_to'];
            $vouchersObject->status = $input['status'];
            $vouchersObject->title = $input['title'];
            $vouchersObject->description = $input['description'];
            $vouchersObject->location = $input['location'];
            $vouchersObject->is_limited = $input['is_limited'];
            $vouchersObject->limit = $input['limit'];
            $vouchersObject->period = $input['period'];
            $vouchersObject->duration = $input['duration'];
            $vouchersObject->category = $input['category'];
            $vouchersObject->type = $input['type'];
            $vouchersObject->code = $input['code'];
            $vouchersObject->save();

            return self::transform( $vouchersObject, new VoucherTransformer());

        }catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    public function getByJobIdAndLimit($params)
    {
        try {
            $vouchers = $this->model->where('voucher_job_id', '=', $params['job_id'])
                ->skip($params['start'])
                ->take($params['limit']);

            return self::transform($vouchers, new VoucherTransformer());

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function generateVoucherWithStoredProcedure($params)
    {
        try {
            $vouchers = DB::statement(
                DB::raw(
                    'CALL generate_voucher("' .
                    $params["status"] . '","' .
                    $params["category"] . '","' .
                    $params["title"] . '","' .
                    $params["location"] . '","' .
                    $params["description"] . '",' .
                    $params["duration"] . ',"' .
                    $params["period"] . '","' .
                    $params["valid_from"] . '","' .
                    $params["valid_to"] . '","' .
                    $params["is_limited"] . '",' .
                    $params["limit"] . ',"' .
                    $params["brand"] . '",' .
                    $params["total"] . ',' .
                    $params["job_id"].
                    ')'
                ));
            return $vouchers;
        } catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }

    public function insertVoucherJob($status)
    {
        try{
            $voucherJob = new VoucherJob();
            if($status == 'new')
            {
                $voucherJob->status = 'new';
                $voucherJob->comments = 'New VoucherJob Inserted';
            }elseif($status == 'processing')
            {
                $voucherJob->status = 'processing';
                $voucherJob->comments = 'Processing VoucherJob.........';
            }elseif($status == 'completed')
            {
                $voucherJob->status = 'completed';
                $voucherJob->comments = 'VoucherJob Complete processing';
            }else
            {
                $voucherJob->status = 'error';
                $voucherJob->comments = 'Error processing the Voucher';
            }
            $voucherJob->save();
            return self::transform($voucherJob, new VoucherJobTransformer());
        }catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    public function insertVoucherJobParamMetadata($data)
    {
        try{
            $value_job = $data['voucher_job_id'];
            foreach($data['arrayCombineKeyValue'] as $key => $value)
            {
                $voucherMetadata = $this->voucherMetadata;
                if (in_array($key, $data['arrayCombineKeyValue'])) {
                    $voucherMetadata->voucher_job_id = $value_job;
                    $voucherMetadata->key = trim($key);
                    $voucherMetadata->value = trim($value);
                    $voucherMetadata->save();
                }
            }
            return self::transform($this->voucherMetadata->all(), new VoucherJobParamMetadataTransformer());
        }catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
}