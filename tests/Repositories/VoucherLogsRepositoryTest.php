<?php

use Voucher\Models\VoucherLog;
use Voucher\Models\Voucher;
use Voucher\Repositories\VoucherLogsRepository;

class VoucherLogsRepositoryTest extends TestCase
{
    protected $model;
    protected $repository;
    protected  $voucher_model;

    public function setUp()
    {
        parent::setUp();
        $this->model = new VoucherLog();
        $this->voucher_model = new Voucher();
        $this->repository = new VoucherLogsRepository($this->model, $this->voucher_model);
    }

    public function testAddVoucherLog()
    {
        $voucher_job_model = new \Voucher\Models\VoucherJob();
        $voucher_model = new \Voucher\Models\Voucher();

        $voucher_job_model->insert(['id' => 9990, 'status' => 'new', 'comments' => 'a comment']);
        $data = [
            'id' => 9999,
            'code' => '12345678abc',
            'type' => 'time',
            'status' => 'active',
            'category' => 'new',
            'title' => 'INTERNAL',
            'location' => 'Nigeria',
            'description' => 'description',
            'duration' => 3,
            'period' => 'day',
            'valid_from' => '2015-10-08 00:00:00',
            'valid_to' => '2015-12-30 00:00:00',
            'is_limited' => 1,
            'limit' => 0,
            'voucher_job_id' => 9990
        ];
        $voucher_model->insert($data);

        $result = $this->repository->addVoucherLog([
            'voucher_id' => 9999,
            'user_id' => '9999a',
            'action' => 'attempt',
            'platform' => 'mobile',
            'comments' => 'a comment'
        ]);
        $this->assertEquals(9999, $result['data']['voucher_id']);
        $this->assertEquals('9999a', $result['data']['user_id']);
    }

    public function testAddVoucherLogErrorException()
    {
        $data = [
            'voucher_id' => 9999,
            'user_id' => '9999a',
            'action' => 'attempt',
            'platform' => 'mobile',
            'comments' => 'a comment'
        ];

        $this->setExpectedException('\Exception');
        $this->repository->addVoucherLog($data);
    }
}