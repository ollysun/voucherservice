<?php namespace Voucher\Repositories;

use Voucher\Models\VoucherCode;
use Voucher\Transformers\VoucherCodeTransformer;

class VoucherCodesRepository extends AbstractRepository implements IVoucherCodesRepository
{
    /** VoucherCode model.
     *
     * @var VoucherCode
     */
    protected $model;

    /** Creates a new VoucherCode repository instances
     *
     * @param VoucherCode $voucherCode
     */
    public function __construct(VoucherCode $voucherCode)
    {
        $this->model = $voucherCode;
    }

    /** Checks is a code exists in the voucher_code table.
     *
     * @param $code
     * @return bool
     * @throws \Exception
     */
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

    /**
     * Adds a new code to voucher_code table.
     *
     * @param $data
     * @return mixed
     * @throws \Exception
     */
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
