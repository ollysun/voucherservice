<?php namespace Voucher\Repositories;

use Illuminate\Pagination\Paginator;
use Voucher\Models\Voucher;
use Voucher\Models\VoucherLog;
use Illuminate\Support\Facades\DB;
use Voucher\Transformers\VoucherTransformer;
use Voucher\Transformers\VoucherLogTransformer;
use Voucher\Models\VoucherJob;
use Voucher\Models\Voucher_jobs_params_metadata;
use Voucher\Transformers\VoucherJobParamMetadataTransformer;
use Voucher\Transformers\VoucherJobTransformer;

use Voucher\Payment\Event;

class VouchersRepository extends AbstractRepository implements IVouchersRepository
{
    protected $model;

    protected $log_model;
    protected $error;

    public function __construct(Voucher $voucher, VoucherLog $voucherLog)
    {
        $this->model = $voucher;
        $this->log_model = $voucherLog;
    }

    public function getVoucherByCode($data)
    {

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

    public function getByJobId($job, $offset, $limit)
    {
        try {
            $vouchers = $this->model->where('voucher_job_id', '=', $job->id)
                ->limit($limit)
                ->skip($offset)
                ->get();

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
           $voucherMetadata = new Voucher_jobs_params_metadata();
            $voucherMetadata->voucher_job_id = $data['voucher_job_id'];
            $voucherMetadata->key = $data['key'];
            $voucherMetadata->value = $data['value'];
            $voucherMetadata->save();
            return self::transform($voucherMetadata, new VoucherJobParamMetadataTransformer());

        }catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }


}
