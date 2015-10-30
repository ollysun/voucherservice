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
        $this->voucherBusinessNotification = new VoucherNotificationIssue(1, 'Voucher:email', [1]);

        $this->voucherBusinessNotification->__set('error', 'no such file exist');
        $this->assertEquals('no such file exist', $this->voucherBusinessNotification->error);
    }

    public function testSetWithException()
    {
        $this->voucherBusinessNotification = new VoucherNotificationIssue(1, 'Voucher:email', [1]);

        $this->setExpectedException('\Exception');
        $this->voucherBusinessNotification->__set('fake_property', true);
    }
}