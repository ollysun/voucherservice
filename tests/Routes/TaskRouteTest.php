<?php
use Voucher\Models\VoucherJob;
use Voucher\Models\VoucherJobParamMetadata;
use Voucher\Models\Voucher;
use Voucher\Models\VoucherCode;
use Illuminate\Http\Response;

class TaskRouteTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();

    }

    public function testIssueVouchers()
    {
        $voucher_job_repo_mocked  = $this->getMock('Voucher\Repositories\VoucherJobsRepository', ['getJobs', 'updateJobStatus', 'addVouchers'],[new VoucherJob()]);
        $voucher_job_repo_mocked->expects($this->any())->method('getJobs')->willReturn([
            'data' => [
                [
                    'id' => 1,
                    'status' => 'new',
                    'comments' => 'start here'
                ]
            ]
        ]);
        $voucher_job_repo_mocked->expects($this->any())->method('updateJobStatus')->willReturn([]);
        $voucher_job_repo_mocked->expects($this->any())->method('addVouchers')->willReturn([]);
        $this->app->instance('Voucher\Repositories\VoucherJobsRepository', $voucher_job_repo_mocked);

        $jobs_params_repo_mocked = $this->getMock('Voucher\Repositories\VoucherJobParamMetadatasRepository', ['getJobParams'], [new VoucherJobParamMetadata()]);
        $jobs_params_repo_mocked->expects($this->any())->method('getJobParams')->willReturn([
            'data' => [
                [
                    'id' => 1,
                    'voucher_job_id' => 1,
                    'key' => 'status',
                    'value' => 'active',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
                [
                    'id' => 2,
                    'voucher_job_id' => 1,
                    'key' => 'category',
                    'value' => 'new',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
                [
                    'id' => 3,
                    'voucher_job_id' => 1,
                    'key' => 'title',
                    'value' => 'INTERNAL',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
                [
                    'id' => 4,
                    'voucher_job_id' => 1,
                    'key' => 'total',
                    'value' => '3',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
                [
                    'id' => 6,
                    'voucher_job_id' => 1,
                    'key' => 'duration',
                    'value' => '1',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
                [
                    'id' => 7,
                    'voucher_job_id' => 1,
                    'key' => 'period',
                    'value' => 'month',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
            ]
        ]);
        $this->app->instance('Voucher\Repositories\VoucherJobParamMetadatasRepository', $jobs_params_repo_mocked);

        $voucher_repo_mocked = $this->getMock('Voucher\Repositories\VouchersRepository', ['getVouchersByJobIdAndLimit'], [
            new Voucher(),
            new VoucherJobParamMetadata(),
            new VoucherCode(),
            new VoucherJob(),

        ]);

        $ob[] = new stdClass();
        $ob[0]->code = 'GHFHJGJGBBJ';
        $ob[] = new stdClass();
        $ob[1]->code = 'GCCDDFgGGG';

        $voucher_repo_mocked->expects($this->any())->method('getVouchersByJobIdAndLimit')->willReturn($ob);
        $this->app->instance('Voucher\Repositories\VouchersRepository', $voucher_repo_mocked);

        $this->call("POST", "/vouchers/issue-vouchers", [], [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_OK);

    }

    public function testIssueVouchersWithNoJobs()
    {
        $voucher_job_repo_mocked  = $this->getMock('Voucher\Repositories\VoucherJobsRepository', ['getJobs', 'updateJobStatus', 'addVouchers'],[new VoucherJob()]);
        $voucher_job_repo_mocked->expects($this->any())->method('getJobs')->willReturn([
            'data' => [] //no jobs
        ]);
        $voucher_job_repo_mocked->expects($this->any())->method('updateJobStatus')->willReturn([]);
        $voucher_job_repo_mocked->expects($this->any())->method('addVouchers')->willReturn([]);
        $this->app->instance('Voucher\Repositories\VoucherJobsRepository', $voucher_job_repo_mocked);

        $jobs_params_repo_mocked = $this->getMock('Voucher\Repositories\VoucherJobParamMetadatasRepository', ['getJobParams'], [new VoucherJobParamMetadata()]);
        $jobs_params_repo_mocked->expects($this->any())->method('getJobParams')->willReturn([]);
        $this->app->instance('Voucher\Repositories\VoucherJobParamMetadatasRepository', $jobs_params_repo_mocked);

        $voucher_repo_mocked = $this->getMock('Voucher\Repositories\VouchersRepository', ['getVouchersByJobIdAndLimit'], [
            new Voucher(),
            new VoucherJobParamMetadata(),
            new VoucherCode(),
            new VoucherJob(),

        ]);

        $ob[] = new stdClass();
        $ob[0]->code = 'GHFHJGJGBBJ';

        $voucher_repo_mocked->expects($this->any())->method('getVouchersByJobIdAndLimit')->willReturn($ob);
        $this->app->instance('Voucher\Repositories\VouchersRepository', $voucher_repo_mocked);

        $this->call("POST", "/vouchers/issue-vouchers", [], [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_OK);
    }

    public function testIssueVouchersWithNoJobsException()
    {
        $voucher_job_repo_mocked  = $this->getMock('Voucher\Repositories\VoucherJobsRepository', ['getJobs', 'updateJobStatus', 'addVouchers'],[new VoucherJob()]);
        $voucher_job_repo_mocked->expects($this->any())->method('getJobs')->willThrowException(new \Exception);
        $voucher_job_repo_mocked->expects($this->any())->method('updateJobStatus')->willReturn([]);
        $voucher_job_repo_mocked->expects($this->any())->method('addVouchers')->willReturn([]);
        $this->app->instance('Voucher\Repositories\VoucherJobsRepository', $voucher_job_repo_mocked);

        $jobs_params_repo_mocked = $this->getMock('Voucher\Repositories\VoucherJobParamMetadatasRepository', ['getJobParams'], [new VoucherJobParamMetadata()]);
        $jobs_params_repo_mocked->expects($this->any())->method('getJobParams')->willReturn([]);
        $this->app->instance('Voucher\Repositories\VoucherJobParamMetadatasRepository', $jobs_params_repo_mocked);

        $voucher_repo_mocked = $this->getMock('Voucher\Repositories\VouchersRepository', ['getVouchersByJobIdAndLimit'], [
            new Voucher(),
            new VoucherJobParamMetadata(),
            new VoucherCode(),
            new VoucherJob(),

        ]);

        $ob[] = new stdClass();
        $ob[0]->code = 'GHFHJGJGBBJ';

        $voucher_repo_mocked->expects($this->any())->method('getVouchersByJobIdAndLimit')->willReturn($ob);
        $this->app->instance('Voucher\Repositories\VouchersRepository', $voucher_repo_mocked);

        $this->call("POST", "/vouchers/issue-vouchers", [], [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function testIssueVouchersWithGenerateCsvError()
    {
        $voucher_job_repo_mocked  = $this->getMock('Voucher\Repositories\VoucherJobsRepository', ['getJobs', 'updateJobStatus', 'addVouchers'],[new VoucherJob()]);
        $voucher_job_repo_mocked->expects($this->any())->method('getJobs')->willReturn([
            'data' => [
                [
                    'id' => 1,
                    'status' => 'new',
                    'comments' => 'start here'
                ]
            ]
        ]);
        $voucher_job_repo_mocked->expects($this->any())->method('updateJobStatus')->willReturn([]);
        $voucher_job_repo_mocked->expects($this->any())->method('addVouchers')->willReturn([]);
        $this->app->instance('Voucher\Repositories\VoucherJobsRepository', $voucher_job_repo_mocked);

        $jobs_params_repo_mocked = $this->getMock('Voucher\Repositories\VoucherJobParamMetadatasRepository', ['getJobParams'], [new VoucherJobParamMetadata()]);
        $jobs_params_repo_mocked->expects($this->any())->method('getJobParams')->willReturn([
            'data' => [
                [
                    'id' => 1,
                    'voucher_job_id' => 1,
                    'key' => 'status',
                    'value' => 'active',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
                [
                    'id' => 2,
                    'voucher_job_id' => 1,
                    'key' => 'category',
                    'value' => 'new',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
                [
                    'id' => 3,
                    'voucher_job_id' => 1,
                    'key' => 'title',
                    'value' => 'INTERNAL',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
                [
                    'id' => 4,
                    'voucher_job_id' => 1,
                    'key' => 'total',
                    'value' => '3',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
                [
                    'id' => 6,
                    'voucher_job_id' => 1,
                    'key' => 'duration',
                    'value' => '1',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
                [
                    'id' => 7,
                    'voucher_job_id' => 1,
                    'key' => 'period',
                    'value' => 'month',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
            ]
        ]);
        $this->app->instance('Voucher\Repositories\VoucherJobParamMetadatasRepository', $jobs_params_repo_mocked);

        $voucher_repo_mocked = $this->getMock('Voucher\Repositories\VouchersRepository', ['getVouchersByJobIdAndLimit'], [
            new Voucher(),
            new VoucherJobParamMetadata(),
            new VoucherCode(),
            new VoucherJob(),

        ]);

        $ob[] = new stdClass();
        //$ob[0]->code = 'GHFHJGJGBBJ'; will cause fail

        $voucher_repo_mocked->expects($this->any())->method('getVouchersByJobIdAndLimit')->willReturn($ob);
        $this->app->instance('Voucher\Repositories\VouchersRepository', $voucher_repo_mocked);

        $this->call("POST", "/vouchers/issue-vouchers", [], [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function testGenerateVoucherCodes()
    {
        $data = [
            'total'=> 2
        ];

        $this->call("POST", "/vouchers/generate-codes", $data, [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_CREATED);
    }

    public function testGenerateVoucherCodesWithInvalidArgsException()
    {
        $data = [
            //'total'=> 2
        ];

        $this->call("POST", "/vouchers/generate-codes", $data, [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_BAD_REQUEST);
    }

    public function testGenerateVoucherCodesWithInternalErrorException()
    {
        $code_repo_mocked = $this->getMock('Voucher\Repositories\VoucherCodesRepository', ['isNotExistingVoucherCode'], [new VoucherCode()]);
        $code_repo_mocked->expects($this->any())->method('isNotExistingVoucherCode')->will($this->throwException(new \Exception()));
        $this->app->instance('Voucher\Repositories\VoucherCodesRepository', $code_repo_mocked);

        $data = [
            'total'=> 2
        ];

        $this->call("POST", "/vouchers/generate-codes", $data, [], [], $this->authHeader);
        $this->assertResponseStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
