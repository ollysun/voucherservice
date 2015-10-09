<?php namespace Voucher\Repositories;

interface IVouchersRepository
{
    public function getVoucherByCode($data);
}