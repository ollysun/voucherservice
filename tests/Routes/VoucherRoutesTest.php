<?php
use Illuminate\Http\Response;
use Voucher\Models\Voucher;
use Voucher\Models\VoucherLog;
use Voucher\Models\VoucherCode;
use Voucher\Models\VoucherJobParamMetadata;

class VoucherRoutesTest extends TestCase
{
    protected $voucherWithExceptionMock;
    protected $repository;
    protected $voucherLogWithExceptionMock;
    protected $voucherJobParamMetadataWithExceptionMock;
    protected $voucherCodeWithExceptionMock;

    public function setUp()
    {
        parent::setUp();
        $this->voucherWithExceptionMock = \Mockery::mock(Voucher::class);
        $this->voucherLogWithExceptionMock = \Mockery::mock(VoucherLog::class);
        $this->voucherCodeWithExceptionMock = \Mockery::mock(VoucherCode::class);
        $this->voucherJobParamMetadataWithExceptionMock = \Mockery::mock(VoucherJobParamMetadata::class);
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
                $this->voucherWithExceptionMock,
                $this->voucherLogWithExceptionMock,
                $this->voucherJobParamMetadataWithExceptionMock,
                $this->voucherCodeWithExceptionMock
            ))
            ->setMethods(array('getVouchers'))
            ->getMock();

        $this->repository->expects($this->any())
            ->method('getVouchers')
            ->willReturn(null);

        $this->app->instance('Voucher\Repositories\VouchersRepository', $this->repository);
        $this->get('/vouchers', $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }

    public function testVouchersInternalErrorGet()
    {
        $this->repository = $this->getMockBuilder('Voucher\Repositories\VouchersRepository')
            ->setConstructorArgs(array(
                $this->voucherWithExceptionMock,
                $this->voucherLogWithExceptionMock,
                $this->voucherJobParamMetadataWithExceptionMock,
                $this->voucherCodeWithExceptionMock
            ))
            ->setMethods(array('getVouchers'))
            ->getMock();

        $this->repository->expects($this->any())
            ->method('getVouchers')
            ->will($this->throwException(new \Exception));

        $this->app->instance('Voucher\Repositories\VouchersRepository', $this->repository);
        $this->get('/vouchers', $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function testVouchersPost()
    {
        //Create a voucher code before the test
        $this->call("POST", "/vouchers/generateCodes", ['total' => 2], [], [], $this->authHeader);

        $data = [
            "type" => "time",
            "status" => "claimed",
            "category" => "new",
            "title" => "INTERNAL",
            "location" => "Nigeria",
            "description" => "A voucher",
            "duration" => 4,
            "period" => "month",
            "is_limited" => true,
            "limit" => 1200,
            "valid_from" => "2015-10-13 02:02:02",
            "valid_to" => "2015-10-15 02:02:02",
            "voucher_job_id" => 1
        ];

        $this->call("POST", "/vouchers", $data, [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_CREATED);
    }

    public function testVouchersPostCodeNotFound()
    {
        $voucher_codes_model = new VoucherCode();
        $voucher_codes_model->truncate();

        $data = [
            "type" => "time",
            "status" => "claimed",
            "category" => "new",
            "title" => "INTERNAL",
            "location" => "Nigeria",
            "description" => "A voucher",
            "duration" => 4,
            "period" => "month",
            "is_limited" => true,
            "limit" => 1200,
            "valid_from" => "2015-10-13 02:02:02",
            "valid_to" => "2015-10-15 02:02:02",
            "voucher_job_id" => 1
        ];

        $this->call("POST", "/vouchers", $data, [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }

    public function testVouchersPostInvalidArgument()
    {
        $data = [
            "type" => "time",
            "status" => "claimed",
            "category" => "new",
            "title" => 1,
            "location" => "Nigeria",
            "description" => "A voucher",
            "duration" => "wwww",
            "period" => "month",
            "is_limited" => true,
            "limit" => 1200,
            "valid_from" => "2015-10-13 02:02:02",
            "valid_to" => "2015-10-15 02:02:02",
            "code" => "fd1127",
            "voucher_job_id" => 1
        ];

        $this->call("POST", "/vouchers", $data, [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_BAD_REQUEST);
    }

    public function testVouchersPostTransactionFail()
    {
        //Create a voucher code before the test
        $this->call("POST", "/vouchers/generateCodes", ['total' => 1], [], [], $this->authHeader);

        $data = [
            "type" => "time",
            "status" => "claimed",
            "category" => "new",
            "title" => "INTERNAL",
            "location" => "Nigeria",
            "description" => "A voucher",
            "duration" => 4,
            "period" => "month",
            "is_limited" => true,
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
                $this->voucherWithExceptionMock,
                $this->voucherLogWithExceptionMock,
                $this->voucherJobParamMetadataWithExceptionMock,
                $this->voucherCodeWithExceptionMock
            ]
        );
        $this->repository->expects($this->any())->method('getVoucherCodeByStatus')->willReturn($code);
        $this->repository->expects($this->any())->method('create')->will($this->throwException(new \Exception));

        $this->app->instance('Voucher\Repositories\VouchersRepository', $this->repository);
        $this->call("POST", "/vouchers", $data, [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function testVouchersInternalErrorExceptionPost()
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
            "is_limited" => true,
            "limit" => 1200,
            "valid_from" => "2015-10-13 02:02:02",
            "valid_to" => "2015-10-15 02:02:02",
            "code" => "fd1127",
            "voucher_job_id" => 1
        ];
        $this->repository = $this->getMockBuilder('Voucher\Repositories\VouchersRepository')
            ->setConstructorArgs(array(
                $this->voucherWithExceptionMock,
                $this->voucherLogWithExceptionMock,
                $this->voucherJobParamMetadataWithExceptionMock,
                $this->voucherCodeWithExceptionMock
            ))
            ->setMethods(array('create'))
            ->getMock();
        $this->repository->expects($this->any())
            ->method('create')
            ->will($this->throwException(new \Exception));

        $this->app->instance('Voucher\Repositories\VouchersRepository', $this->repository);
        $this->call("POST", "/vouchers", $data, [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function testVoucherPutVoucher()
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
            "is_limited" => true,
            "limit" => 1200,
            "valid_from" => "2015-10-13 02:02:02",
            "valid_to" => "2015-10-15 02:02:02",
            "code" => "fd1127",
            "voucher_job_id" => 1
        ];

        $this->call('PUT', '/vouchers/3', $data, [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_OK);
    }

    public function testVoucherPutVoucherNotFound()
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
            "is_limited" => true,
            "limit" => 1200,
            "valid_from" => "2015-10-13 02:02:02",
            "valid_to" => "2015-10-15 02:02:02",
            "code" => "fd1127",
            "voucher_job_id" => 1
        ];

        $this->call('PUT', '/vouchers/333333333333', $data, [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }

    public function testVoucherPutThrowInvalidArgsException()
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

    public function testVoucherPutThrowNotFoundException()
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
            "is_limited" => true,
            "limit" => 1200,
            "valid_from" => "2015-10-13 02:02:02",
            "valid_to" => "2015-10-15 02:02:02",
            "code" => "fd1127",
            "voucher_job_id" => 1
        ];
        $this->call('PUT', '/plans/2000000000000', $data, [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }

    public function testVoucherPutInternalErrorException()
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
            "is_limited" => true,
            "limit" => 1200,
            "valid_from" => "2015-10-13 02:02:02",
            "valid_to" => "2015-10-15 02:02:02",
            "code" => "fd1127",
            "voucher_job_id" => 1
        ];
        $this->repository = $this->getMockBuilder('Voucher\Repositories\VouchersRepository')
            ->setConstructorArgs(array(
                $this->voucherWithExceptionMock,
                $this->voucherLogWithExceptionMock,
                $this->voucherJobParamMetadataWithExceptionMock,
                $this->voucherCodeWithExceptionMock
            ))
            ->setMethods(array('update'))
            ->getMock();

        $this->repository->expects($this->any())
            ->method('update')
            ->will($this->throwException(new \Exception));

        $this->app->instance('Voucher\Repositories\VouchersRepository', $this->repository);
        $this->call("PUT", "/vouchers/1", $data, [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function testRedeemVouchersPost()
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

        $this->repository->expects($this->any())
            ->method('redeem')
            ->willReturn(true);

        $this->app->instance('Voucher\Voucher\Voucher', $this->repository);
        $this->call("POST", "/vouchers/redeem", $data, [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_OK);
    }

    public function testRedeemVouchersPostFail()
    {
        $data = [
            "platform" => "mobile",
            "code" => "fd1127",
            "user_id" => "asdf"
        ];

        $this->call("POST", "/vouchers/redeem", $data, [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_BAD_REQUEST);
    }

    public function testRedeemVouchersPostInternalErrorException()
    {
        $data = [
            "platform" => "mobile",
            "code" => "fd1127",
            "user_id" => "asdf"
        ];

        $errors = \Voucher\Validators\VoucherValidator::getVoucherRules();
        $message_bag = \Voucher\Validators\VoucherValidator::getMessages();

        \Illuminate\Support\Facades\Validator::shouldReceive('make')
            ->once()
            ->andReturn(Mockery::mock([
                'fails' => 'true',
                'messages' => $message_bag,
                'errors' => $errors
            ]));

        $this->call("POST", "/vouchers/redeem", $data, [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function testBulkCreateVoucherPost()
    {
        //Create a voucher code before the test
        $this->call("POST", "/vouchers/generateCodes", ['total' => 2], [], [], $this->authHeader);

        $data = [
            "type" => "time",
            "status" => "claimed",
            "category" => "new",
            "title" => "INTERNAL",
            "location" => "Nigeria",
            "description" => "A voucher",
            "duration" => 4,
            "period" => "day",
            "is_limited" => true,
            "limit" => 0,
            "brand" => "type",
            "total" => 10,
            "valid_from" => "2015-10-13 02:02:02",
            "valid_to" => "2015-10-15 02:02:02"
        ];

        $this->call("POST", "/vouchers/bulk", $data, [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_CREATED);
    }

    public function testBulkCreateVoucherPostInvalidArgument()
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
            "is_limited" => true,
            "limit" => 'weell',
            "brand" => "type",
            "total" => 10,
            "valid_from" => "2015-10-13 02:02:02",
            "valid_to" => "2015-10-15 02:02:02"
        ];
        $this->call("POST", "/vouchers/bulk", $data, [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_BAD_REQUEST);
    }

    public function testBulkCreateVoucherPostTransactionFail()
    {
        //Create a voucher code before the test
        $this->call("POST", "/vouchers/generateCodes", ['total' => 2], [], [], $this->authHeader);

        $data = [
            "type" => "time",
            "status" => "claimed",
            "category" => "new",
            "title" => "INTERNAL",
            "location" => "Nigeria",
            "description" => "A voucher",
            "duration" => 4,
            "period" => "day",
            "is_limited" => true,
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
                $this->voucherWithExceptionMock,
                $this->voucherLogWithExceptionMock,
                $this->voucherJobParamMetadataWithExceptionMock,
                $this->voucherCodeWithExceptionMock
            ]
        );
        $this->repository->expects($this->any())
            ->method('insertVoucherJob')
            ->will($this->throwException(new \Exception));

        $this->app->instance('Voucher\Repositories\VouchersRepository', $this->repository);
        $this->call("POST", "/vouchers/bulk", $data, [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function testBulkCreateVoucherPostInternalErrorExceptionPost()
    {
        $errors = \Voucher\Validators\VoucherValidator::getVoucherRules();
        $message_bag = \Voucher\Validators\VoucherValidator::getMessages();

        \Illuminate\Support\Facades\Validator::shouldReceive('make')
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
