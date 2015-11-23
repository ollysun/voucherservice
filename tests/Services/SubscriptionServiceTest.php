<?php
use Voucher\Services\SubscriptionService;

class SubscriptionServiceTest   extends TestCase
{
    protected $subscription_service;

    public function setUp()
    {
        parent::setUp();
        $this->subscription_service = new SubscriptionService();
    }

    public function testSubscriptionApi()
    {
        $result = $this->subscription_service->subscriptionApi('/subscriptions/500', 'get');
        $this->assertArrayHasKey('data', $result);
    }

    public function testSubscriptionApiWithInternalErrorException()
    {
        $result = $this->subscription_service->subscriptionApi('/subscriptions/1111111111111111111111111', 'get');
        $this->assertArrayHasKey('error', $result);
    }
}
