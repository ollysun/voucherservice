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

        $this->call("POST", "/vouchers", $data, [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_CREATED);
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

        $this->call('PUT','/vouchers/3', $data, [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_OK);
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

        $this->call('PUT','/vouchers/q', $data, [], [], $this->authHeader);
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
        $this->call('PUT','/plans/2000000000000', $data, [], [], $this->authHeader);
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

//    public function testRedeemVouchersPost()
//    {
//        $data = [
//            "platform" => "mobile",
//            "code" => "fd1127",
//            "user_id" => 1
//        ];
//        $this->call("POST", "/vouchers/redeem", $data, [], [], $this->authHeader);
//        $this->assertResponseStatus(Response::HTTP_CREATED);
//    }

    public function testBulkCreateVoucherPost()
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

    public function testBulkCreateVoucherPostInternalErrorExceptionPost()
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
        $this->repository = $this->getMockBuilder('Voucher\Repositories\VouchersRepository')
            ->setConstructorArgs(array(
                $this->voucherWithExceptionMock,
                $this->voucherLogWithExceptionMock,
                $this->voucherJobParamMetadataWithExceptionMock,
                $this->voucherCodeWithExceptionMock
            ))
            ->setMethods(array('insertVoucherJobParamMetadata'))
            ->getMock();
        $this->repository->expects($this->any())
            ->method('insertVoucherJobParamMetadata')
            ->will($this->throwException(new \Exception));

        $this->app->instance('Voucher\Repositories\VouchersRepository', $this->repository);
        $this->call("POST", "/vouchers/bulk", $data, [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
    }


}