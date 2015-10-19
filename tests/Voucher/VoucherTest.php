<?php

use Voucher\Repositories\VoucherLogsRepository;
use Voucher\Repositories\VouchersRepository;
use Voucher\Models\Voucher;
use Voucher\Models\VoucherLog;
use Voucher\Models\VoucherJobParamMetadata;
use Voucher\Models\VoucherCode;
use Voucher\Voucher\Voucher as VoucherService;

class VoucherTest extends TestCase
{
    protected $voucher_service;

    protected $voucher_repo;

    protected $voucher_log_repo;

    protected $voucherBusinessNotification;

    public function setUp()
    {
        $this->voucher_repo = new VouchersRepository(new Voucher(), new VoucherLog(), new VoucherJobParamMetadata(), new VoucherCode());

        $this->voucher_log_repo = new VoucherLogsRepository(new VoucherLog());

        parent::setUp();
    }

    public function testRedeem()
    {
        $this->voucher_repo->create([
            'valid_from' => '2015-1-1 20:11:1',
            'valid_to' => date('Y-m-d H:i:s', strtotime('+ 1 day')),
            'limit' => 1,
            'type' => 'time',
            'code' => 'XAD34E1'
        ]);

        $inputs = [
          'code' => 'XAD34E1',
            'user_id' => 122333,
            'platform' => 1,
        ];

        $voucher =  new VoucherService($this->voucher_repo, $this->voucher_log_repo);
        $voucher->setSubscriptionService(new \Voucher\Services\SubscriptionService());
        $result = $voucher->redeem($inputs);
        $this->assertEquals(true, $result);
    }

    public function testRedeemWithNoneExistingCodeException()
    {
        $this->voucher_repo->create([
            'valid_from' => '2015-1-1 20:11:1',
            'valid_to' => date('Y-m-d H:i:s', strtotime('+ 1 day')),
            'limit' => 1,
            'type' => 'time',
            'code' => 'XCD34E1QQ'
        ]);

        $inputs = [
            'code' => 'XCD34E1',
            'user_id' => 122333,
            'platform' => 1,
        ];

        $this->setExpectedException('\Exception');
        $voucher =  new VoucherService($this->voucher_repo, $this->voucher_log_repo);
        $voucher->setSubscriptionService(new \Voucher\Services\SubscriptionService());
        $voucher->redeem($inputs);
    }

    public function testRedeemWithUsedCodeException()
    {
        $this->voucher_repo->create([
            'valid_from' => '2015-1-1 20:11:1',
            'valid_to' => date('Y-m-d H:i:s', strtotime('+ 1 day')),
            'limit' => 1,
            'type' => 'time',
            'status' => 'inactive',
            'code' => 'XD34E1HH'
        ]);

        $inputs = [
            'code' => 'XD34E1HH',
            'user_id' => 122333,
            'platform' => 1,
        ];

        $this->setExpectedException('\Exception');
        $voucher =  new VoucherService($this->voucher_repo, $this->voucher_log_repo);
        $voucher->setSubscriptionService(new \Voucher\Services\SubscriptionService());
        $voucher->redeem($inputs);
    }

    public function testRedeemWithExpiredCodeException()
    {
        $this->voucher_repo->create([
            'valid_from' => '2015-1-1 20:11:1',
            'valid_to' => '2014-1-1 20:11:1',
            'limit' => 1,
            'type' => 'time',
            'status' => 'active',
            'code' => 'XD34E1SS'
        ]);

        $inputs = [
            'code' => 'XD34E1SS',
            'user_id' => 122333,
            'platform' => 1,
        ];

        $this->setExpectedException('\Exception');
        $voucher =  new VoucherService($this->voucher_repo, $this->voucher_log_repo);
        $voucher->setSubscriptionService(new \Voucher\Services\SubscriptionService());
        $voucher->redeem($inputs);
    }

    public function testRedeemWithUsageLimitReachedException()
    {
        $inputs = [
            'code' => 'XD34E1Q11',
            'user_id' => 122333,
            'platform' => 1,
        ];

        $voucher_insert = $this->voucher_repo->create([
            'valid_from' => '2015-1-1 20:11:1',
            'valid_to' => date('Y-m-d H:i:s', strtotime('+ 1 day')),
            'limit' => 1,
            'type' => 'time',
            'status' => 'active',
            'code' => 'XD34E1Q11'
        ]);

        $this->voucher_log_repo->addVoucherLog([
            'voucher_id' => $voucher_insert['data']['id'],
            'user_id' => $inputs['user_id'],
            'action' => 'success',
            'platform' => 'mobile',
            'comments' => 'just test',
        ]);

        $this->setExpectedException('\Exception');
        $voucher =  new VoucherService($this->voucher_repo, $this->voucher_log_repo);
        $voucher->setSubscriptionService(new \Voucher\Services\SubscriptionService());
        $voucher->redeem($inputs);
    }

}
