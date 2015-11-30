<?php namespace Voucher\Repositories;

interface IVoucherLogsRepository
{
    public function addVoucherLog($data);

    public function redeemedByUser($user_id, $voucher_id);
}
