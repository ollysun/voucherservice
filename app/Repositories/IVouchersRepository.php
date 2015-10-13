<?php namespace Voucher\Repositories;
/**
 * Created by PhpStorm.
 * User: Tech-1
 * Date: 10/13/15
 * Time: 12:24 PM
 */

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