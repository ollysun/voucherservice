<?php namespace Voucher\Repositories;

interface IVouchersRepository
{
    public function getVouchers($data);

    public function getVoucherById($id);

    public function getVoucherByCode($code);

    public function create($input);

    public function update($id, $input);

    public function getVouchersByJobIdAndLimit($params);

    public function insertVoucherJob($status);

    public function insertVoucherJobParamMetadata($data, $id);

    public function setVoucherStatusToClaiming($data);
}
