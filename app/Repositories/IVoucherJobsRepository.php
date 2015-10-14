<?php
/**
 * Created by PhpStorm.
 * User: tech7
 * Date: 10/14/15
 * Time: 8:17 AM
 */

namespace Voucher\Repositories;

interface IVoucherJobsRepository
{
    public function getJobs();

    public function updateJobStatus($params);
}