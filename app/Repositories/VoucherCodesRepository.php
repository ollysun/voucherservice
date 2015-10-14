<?php
/**
 * Created by PhpStorm.
 * User: tech7
 * Date: 10/12/15
 * Time: 12:52 PM
 */

namespace Voucher\Repositories;

use Voucher\Models\VoucherCode;
use Voucher\Transformers\VoucherCodeTransformer;

class VoucherCodesRepository extends AbstractRepository implements IVoucherCodesRepository
{
    public function __construct(VoucherCode $voucherCode)
    {
        $this->model = $voucherCode;
    }

    public function isNotExistingVoucherCode($code)
    {
        try {
            $code = $this->model->where('voucher_code', $code)->get();

            if ($code->isEmpty()) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function insertVoucherCode($data)
    {
        try {
            $voucherCodeObject = new VoucherCode();

            $voucherCodeObject->voucher_code = $data['voucher_code'];
            $voucherCodeObject->code_status = $data['voucher_status'];
            $voucherCodeObject->save();

            return self::transform($voucherCodeObject, new VoucherCodeTransformer());

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}