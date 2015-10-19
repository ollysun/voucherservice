<?php

use Illuminate\Support\Facades\DB;
use Voucher\Models\VoucherJob;
use Voucher\Repositories\VoucherJobsRepository;

class VoucherJobsRepositoryTest extends TestCase
{
    protected $model;
    protected $repository;

    public function setUp()
    {
        parent::setUp();
        $this->model = new VoucherJob();
        $this->repository = new VoucherJobsRepository($this->model);
    }

    public function testGetJobs()
    {
        DB::table('voucher_jobs')
            ->insert([
                'status' => 'new',
                'comments' => 'a comment'
            ]);

        $result = $this->repository->getJobs();
        $this->assertNotNull($result['data'][0]['id']);
    }

//    public function testGetJobsFalse()
//    {
//        $this->model->truncate();
//
//        $result = $this->repository->getJobs();
//        $this->assertEmpty($result);
//    }
}