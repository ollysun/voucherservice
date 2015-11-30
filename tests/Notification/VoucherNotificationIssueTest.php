<?php
use Voucher\Notification\VoucherNotificationIssue;

/**
 * Created by PhpStorm.
 * User: tech7
 * Date: 10/16/15
 * Time: 10:23 AM
 */
class VoucherNotificationIssueTest extends TestCase
{
    protected $voucherBusinessNotification;

    public function testSet()
    {
        $this->voucherBusinessNotification = new VoucherNotificationIssue(1, 'voucher.generate_failed', [1], ['email', 'sms']);

        $this->voucherBusinessNotification->__set('error', 'no such file exist');
        $this->assertEquals('no such file exist', $this->voucherBusinessNotification->error);
    }

    public function testSetWithException()
    {
        $this->voucherBusinessNotification = new VoucherNotificationIssue(1, 'voucher.generate_failed', [1], ['email', 'sms']);

        $this->setExpectedException('\Exception');
        $this->voucherBusinessNotification->__set('fake_property', true);
    }
}