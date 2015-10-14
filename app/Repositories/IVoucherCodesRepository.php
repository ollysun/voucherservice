<?php namespace Voucher\Repositories;

interface IVoucherCodesRepository
{
    public function isNotExistingVoucherCode($code);

    public function insertVoucherCode($data);
}
