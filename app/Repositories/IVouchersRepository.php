<?php namespace Voucher\Repositories;

interface IVouchersRepository
{
    public function getVouchers($data);

    public function getVoucherById($id);

    public function getVoucherByCode($code);

    public function createOrUpdate($id, $input);

    public function getByJobId($job);

    public function generateVoucherWithStoredProcedure($params);

    public function insertVoucherJob($status);

    public function insertVoucherJobParamMetadata($data);
}