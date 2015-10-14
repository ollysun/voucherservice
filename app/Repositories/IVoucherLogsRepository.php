<?php namespace Voucher\Repositories;

interface IVoucherLogsRepository
{
    public function addVoucherLog($data);

    public function getVoucherRedeemedCount($voucher_id);
}
