<?php
/**
 * Created by PhpStorm.
 * User: tech7
 * Date: 10/12/15
 * Time: 12:54 PM
 */

namespace Voucher\Repositories;

interface IVoucherCodesRepository
{
    public function isExistingVoucherCode($code);

    public function insertVoucherCode($data);
}