<?php namespace Voucher\Repositories;

interface IVouchersRepository
{
    public function getVouchers($data);

    public function getVoucherById($id);

    public function getVoucherByCode($code);

    public function createOrUpdate($id, $input);

    public function getByJobId($job, $offset, $limit);

    public function generateVoucherWithStoredProcedure($params);
}