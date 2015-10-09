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

    public function getVoucherById($id)
    {
        try {
            $voucher = $this->model->find($id);

            if (!is_null($voucher)) {
                return self::transform($voucher, new VoucherTransformer());
            } else {
                return null;
            }
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
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


}
