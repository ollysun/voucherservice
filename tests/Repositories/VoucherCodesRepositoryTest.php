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
//        $this->setExpectedException('\Exception');

        $this->model = $this->getMock('Voucher\Models\VoucherCodes', ['where']);
        $this->model->expects($this->any())->method('where')->willThrowException(new \Exception());

        $this->repository = new VoucherCodesRepository($this->model);
        $this->repository->isNotExistingVoucherCode('xxxxxxxx9');
    }
}