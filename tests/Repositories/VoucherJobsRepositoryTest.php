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

    public function testGetJobsFalse()
    {
        //Ensure there is no data in table
        $this->model->get();
        $this->model->update(["status" => "completed"]);

        $result = $this->repository->getJobs();
        $this->assertFalse($result);
    }

    public function testGetJobsErrorException()
    {
        $this->model = \Mockery::mock(VoucherJob::class);
        $this->repository = new VoucherJobsRepository($this->model);

        $this->model->shouldReceive('where')
            ->atLeast(1)
            ->andThrow(new \Exception("Mock Exception"));

        $this->setExpectedException('\Exception');
        $this->repository->getJobs();
    }

    public function testUpdateJobStatus()
    {
        DB::table('voucher_jobs')
            ->insert([
                'id' => 9999,
                'status' => 'new',
                'comments' => 'a comment'
            ]);

        $data = [
            'voucher_job_id' => 9999,
            'comments' => 'test comment',
            'status' => 'processing'
        ];

        $result = $this->repository->updateJobStatus($data);
        $this->assertTrue($result);
    }

    public function testUpdateJobStatusErrorException()
    {
        $data = [
            'voucher_job_id' => '999a',
            'comments' => 'test comment',
            'status' => 'processing'
        ];

        $this->setExpectedException('\Exception');
        $this->repository->updateJobStatus($data);
    }

    public function testAddVouchers()
    {
        //Add Voucher Job before test
        DB::table('voucher_jobs')
            ->insert([
                'id' => 9999,
                'status' => 'new',
                'comments' => 'a comment'
            ]);

        $data = [
            'status' => 'active',
            'category' => 'new',
            'title' => 'INTERNAL',
            'location' => 'Nigeria',
            'description' => 'a description',
            'duration' => 3,
            'period' => 'day',
            'valid_from' => '2015-10-08 00:00:00',
            'valid_to' => '2015-12-30 00:00:00',
            'limit' => '1',
            'brand' => 'a brand',
            'total' => '2',
            'voucher_job_id' => 9999
        ];

        $result = $this->repository->addVouchers($data);
        $this->assertTrue($result);
    }

    public function testAddVouchersErrorException()
    {
        $data = [
            'status' => 'old'
        ];

        $this->setExpectedException('\Exception');
        $this->repository->addVouchers($data);
    }
}