<?php
use Voucher\Services\PlanService;

class PlanServiceTest extends TestCase
{
    protected $plan_service;

    public function setUp()
    {
        parent::setUp();
        $this->plan_service = new PlanService();
    }

    public function testPlansApi()
    {
        $result = $this->plan_service->plansApi('/plans/1', 'get');
        $this->assertArrayHasKey('data', $result);
    }

    public function testPlanApiWithInternalErrorException()
    {
        $result = $this->plan_service->plansApi('/plans/10000000000000000', 'get');
        $this->assertArrayHasKey('error', $result);
    }
}
