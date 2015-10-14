<?php namespace Voucher\Repositories;

interface IVoucherJobsRepository
{
    public function getJobs();

    public function updateJobStatus($params);

    public function issueCodesFromVoucherCodesTableToVouchersTable($params);
}
