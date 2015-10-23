<?php

use Voucher\Models\Voucher;
use Voucher\Models\VoucherCode;
use Voucher\Models\VoucherJob;
use Voucher\Models\VoucherJobParamMetadata;
use Voucher\Models\VoucherLog;
use Voucher\Repositories\VouchersRepository;

class VouchersRepositoryTest extends TestCase
{
    protected $voucher_model;
    protected $voucher_log_model;
    protected $voucher_param_model;
    protected $voucher_code_model;
    protected $voucher_job_model;
    protected $repository;

    public function setUp()
    {
        parent::setUp();
        $this->voucher_model = new Voucher();
        $this->voucher_log_model = new VoucherLog();
        $this->voucher_param_model = new VoucherJobParamMetadata();
        $this->voucher_code_model = new VoucherCode();
        $this->voucher_job_model = new VoucherJob();
        $this->repository = new VouchersRepository(
            $this->voucher_model,
            $this->voucher_param_model,
            $this->voucher_code_model,
            $this->voucher_job_model
        );
    }

    public function testGetVouchers()
    {
        $this->voucher_job_model = new VoucherJob();
        $this->voucher_job_model->insert(['id' => 9990, 'status' => 'new', 'comments' => 'a comment']);

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
        $this->voucher_job_model = new VoucherJob();
        $this->voucher_job_model->insert(['id' => 9990, 'status' => 'new', 'comments' => 'a comment']);

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
            $this->voucher_param_model,
            $this->voucher_code_model,
            $this->voucher_job_model
        );
        $this->repository->getVouchers($params);
    }

    public function testGetVoucherById()
    {
        $this->voucher_job_model = new VoucherJob();
        $this->voucher_job_model->insert(['id' => 9990, 'status' => 'new', 'comments' => 'a comment']);

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
            $this->voucher_param_model,
            $this->voucher_code_model,
            $this->voucher_job_model
        );
        $this->repository->getVoucherById('xyz123');
    }

    public function testGetVoucherByCode()
    {
        $this->voucher_job_model = new VoucherJob();
        $this->voucher_job_model->insert(['id' => 9990, 'status' => 'new', 'comments' => 'a comment']);

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
            $this->voucher_param_model,
            $this->voucher_code_model,
            $this->voucher_job_model
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
            $this->voucher_param_model,
            $this->voucher_code_model,
            $this->voucher_job_model
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
            $this->voucher_param_model,
            $this->voucher_code_model,
            $this->voucher_job_model
        );

        $this->repository->updateVoucherCodeStatusByID('9990');
    }

    public function testCreate()
    {
        $this->voucher_job_model = new VoucherJob();
        $this->voucher_job_model->insert(['id' => 9990, 'status' => 'new', 'comments' => 'a comment']);

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

        $result = $this->repository->create($data);
        $this->assertEquals($data['code'], $result['data']['code']);
    }

    public function testCreateErrorException()
    {
        $this->setExpectedException('\Exception');
        $this->voucher_model = $this->getMock(Voucher::class, ['save']);
        $this->voucher_model->expects($this->any())->method('save')->willThrowException(new \Exception());

        $this->repository = new VouchersRepository(
            $this->voucher_model,
            $this->voucher_param_model,
            $this->voucher_code_model,
            $this->voucher_job_model
        );

        $this->repository->create('');
    }

    public function testUpdate()
    {
        $this->voucher_job_model = new VoucherJob();
        $this->voucher_job_model->insert(['id' => 9990, 'status' => 'new', 'comments' => 'a comment']);

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

        $result = $this->repository->update(9999, ['status' => 'claiming']);
        $this->assertEquals('claiming', $result['data']['status']);
    }

    public function testUpdateErrorException()
    {
        $this->setExpectedException('\Exception');
        $this->voucher_model = $this->getMock(Voucher::class, ['save']);
        $this->voucher_model->expects($this->any())->method('save')->willThrowException(new \Exception());

        $this->repository = new VouchersRepository(
            $this->voucher_model,
            $this->voucher_param_model,
            $this->voucher_code_model,
            $this->voucher_job_model
        );

        $this->repository->update('', []);
    }

    public function testGetVouchersByJobIdAndLimit()
    {
        $this->voucher_job_model = new VoucherJob();
        $this->voucher_job_model->insert(['id' => 9990, 'status' => 'new', 'comments' => 'a comment']);

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

        $data['code'] = '12345678abd';
        $data['id'] = '9998';
        $this->voucher_model->insert($data);

        $params = [
            'voucher_job_id' => 9990,
            'start' => 0,
            'limit' => 2
        ];

        $result = $this->repository->getVouchersByJobIdAndLimit($params);
        $this->assertEquals($data['code'], $result[0]['code']);
        $this->assertCount($params['limit'], $result);
    }

    public function testGetVouchersByJobIdAndLimitErrorException()
    {
        $this->setExpectedException('\Exception');
        $this->voucher_model = $this->getMock(Voucher::class, ['save']);
        $this->voucher_model->expects($this->any())->method('save')->willThrowException(new \Exception());

        $this->repository = new VouchersRepository(
            $this->voucher_model,
            $this->voucher_param_model,
            $this->voucher_code_model,
            $this->voucher_job_model
        );

        $this->repository->getVouchersByJobIdAndLimit([]);
    }

    public function testUpdateVoucherStatus()
    {
        $this->voucher_job_model = new VoucherJob();
        $this->voucher_job_model->insert(['id' => 9990, 'status' => 'new', 'comments' => 'a comment']);

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

        $params = [
            'voucher_id' => 9999,
            'voucher_status' => 'claiming',
            'limit' => 1
        ];

        $result = $this->repository->updateVoucherStatus($params);
        $this->assertTrue($result);
    }

    public function testUpdateVoucherStatusErrorException()
    {
        $this->setExpectedException('\Exception');
        $this->voucher_model = $this->getMock(Voucher::class, ['save']);
        $this->voucher_model->expects($this->any())->method('save')->willThrowException(new \Exception());

        $this->repository = new VouchersRepository(
            $this->voucher_model,
            $this->voucher_param_model,
            $this->voucher_code_model,
            $this->voucher_job_model
        );

        $this->repository->updateVoucherStatus([]);
    }

    public function testInsertVoucherJobNew()
    {
        $result = $this->repository->insertVoucherJob('new');
        $this->assertEquals('new', $result['data']['status']);
    }

    public function testInsertVoucherJobProcessing()
    {
        $result = $this->repository->insertVoucherJob('processing');
        $this->assertEquals('processing', $result['data']['status']);
    }

    public function testInsertVoucherJobCompleted()
    {
        $result = $this->repository->insertVoucherJob('completed');
        $this->assertEquals('completed', $result['data']['status']);
    }

    public function testInsertVoucherJobErrorException()
    {
        $this->setExpectedException('\Exception');
        $this->voucher_job_model = $this->getMock(VoucherJob::class, ['save']);
        $this->voucher_job_model->expects($this->any())->method('save')->willThrowException(new \Exception());

        $this->repository = new VouchersRepository(
            $this->voucher_model,
            $this->voucher_param_model,
            $this->voucher_code_model,
            $this->voucher_job_model
        );

        $this->repository->insertVoucherJob([]);
    }

    public function testInsertVoucherJobParamMetadata()
    {
        $this->voucher_job_model = new VoucherJob();
        $this->voucher_job_model->insert(['id' => 9990, 'status' => 'new', 'comments' => 'a comment']);

        $data = [
            'status' => 'active',
            'category' => 'new'
        ];

        $result = $this->repository->insertVoucherJobParamMetadata($data, 9990);
        $this->assertTrue($result);
    }

    public function testInsertVoucherJobParamMetadataErrorException()
    {
        $this->setExpectedException('\Exception');
        $this->voucher_param_model = $this->getMock(VoucherJobParamMetadata::class, ['save']);
        $this->voucher_param_model->expects($this->any())->method('save')->willThrowException(new \Exception());

        $this->repository = new VouchersRepository(
            $this->voucher_model,
            $this->voucher_param_model,
            $this->voucher_code_model,
            $this->voucher_job_model
        );

        $data = [
            'status' => 'active',
            'category' => 'new'
        ];

        $this->repository->insertVoucherJobParamMetadata($data, 9990);
    }
}
