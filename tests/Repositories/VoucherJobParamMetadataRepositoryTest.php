<?php

use Voucher\Models\VoucherJobParamMetadata;
use Voucher\Repositories\VoucherJobParamMetadatasRepository;

class VoucherJobParamMetadataRepositoryTest extends TestCase
{
    protected $model;
    protected $repository;

    public function setUp()
    {
        parent::setUp();
        $this->model = new VoucherJobParamMetadata();
        $this->repository = new VoucherJobParamMetadatasRepository($this->model);
    }

    public function testGetJobParams()
    {
        $job_model = new \Voucher\Models\VoucherJob();
        $job_model->insert(['id' => 9990, 'status' => 'active', 'comments' => 'a comment']);

        $data = [
            'voucher_job_id' => 9990,
            'key' => 'status',
            'value' => 'active'
        ];
        $this->model->insert($data);
        $data['id'] = 9990;

        $result = $this->repository->getJobParams($data);
        $this->assertEquals($data['id'], $result['data'][0]['voucher_job_id']);
    }

    public function testGetJobParamsErrorException()
    {
        $this->model->truncate;

        $data = [
            'voucher_job_id' => 9990,
            'key' => 'status',
            'value' => 'active'
        ];

        $this->setExpectedException('\Exception');
        $this->repository->getJobParams($data);
    }
}