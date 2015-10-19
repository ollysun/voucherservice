<?php

use Voucher\Models\Voucher;
use Voucher\Models\VoucherCode;
use Voucher\Models\VoucherJobParamMetadata;
use Voucher\Models\VoucherLog;
use Voucher\Repositories\VouchersRepository;

class VouchersRepositoryTest extends TestCase
{
    protected $voucher_model;
    protected $voucher_log_model;
    protected $voucher_param_model;
    protected $voucher_code_model;
    protected $repository;

    public function setUp()
    {
        parent::setUp();
        $this->voucher_model = new Voucher();
        $this->voucher_log_model = new VoucherLog();
        $this->voucher_param_model = new VoucherJobParamMetadata();
        $this->voucher_code_model = new VoucherCode();
        $this->repository = new VouchersRepository(
            $this->voucher_model,
            $this->voucher_log_model,
            $this->voucher_param_model,
            $this->voucher_code_model
        );
    }

    public function testGetVouchers()
    {
        $voucher_job_model = new \Voucher\Models\VoucherJob();
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
            'is_limited' => 0,
            'limit' => 2,
            'voucher_job_id' => 9990
        ];
        $this->voucher_model->insert($data);

        $this->voucher_log_model->insert([
            'voucher_id' => 9999,
            'user_id' => '9999a',
            'action' => 'attempt',
            'platform' => 'mobile',
            'comments' => 'a comment'
        ]);

        $params = [
            'query' => null,
            'order' => 'ASC',
            'sort' => 'created_at',
            'limit' => 5,
            'offset' => 1
        ];

        $result = $this->repository->getVouchers($params);
        $this->assertNotEmpty($result['data']);
    }

    public function testGetVouchersQueryNotNull()
    {
        $voucher_job_model = new \Voucher\Models\VoucherJob();
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
            'is_limited' => 0,
            'limit' => 2,
            'voucher_job_id' => 9990
        ];
        $this->voucher_model->insert($data);

        $this->voucher_log_model->insert([
            'voucher_id' => 9999,
            'user_id' => '9999a',
            'action' => 'attempt',
            'platform' => 'mobile',
            'comments' => 'a comment'
        ]);

        $params = [
            'query' => 'a',
            'order' => 'ASC',
            'sort' => 'created_at',
            'limit' => 5,
            'offset' => 1
        ];

        $result = $this->repository->getVouchers($params);
        $this->assertNotEmpty($result['data']);
    }

    public function testGetVouchersNull()
    {
        $params = [
            'query' => 'xyz123456789',
            'order' => 'ASC',
            'sort' => 'created_at',
            'limit' => 5,
            'offset' => 1
        ];

        $result = $this->repository->getVouchers($params);
        $this->assertNull($result);
    }

    public function testGetVouchersErrorException()
    {
        $params = [
            'query' => 'xyz123456789',
            'order' => 'ASC',
            'sort' => 'created_at',
            'limit' => 5,
            'offset' => 1
        ];

        $this->setExpectedException('\Exception');
        $this->voucher_model = $this->getMock(Voucher::class, ['orderBy']);
        $this->voucher_model->expects($this->any())->method('orderBy')->willThrowException(new \Exception());

        $this->repository = new VouchersRepository(
            $this->voucher_model,
            $this->voucher_log_model,
            $this->voucher_param_model,
            $this->voucher_code_model
        );
        $this->repository->getVouchers($params);
    }

    public function testGetVoucherById()
    {
        $voucher_job_model = new \Voucher\Models\VoucherJob();
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
            'is_limited' => 0,
            'limit' => 2,
            'voucher_job_id' => 9990
        ];
        $this->voucher_model->insert($data);

        $this->voucher_log_model->insert([
            'voucher_id' => 9999,
            'user_id' => '9999a',
            'action' => 'attempt',
            'platform' => 'mobile',
            'comments' => 'a comment'
        ]);

        $result = $this->repository->getVoucherById('9999');
        $this->assertEquals($data['id'], $result['data']['id']);
        $this->assertEquals($data['code'], $result['data']['code']);
    }

    public function testGetVoucherByIdNull()
    {
        $result = $this->repository->getVoucherById('9999a');
        $this->assertNull($result);
    }

    public function testGetVoucherByIdErrorException()
    {
        $this->setExpectedException('\Exception');
        $this->voucher_model = $this->getMock(Voucher::class, ['where']);
        $this->voucher_model->expects($this->any())->method('where')->willThrowException(new \Exception());

        $this->repository = new VouchersRepository(
            $this->voucher_model,
            $this->voucher_log_model,
            $this->voucher_param_model,
            $this->voucher_code_model
        );
        $this->repository->getVoucherById('xyz123');
    }

    public function testGetVoucherByCode()
    {
        $voucher_job_model = new \Voucher\Models\VoucherJob();
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
            'is_limited' => 0,
            'limit' => 2,
            'voucher_job_id' => 9990
        ];
        $this->voucher_model->insert($data);

        $this->voucher_log_model->insert([
            'voucher_id' => 9999,
            'user_id' => '9999a',
            'action' => 'attempt',
            'platform' => 'mobile',
            'comments' => 'a comment'
        ]);

        $result = $this->repository->getVoucherByCode('12345678abc');
        $this->assertEquals($data['code'], $result['data']['code']);
        $this->assertEquals($data['id'], $result['data']['id']);
    }

    public function testGetVoucherByCodeFalse()
    {
        $result = $this->repository->getVoucherByCode('123456789abc');
        $this->assertFalse($result);
    }

    public function testGetVoucherByCodeErrorException()
    {
        $this->setExpectedException('\Exception');
        $this->voucher_model = $this->getMock(Voucher::class, ['where']);
        $this->voucher_model->expects($this->any())->method('where')->willThrowException(new \Exception());

        $this->repository = new VouchersRepository(
            $this->voucher_model,
            $this->voucher_log_model,
            $this->voucher_param_model,
            $this->voucher_code_model
        );
        $this->repository->getVoucherByCode('123456789abc');
    }

    public function testGetVoucherCodeByStatus()
    {
        $this->voucher_code_model->insert(['voucher_code' => '123456789abc', 'code_status' => 'new']);
        $this->voucher_code_model->insert(['voucher_code' => '123456789abcd', 'code_status' => 'used']);

        $result = $this->repository->getVoucherCodeByStatus('new');
        $this->assertEquals('new', $result['data']['code_status']);
        $this->assertEquals('123456789abc', $result['data']['voucher_code']);

        $result = $this->repository->getVoucherCodeByStatus('used');
        $this->assertEquals('used', $result['data']['code_status']);
        $this->assertEquals('123456789abcd', $result['data']['voucher_code']);
    }

    public function testGetVoucherCodeByStatusFalse()
    {
        $this->voucher_code_model->truncate();

        $result = $this->repository->getVoucherCodeByStatus('new');
        $this->assertFalse($result);
    }

    public function testGetVoucherCodeByStatusErrorException()
    {
        $this->setExpectedException('\Exception');
        $this->voucher_code_model = $this->getMock(VoucherCode::class, ['first']);
        $this->voucher_code_model->expects($this->any())->method('first')->willThrowException(new \Exception());

        $this->repository = new VouchersRepository(
            $this->voucher_model,
            $this->voucher_log_model,
            $this->voucher_param_model,
            $this->voucher_code_model
        );

        $this->repository->getVoucherCodeByStatus('new');
    }

    public function testUpdateVoucherCodeStatusByID()
    {
        $this->voucher_code_model->insert(['id' => '9990', 'voucher_code' => '123456789abc', 'code_status' => 'new']);

        $result = $this->repository->updateVoucherCodeStatusByID('9990');
        $this->assertEquals('used', $result['data']['code_status']);
        $this->assertEquals('123456789abc', $result['data']['voucher_code']);
    }

    public function testUpdateVoucherCodeStatusByIDErrorException()
    {
        $this->setExpectedException('\Exception');
        $this->voucher_code_model = $this->getMock(VoucherCode::class, ['find']);
        $this->voucher_code_model->expects($this->any())->method('find')->willThrowException(new \Exception());

        $this->repository = new VouchersRepository(
            $this->voucher_model,
            $this->voucher_log_model,
            $this->voucher_param_model,
            $this->voucher_code_model
        );

        $this->repository->updateVoucherCodeStatusByID('9990');
    }
}
