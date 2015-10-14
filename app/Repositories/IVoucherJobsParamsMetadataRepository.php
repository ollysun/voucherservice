<?php
/**
 * Created by PhpStorm.
 * User: tech7
 * Date: 10/14/15
 * Time: 8:15 AM
 */

namespace Voucher\Repositories;

interface IVoucherJobsParamsMetadataRepository
{
    public function getJobParams($job);
}