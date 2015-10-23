<?php

use Voucher\Models\VoucherCode;
use Voucher\Repositories\VoucherCodesRepository;

class VoucherCodesRepositoryTest extends TestCase
{
    protected $model;

    protected $repository;

    public function setUp()
    {
        parent::setUp();
        $this->model = new VoucherCode();
        $this->repository = new VoucherCodesRepository($this->model);
    }

    public function testIsNotExistingVoucherCode()
    {
        $result = $this->repository->isNotExistingVoucherCode('xxxxxxxx9');
        $this->assertTrue($result);
    }

    public function testIsNotExistingVoucherCodeFalse()
    {
        $data = [
            'voucher_status' => 'new',
            'voucher_code' => 'abc123'
        ];

        $code = $this->repository->insertVoucherCode($data);
        $this->assertNotNull($code);

        $result = $this->repository->isNotExistingVoucherCode('abc123');
        $this->assertFalse($result);
    }

    public function testIsNotExistingVoucherCodeErrorException()
    {
        $this->model = \Mockery::mock(VoucherCode::class);
        $this->repository = new VoucherCodesRepository($this->model);

        $this->model->shouldReceive('where')
            ->atLeast(1)
            ->andThrow(new \Exception("Mock Exception"));

        $this->setExpectedException('\Exception');
        $this->repository->isNotExistingVoucherCode('xxxxxxxx9');
    }

    public function testInsertVoucherCode()
    {
        $data = [
            'voucher_status' => 'new',
            'voucher_code' => 'abc123'
        ];

        $code = $this->repository->insertVoucherCode($data);
        $this->assertEquals('abc123', $code['data']['voucher_code']);
    }

    public function testInsertVoucherCodeErrorException()
    {
        $data = [
            'voucher_status' => 'new'
        ];

        $this->setExpectedException('\Exception');
        $this->repository->insertVoucherCode($data);
    }
}