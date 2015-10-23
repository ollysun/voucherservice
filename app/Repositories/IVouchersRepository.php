<?php namespace Voucher\Repositories;

interface IVouchersRepository
{
    public function getVouchers($data);

    public function getVoucherById($id);

    public function getVoucherByCode($code);

    public function getVoucherCodeByStatus($status);

    public function updateVoucherCodeStatusByID($id);

    public function create($input);

    public function update($id, $input);

    public function getVouchersByJobIdAndLimit($params);

    public function updateVoucherStatus($data);

    public function insertVoucherJob($status);

    public function insertVoucherJobParamMetadata($data, $id);


}
