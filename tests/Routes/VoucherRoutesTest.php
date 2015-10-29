<?php
use Illuminate\Http\Response;
use Voucher\Models\Voucher;
use Voucher\Models\VoucherLog;
use Voucher\Models\VoucherCode;
use Voucher\Models\VoucherJobParamMetadata;
use Voucher\Repositories\VoucherCodesRepository;
use Voucher\Models\VoucherJob;
use Voucher\Validators\VoucherValidator;
use Illuminate\Support\Facades\Validator;

class VoucherRoutesTest extends TestCase
{
    protected $voucher_model;

    protected $repository;

    protected $voucher_log_model;

    protected $voucher_job_params_model;

    protected $voucher_code_model;

    protected $voucher_job_model;

    protected $voucher_code_repo;

    public function setUp()
    {
        parent::setUp();
        $this->voucher_model = new Voucher();
        $this->voucher_log_model = new VoucherLog();
        $this->voucher_code_model = new VoucherCode();
        $this->voucher_job_params_model = new VoucherJobParamMetadata();
        $this->voucher_job_model = new VoucherJob();
        $this->voucher_code_repo = new VoucherCodesRepository($this->voucher_code_model);
    }

    public function testVouchersGet()
    {
        $this->get('/vouchers', $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_OK);
    }

    public function testVouchersGetIncludeLogs()
    {
        $this->get('/vouchers?include=voucherLogs', $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_OK);
    }

    public function testVouchersInvalidArgsErrorLogsGet()
    {
        $this->get('/vouchers?limit=$$$$$4', $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_BAD_REQUEST);
    }

    public function testVouchersNotFoundErrorLogsGet()
    {
        $this->repository = $this->getMockBuilder('Voucher\Repositories\VouchersRepository')
            ->setConstructorArgs(array(
                $this->voucher_model,
                $this->voucher_job_params_model,
                $this->voucher_code_model,
                $this->voucher_job_model
            ))
            ->setMethods(array('getVouchers'))
            ->getMock();

        $this->repository->expects($this->any())->method('getVouchers')->willReturn(null);
        $this->app->instance('Voucher\Repositories\VouchersRepository', $this->repository);

        $this->get('/vouchers', $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }

    public function testVoucherWithInternalErrorExceptionGet()
    {
        $this->repository = $this->getMockBuilder('Voucher\Repositories\VouchersRepository')
            ->setConstructorArgs(array(
                $this->voucher_model,
                $this->voucher_job_params_model,
                $this->voucher_code_model,
                $this->voucher_job_model
            ))
            ->setMethods(array('getVouchers'))
            ->getMock();

        $this->repository->expects($this->any())->method('getVouchers')->will($this->throwException(new \Exception));
        $this->app->instance('Voucher\Repositories\VouchersRepository', $this->repository);

        $this->get('/vouchers', $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function testVouchersPost()
    {
        $code = [
            'voucher_code' => 'TE2TC0DE',
            'voucher_status' => 'new'
        ];

        $this->voucher_code_repo->insertVoucherCode($code);

        $data = [
            "type" => "time",
            "status" => "claimed",
            "category" => "new",
            "title" => "INTERNAL",
            "location" => "Nigeria",
            "description" => "A voucher",
            "duration" => 4,
            "period" => "month",
            "limit" => 1200,
            "valid_from" => "2015-10-13 02:02:02",
            "valid_to" => "2015-10-15 02:02:02",
            "voucher_job_id" => 1
        ];

        $this->call("POST", "/vouchers", $data, [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_CREATED);
    }

    public function testVoucherPostWithNotFoundErrorException()
    {
        $this->voucher_code_model->truncate();

        $data = [
            "type" => "time",
            "status" => "claimed",
            "category" => "new",
            "title" => "INTERNAL",
            "location" => "Nigeria",
            "description" => "A voucher",
            "duration" => 4,
            "period" => "month",
            "limit" => 1200,
            "valid_from" => "2015-10-13 02:02:02",
            "valid_to" => "2015-10-15 02:02:02",
            "voucher_job_id" => 1
        ];

        $this->call("POST", "/vouchers", $data, [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }

    public function testVoucherPostWithInvalidArgsErrorException()
    {
        $data = [
            "type" => "time",
            "status" => "claimed",
            "category" => "new",
            "title" => 1,
            "location" => "Nigeria",
            "description" => "A voucher",
            "duration" => "wwww",//bad parameter
            "period" => "month",
            "limit" => 1200,
            "valid_from" => "2015-10-13 02:02:02",
            "valid_to" => "2015-10-15 02:02:02",
            "code" => "fd1127",
            "voucher_job_id" => 1
        ];

        $this->call("POST", "/vouchers", $data, [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_BAD_REQUEST);
    }

    public function testVoucherPostWithInternalErrorExceptionOnCreate()
    {
        $insert_code = [
            'voucher_code' => 'TE2TC0DE2',
            'voucher_status' => 'new'
        ];

        $this->voucher_code_repo->insertVoucherCode($insert_code);

        $data = [
            "type" => "time",
            "status" => "claimed",
            "category" => "new",
            "title" => "INTERNAL",
            "location" => "Nigeria",
            "description" => "A voucher",
            "duration" => 4,
            "period" => "month",
            "limit" => 1200,
            "valid_from" => "2015-10-13 02:02:02",
            "valid_to" => "2015-10-15 02:02:02",
            "voucher_job_id" => 1
        ];

        $code = [
            'data' => [
                'voucher_code' => 'asdf1234'
            ]
        ];
        $this->repository = $this->getMock(
            'Voucher\Repositories\VouchersRepository',
            ['create', 'getVoucherCodeByStatus'],
            [
                $this->voucher_model,
                $this->voucher_job_params_model,
                $this->voucher_code_model,
                $this->voucher_job_model
            ]
        );
        $this->repository->expects($this->any())->method('getVoucherCodeByStatus')->willReturn($code);
        $this->repository->expects($this->any())->method('create')->will($this->throwException(new \Exception));
        $this->app->instance('Voucher\Repositories\VouchersRepository', $this->repository);

        $this->call("POST", "/vouchers", $data, [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function testVoucherPostWithInternalErrorException()
    {
        $insert_code = [
            'voucher_code' => 'TE2TC0DE2',
            'voucher_status' => 'new'
        ];

        $this->voucher_code_repo->insertVoucherCode($insert_code);

        $data = [
            "type" => "time",
            "status" => "claimed",
            "category" => "new",
            "title" => "INTERNAL",
            "location" => "Nigeria",
            "description" => "A voucher",
            "duration" => 4,
            "period" => "month",
            "limit" => 1200,
            "valid_from" => "2015-10-13 02:02:02",
            "valid_to" => "2015-10-15 02:02:02",
            "code" => "fd1127",
            "voucher_job_id" => 1
        ];
        $this->repository = $this->getMockBuilder('Voucher\Repositories\VouchersRepository')
            ->setConstructorArgs(array(
                $this->voucher_model,
                $this->voucher_job_params_model,
                $this->voucher_code_model,
                $this->voucher_job_model
            ))
            ->setMethods(array('create'))
            ->getMock();

        $this->repository->expects($this->any())->method('create')->will($this->throwException(new \Exception));
        $this->app->instance('Voucher\Repositories\VouchersRepository', $this->repository);

        $this->call("POST", "/vouchers", $data, [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function testVoucherPut()
    {
        $data = [
            "type" => "time",
            "status" => "claimed",
            "category" => "new",
            "title" => "INTERNAL",
            "location" => "Nigeria",
            "description" => "A voucher",
            "duration" => 4,
            "period" => "month",
            "limit" => 1200,
            "valid_from" => "2015-10-13 02:02:02",
            "valid_to" => "2015-10-15 02:02:02",
            "code" => "fd1127",
            "voucher_job_id" => 1
        ];

        $this->call('PUT', '/vouchers/3', $data, [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_OK);
    }

    public function testVoucherPutWithInvalidArgsErrorException()
    {
        $data = [
            "type" => "time",
            "status" => "claimed",
            "category" => "new",
            "title" => "INTERNAL",
            "limit" => 1200,
            "valid_from" => "2015-10-13 02:02:02",
            "valid_to" => "2015-10-15 02:02:02",
            "code" => "fd1127",
            "voucher_job_id" => 1
        ];

        $this->call('PUT', '/vouchers/q', $data, [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_BAD_REQUEST);
    }

    public function testVoucherPutWithNotFoundErrorException()
    {
        $data = [
            "type" => "time",
            "status" => "claimed",
            "category" => "new",
            "title" => "INTERNAL",
            "location" => "Nigeria",
            "description" => "A voucher",
            "duration" => 4,
            "period" => "month",
            "limit" => 1200,
            "valid_from" => "2015-10-13 02:02:02",
            "valid_to" => "2015-10-15 02:02:02",
            "code" => "fd1127",
            "voucher_job_id" => 1
        ];
        $this->call('PUT', '/vouchers/2000000000000', $data, [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }

    public function testVoucherPutWithInternalErrorException()
    {
        $data = [
            "type" => "time",
            "status" => "claimed",
            "category" => "new",
            "title" => "INTERNAL",
            "location" => "Nigeria",
            "description" => "A voucher",
            "duration" => 4,
            "period" => "month",
            "limit" => 1200,
            "valid_from" => "2015-10-13 02:02:02",
            "valid_to" => "2015-10-15 02:02:02",
            "code" => "fd1127",
            "voucher_job_id" => 1
        ];
        $this->repository = $this->getMockBuilder('Voucher\Repositories\VouchersRepository')
            ->setConstructorArgs(array(
                $this->voucher_model,
                $this->voucher_job_params_model,
                $this->voucher_code_model,
                $this->voucher_job_model
            ))
            ->setMethods(array('update'))
            ->getMock();

        $this->repository->expects($this->any())->method('update')->will($this->throwException(new \Exception));
        $this->app->instance('Voucher\Repositories\VouchersRepository', $this->repository);

        $this->call("PUT", "/vouchers/1", $data, [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function testRedeem()
    {
        $data = [
            "platform" => "mobile",
            "code" => "fd1127",
            "user_id" => 1
        ];
        $voucherRepositoryMock = \Mockery::mock(\Voucher\Repositories\VouchersRepository::class);
        $voucherLogsRepositoryMock = \Mockery::mock(\Voucher\Repositories\VoucherLogsRepository::class);

        $this->repository = $this->getMockBuilder('Voucher\Voucher\Voucher')
            ->setConstructorArgs(array(
                $voucherRepositoryMock,
                $voucherLogsRepositoryMock
            ))
            ->setMethods(array('redeem'))
            ->getMock();

        $this->repository->expects($this->any())->method('redeem')->willReturn(true);
        $this->app->instance('Voucher\Voucher\Voucher', $this->repository);

        $this->call("POST", "/vouchers/redeem", $data, [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_OK);
    }

    public function testRedeemWithInvalidArgsErrorException()
    {
        $data = [
            "platform" => "mobile",
            "code" => "fd1127",
            "user_id" => "asdf"
        ];

        $this->call("POST", "/vouchers/redeem", $data, [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_BAD_REQUEST);
    }

    public function testRedeemWithInternalErrorException()
    {
        $data = [
            "platform" => "mobile",
            "code" => "fd1127",
            "user_id" => "asdf"
        ];

        $errors = VoucherValidator::getVoucherRules();
        $message_bag = VoucherValidator::getMessages();

        Validator::shouldReceive('make')
            ->once()
            ->andReturn(Mockery::mock([
                'fails' => 'true',
                'messages' => $message_bag,
                'errors' => $errors
            ]));

        $this->call("POST", "/vouchers/redeem", $data, [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function testBulkCreateVoucher()
    {
        $data = [
            "type" => "time",
            "status" => "claimed",
            "category" => "new",
            "title" => "INTERNAL",
            "location" => "Nigeria",
            "description" => "A voucher",
            "duration" => 4,
            "period" => "day",
            "limit" => 0,
            "brand" => "type",
            "total" => 10,
            "valid_from" => "2015-10-13 02:02:02",
            "valid_to" => "2015-10-15 02:02:02"
        ];

        $this->call("POST", "/vouchers/bulk", $data, [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_CREATED);
    }

    public function testBulkCreateVoucherWithInvalidArgsErrorException()
    {
        $data = [
            "type" => "time",
            "status" => "claimed",
            "category" => "new",
            "title" => "INTERNAL",
            "location" => "Nigeria",
            "description" => "A voucher",
            "duration" => "welcome",
            "period" => "day",
            "limit" => 'weell',
            "brand" => "type",
            "total" => 10,
            "valid_from" => "2015-10-13 02:02:02",
            "valid_to" => "2015-10-15 02:02:02"
        ];
        $this->call("POST", "/vouchers/bulk", $data, [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_BAD_REQUEST);
    }

    public function testBulkCreateVoucherWithInternalErrorExceptionOnInsert()
    {
        $data = [
            "type" => "time",
            "status" => "claimed",
            "category" => "new",
            "title" => "INTERNAL",
            "location" => "Nigeria",
            "description" => "A voucher",
            "duration" => 4,
            "period" => "day",
            "limit" => 0,
            "brand" => "type",
            "total" => 10,
            "valid_from" => "2015-10-13 02:02:02",
            "valid_to" => "2015-10-15 02:02:02"
        ];

        $this->repository = $this->getMock(
            'Voucher\Repositories\VouchersRepository',
            ['insertVoucherJob'],
            [
                $this->voucher_model,
                $this->voucher_job_params_model,
                $this->voucher_code_model,
                $this->voucher_job_model
            ]
        );
        $this->repository->expects($this->any())->method('insertVoucherJob')->will($this->throwException(new \Exception));
        $this->app->instance('Voucher\Repositories\VouchersRepository', $this->repository);

        $this->call("POST", "/vouchers/bulk", $data, [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function testBulkCreateVoucherWithInternalErrorException()
    {
        $errors = VoucherValidator::getVoucherRules();
        $message_bag = VoucherValidator::getMessages();

        Validator::shouldReceive('make')
            ->once()
            ->andReturn(Mockery::mock([
                'fails' => 'true',
                'messages' => $message_bag,
                'errors' => $errors
            ]));

        $this->call("POST", "/vouchers/bulk", [], [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
