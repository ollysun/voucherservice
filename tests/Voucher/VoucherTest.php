<?php

use Voucher\Repositories\VoucherLogsRepository;
use Voucher\Repositories\VouchersRepository;
use Voucher\Models\Voucher;
use Voucher\Models\VoucherLog;
use Voucher\Models\VoucherJobParamMetadata;
use Voucher\Models\VoucherCode;
use Voucher\Models\VoucherJob;
use Voucher\Voucher\Voucher as VoucherService;

class VoucherTest extends TestCase
{
    protected $voucher_service;

    protected $voucher_repo;

    protected $voucher_log_repo;

    public function setUp()
    {
        $this->voucher_repo = new VouchersRepository(
            new Voucher(),
            new VoucherLog(),
            new VoucherJobParamMetadata(),
            new VoucherCode(),
            new VoucherJob()
        );

        $this->voucher_log_repo = new VoucherLogsRepository(new VoucherLog());

        parent::setUp();
    }

    /**
     * TesCase for a voucher code that was claimed successfully.
     *
     * @throws Exception
     */
    public function testRedeem()
    {
        $voucher_job_model = new VoucherJob();
        $voucher_job_model->insert(['id' => 9990, 'status' => 'new', 'comments' => 'a comment']);

        $this->voucher_repo->create([
            'valid_from' => '2015-1-1 20:11:1',
            'valid_to' => date('Y-m-d H:i:s', strtotime('+ 1 day')),
            'limit' => 1,
            'type' => 'time',
            'code' => 'XAD34E1',
            'category' => 'new',
            'voucher_job_id' => 9990
        ]);

        $inputs = [
          'code' => 'XAD34E1',
            'user_id' => 122333,
            'platform' => 1,
        ];

        $mocked_sub_service = $this->getMock('Voucher\Services\SubscriptionService', ['subscriptionApi']);
        $mocked_sub_service->expects($this->any())->method('subscriptionApi')->willReturn(false);

        $voucher =  new VoucherService($this->voucher_repo, $this->voucher_log_repo);
        $voucher->setSubscriptionService($mocked_sub_service);
        $result = $voucher->redeem($inputs);
        $this->assertEquals(true, $result);
    }

    /**
     * TestCase for a voucher code that has a claiming status.
     * @throws Exception
     */
    public function testRedeemWithClaimingStatus()
    {
        $voucher_job_model = new VoucherJob();
        $voucher_job_model->insert(['id' => 9990, 'status' => 'new', 'comments' => 'a comment']);

        $this->voucher_repo->create([
            'valid_from' => '2015-1-1 20:11:1',
            'valid_to' => date('Y-m-d H:i:s', strtotime('+ 1 day')),
            'limit' => 11,
            'type' => 'time',
            'code' => 'XAD34E13',
            'category' => 'new',
            'voucher_job_id' => 9990
        ]);

        $inputs = [
            'code' => 'XAD34E13',
            'user_id' => 122333,
            'platform' => 1,
        ];

        $mocked_sub_service = $this->getMock('Voucher\Services\SubscriptionService', ['subscriptionApi']);
        $mocked_sub_service->expects($this->any())->method('subscriptionApi')->willReturn(false);

        $voucher =  new VoucherService($this->voucher_repo, $this->voucher_log_repo);
        $voucher->setSubscriptionService($mocked_sub_service);
        $result = $voucher->redeem($inputs);
        $this->assertEquals(true, $result);
    }

    /**
     * TestCase for a voucher code that does not exist.
     *
     * @throws Exception
     */
    public function testRedeemWithNoneExistingCodeException()
    {
        $inputs = [
            'code' => 'TESTCODE',
            'user_id' => 122333,
            'platform' => 1,
        ];

        $this->setExpectedException('\Exception');
        $mocked_sub_service = $this->getMock('Voucher\Services\SubscriptionService', ['subscriptionApi']);
        $mocked_sub_service->expects($this->any())->method('subscriptionApi')->willReturn(false);

        $voucher =  new VoucherService($this->voucher_repo, $this->voucher_log_repo);
        $voucher->setSubscriptionService($mocked_sub_service);
        $voucher->redeem($inputs);
    }

    /**
     * TestCase for voucher code with has already been used.
     *
     * @throws Exception
     */
    public function testRedeemWithUsedCodeException()
    {
        $voucher_job_model = new VoucherJob();
        $voucher_job_model->insert(['id' => 9990, 'status' => 'new', 'comments' => 'a comment']);

        $this->voucher_repo->create([
            'valid_from' => '2015-1-1 20:11:1',
            'valid_to' => date('Y-m-d H:i:s', strtotime('+ 1 day')),
            'limit' => 1,
            'type' => 'time',
            'code' => 'XAD34E1',
            'category' => 'new',
            'voucher_job_id' => 9990
        ]);

        $inputs = [
            'code' => 'XD34E1HH',
            'user_id' => 122333,
            'platform' => 1,
        ];

        $this->setExpectedException('\Exception');
        $mocked_sub_service = $this->getMock('Voucher\Services\SubscriptionService', ['subscriptionApi']);
        $mocked_sub_service->expects($this->any())->method('subscriptionApi')->willReturn(false);

        $voucher =  new VoucherService($this->voucher_repo, $this->voucher_log_repo);
        $voucher->setSubscriptionService($mocked_sub_service);
        $voucher->redeem($inputs);
    }

    /**
     * TestCase for voucher code which has expired.
     *
     * @throws Exception
     */
    public function testRedeemWithExpiredCodeException()
    {
        $voucher_job_model = new VoucherJob();
        $voucher_job_model->insert(['id' => 9990, 'status' => 'new', 'comments' => 'a comment']);

        $this->voucher_repo->create([
            'valid_from' => '2015-1-1 20:11:1',
            'valid_to' => date('Y-m-d H:i:s', strtotime('+ 1 day')),
            'limit' => 1,
            'type' => 'time',
            'code' => 'XAD34E1',
            'category' => 'new',
            'voucher_job_id' => 9990
        ]);

        $inputs = [
            'code' => 'XD34E1SS',
            'user_id' => 122333,
            'platform' => 1,
        ];

        $this->setExpectedException('\Exception');
        $mocked_sub_service = $this->getMock('Voucher\Services\SubscriptionService', ['subscriptionApi']);
        $mocked_sub_service->expects($this->any())->method('subscriptionApi')->willReturn(false);

        $voucher =  new VoucherService($this->voucher_repo, $this->voucher_log_repo);
        $voucher->setSubscriptionService($mocked_sub_service);
        $voucher->redeem($inputs);
    }

    /**
     * TestCase for voucher with usage limit reached.
     * @throws Exception
     */
    public function testRedeemWithUsageLimitReachedException()
    {
        $inputs = [
            'code' => 'XD34E1Q11',
            'user_id' => 122333,
            'platform' => 1,
        ];

        $voucher_job_model = new VoucherJob();
        $voucher_job_model->insert(['id' => 9990, 'status' => 'new', 'comments' => 'a comment']);

        $voucher_insert = $this->voucher_repo->create([
            'valid_from' => '2015-1-1 20:11:1',
            'valid_to' => date('Y-m-d H:i:s', strtotime('+ 1 day')),
            'limit' => 1,
            'type' => 'time',
            'status' => 'active',
            'code' => 'XD34E1Q11',
            'voucher_job_id' => 9990
        ]);

        $this->voucher_log_repo->addVoucherLog([
            'voucher_id' => $voucher_insert['data']['id'],
            'user_id' => $inputs['user_id'],
            'action' => 'success',
            'platform' => 'mobile',
            'comments' => 'just test',
        ]);

        $this->setExpectedException('\Exception');
        $mocked_sub_service = $this->getMock('Voucher\Services\SubscriptionService', ['subscriptionApi']);
        $mocked_sub_service->expects($this->any())->method('subscriptionApi')->willReturn(false);

        $voucher =  new VoucherService($this->voucher_repo, $this->voucher_log_repo);
        $voucher->setSubscriptionService($mocked_sub_service);
        $voucher->redeem($inputs);
    }

    /**
     * TestCase for a voucher code with expired category.
     *
     * @throws Exception
     */
    public function testRedeemWithExpiredCategoryCode()
    {
        $voucher_job_model = new VoucherJob();
        $voucher_job_model->insert(['id' => 9990, 'status' => 'new', 'comments' => 'a comment']);

        $this->voucher_repo->create([
            'valid_from' => '2015-1-1 20:11:1',
            'valid_to' => date('Y-m-d H:i:s', strtotime('+ 1 day')),
            'limit' => 1,
            'type' => 'time',
            'code' => 'XAD34E1',
            'category' => 'expired',
            'voucher_job_id' => 9990
        ]);

        $inputs = [
            'code' => 'XAD34E1',
            'user_id' => 122333,
            'platform' => 1,
        ];

        $mocked_sub_service = $this->getMock('Voucher\Services\SubscriptionService', ['subscriptionApi']);
        $mocked_sub_service->expects($this->any())->method('subscriptionApi')->willReturn(
            [
                'data' => [
                    'plan_id' => 1,
                    'is_active' => false,
                    'customer_id' => '12323232323',
                ]
            ]
        );
        $voucher =  new VoucherService($this->voucher_repo, $this->voucher_log_repo);
        $voucher->setSubscriptionService($mocked_sub_service);
        $result = $voucher->redeem($inputs);
        $this->assertEquals(true, $result);
    }

    /**
     * TestCase for a voucher code with expired category but used
     * by a user with active subscription.
     *
     * @throws Exception
     */
    public function testRedeemWithExpiredCategoryCodeButActiveSubscriptionException()
    {
        $voucher_job_model = new VoucherJob();
        $voucher_job_model->insert(['id' => 9990, 'status' => 'new', 'comments' => 'a comment']);

        $this->voucher_repo->create([
            'valid_from' => '2015-1-1 20:11:1',
            'valid_to' => date('Y-m-d H:i:s', strtotime('+ 1 day')),
            'limit' => 1,
            'type' => 'time',
            'code' => 'XAD34E1',
            'category' => 'expired',
            'voucher_job_id' => 9990
        ]);

        $inputs = [
            'code' => 'XAD34E1',
            'user_id' => 122333,
            'platform' => 1,
        ];

        $mocked_sub_service = $this->getMock('Voucher\Services\SubscriptionService', ['subscriptionApi']);
        $mocked_sub_service->expects($this->any())->method('subscriptionApi')->willReturn(
            [
                'data' => [
                    'plan_id' => 1,
                    'is_active' => true,
                    'customer_id' => '12323232323',
                ]
            ]
        );
        $this->setExpectedException('\Exception');
        $voucher =  new VoucherService($this->voucher_repo, $this->voucher_log_repo);
        $voucher->setSubscriptionService($mocked_sub_service);
        $voucher->redeem($inputs);
    }

    /**
     * TestCase for a voucher code with new_expired category.
     *
     * @throws Exception
     */
    public function testRedeemWithNewExpiredCategoryCode()
    {
        $voucher_job_model = new VoucherJob();
        $voucher_job_model->insert(['id' => 9990, 'status' => 'new', 'comments' => 'a comment']);

        $this->voucher_repo->create([
            'valid_from' => '2015-1-1 20:11:1',
            'valid_to' => date('Y-m-d H:i:s', strtotime('+ 1 day')),
            'limit' => 1,
            'type' => 'time',
            'code' => 'XAD34E1',
            'category' => 'new_expired',
            'voucher_job_id' => 9990
        ]);

        $inputs = [
            'code' => 'XAD34E1',
            'user_id' => 122333,
            'platform' => 1,
        ];

        $mocked_sub_service = $this->getMock('Voucher\Services\SubscriptionService', ['subscriptionApi']);
        $mocked_sub_service->expects($this->any())->method('subscriptionApi')->willReturn(
            [
                'data' => [
                    'plan_id' => 1,
                    'is_active' => false,
                    'customer_id' => '12323232323',
                ]
            ]
        );
        $voucher =  new VoucherService($this->voucher_repo, $this->voucher_log_repo);
        $voucher->setSubscriptionService($mocked_sub_service);
        $result = $voucher->redeem($inputs);
        $this->assertEquals(true, $result);
    }

    /**
     * TestCase for a voucher code with new_expired category but was used
     * by a user with active subscription.
     *
     * @throws Exception
     */
    public function testRedeemWithNewExpiredCategoryCodeButActiveSubscriptionException()
    {
        $voucher_job_model = new VoucherJob();
        $voucher_job_model->insert(['id' => 9990, 'status' => 'new', 'comments' => 'a comment']);

        $this->voucher_repo->create([
            'valid_from' => '2015-1-1 20:11:1',
            'valid_to' => date('Y-m-d H:i:s', strtotime('+ 1 day')),
            'limit' => 1,
            'type' => 'time',
            'code' => 'XAD34E1',
            'category' => 'new_expired',
            'voucher_job_id' => 9990
        ]);

        $inputs = [
            'code' => 'XAD34E1',
            'user_id' => 122333,
            'platform' => 1,
        ];

        $mocked_sub_service = $this->getMock('Voucher\Services\SubscriptionService', ['subscriptionApi']);
        $mocked_sub_service->expects($this->any())->method('subscriptionApi')->willReturn(
            [
                'data' => [
                    'plan_id' => 1,
                    'is_active' => true,
                    'customer_id' => '12323232323',
                ]
            ]
        );
        $this->setExpectedException('\Exception');
        $voucher =  new VoucherService($this->voucher_repo, $this->voucher_log_repo);
        $voucher->setSubscriptionService($mocked_sub_service);
        $voucher->redeem($inputs);
    }

    /**
     * TestCase for voucher code with an active category
     *
     * @throws Exception
     */
    public function testRedeemWithActiveCategoryCode()
    {
        $voucher_job_model = new VoucherJob();
        $voucher_job_model->insert(['id' => 9990, 'status' => 'new', 'comments' => 'a comment']);

        $this->voucher_repo->create([
            'valid_from' => '2015-1-1 20:11:1',
            'valid_to' => date('Y-m-d H:i:s', strtotime('+ 1 day')),
            'limit' => 1,
            'type' => 'time',
            'code' => 'XAD34E1',
            'category' => 'active',
            'voucher_job_id' => 9990
        ]);

        $inputs = [
            'code' => 'XAD34E1',
            'user_id' => 122333,
            'platform' => 1,
        ];

        $mocked_sub_service = $this->getMock('Voucher\Services\SubscriptionService', ['subscriptionApi']);
        $mocked_sub_service->expects($this->any())->method('subscriptionApi')->willReturn(
            [
                'data' => [
                    'plan_id' => 1,
                    'is_active' => true,
                    'customer_id' => '12323232323',
                ]
            ]
        );

        $mocked_plan_service = $this->getMock('Voucher\Services\PlanService', ['plansApi']);
        $mocked_plan_service->expects($this->any())->method('plansApi')->willReturn(
            [
                'data' => [
                    'is_recurring' => false,
                ]
            ]
        );
        $voucher =  new VoucherService($this->voucher_repo, $this->voucher_log_repo);
        $voucher->setSubscriptionService($mocked_sub_service);
        $voucher->setPlansService($mocked_plan_service);
        $result = $voucher->redeem($inputs);
        $this->assertEquals(true, $result);
    }

    /**
     * TestCase when a voucher code has an active category for non-recurring plan,
     * but used by a user with a recurring active plan.
     *
     * @throws Exception
     */
    public function testRedeemWithActiveCategoryCodeButHasRecurringPlanException()
    {
        $voucher_job_model = new VoucherJob();
        $voucher_job_model->insert(['id' => 9990, 'status' => 'new', 'comments' => 'a comment']);

        $this->voucher_repo->create([
            'valid_from' => '2015-1-1 20:11:1',
            'valid_to' => date('Y-m-d H:i:s', strtotime('+ 1 day')),
            'limit' => 1,
            'type' => 'time',
            'code' => 'XAD34E1',
            'category' => 'active',
            'voucher_job_id' => 9990
        ]);

        $inputs = [
            'code' => 'XAD34E1',
            'user_id' => 122333,
            'platform' => 1,
        ];

        $mocked_sub_service = $this->getMock('Voucher\Services\SubscriptionService', ['subscriptionApi']);
        $mocked_sub_service->expects($this->any())->method('subscriptionApi')->willReturn(
            [
                'data' => [
                    'plan_id' => 1,
                    'is_active' => true,
                    'customer_id' => '12323232323',
                ]
            ]
        );

        $mocked_plan_service = $this->getMock('Voucher\Services\PlanService', ['plansApi']);
        $mocked_plan_service->expects($this->any())->method('plansApi')->willReturn(
            [
                'data' => [
                    'is_recurring' => true,
                ]
            ]
        );

        $this->setExpectedException('\Exception');
        $voucher =  new VoucherService($this->voucher_repo, $this->voucher_log_repo);
        $voucher->setSubscriptionService($mocked_sub_service);
        $voucher->setPlansService($mocked_plan_service);
        $voucher->redeem($inputs);
    }
}
