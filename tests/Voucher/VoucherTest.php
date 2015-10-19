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
            'valid_to' => '2015-12-1 20:11:1',
            'limit' => 1,
            'type' => 'time',
            'code' => 'XD34E1'
        ]);

        $inputs = [
          'code' => 'XD34E1',
            'user_id' => 122333,
            'platform' => 1,
        ];

        $voucher =  new VoucherService($this->voucher_repo, $this->voucher_log_repo);
        $voucher->setSubscriptionService(new \Voucher\Services\SubscriptionService());
        $result = $voucher->redeem($inputs);
        $this->assertEquals(true, $result);
    }


    public function testRedeemWithException()
    {
        $this->voucher_repo->create([
            'valid_from' => '2015-1-1 20:11:1',
            'valid_to' => '2015-12-1 20:11:1',
            'limit' => 1,
            'type' => 'time',
            'code' => 'XD34E1QQ'
        ]);

        $inputs = [
            'code' => 'XD34E1',
            'user_id' => 122333,
            'platform' => 1,
        ];

        $this->setExpectedException('\Exception');
        $voucher =  new VoucherService($this->voucher_repo, $this->voucher_log_repo);
        $voucher->setSubscriptionService(new \Voucher\Services\SubscriptionService());
        $voucher->redeem($inputs);
    }
//
//    public function testIsVoucherExistsAndValid()
//    {
//
//    }
//
//    public function testIsVoucherExistsAndValidWithException()
//    {
//
//    }
//
//    public function testIsVoucherValidForUser()
//    {
//
//    }
//
//    public function testIsVoucherValidForUserWithException()
//    {
//
//    }
//
//    public function testSendSubscribeRequest()
//    {
//
//    }
//
//    public function testSendSubscribeRequestWithFailedRequest()
//    {
//
//    }
}
