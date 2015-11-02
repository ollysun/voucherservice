<?php
use Voucher\Notification\VoucherNotification;

/**
 * Created by PhpStorm.
 * User: tech7
 * Date: 10/16/15
 * Time: 10:14 AM
 */
class VoucherNotificationTest extends TestCase
{
    protected $voucherNotification;

    public function testSet()
    {
        $this->voucherNotification = new VoucherNotification(1, 'Voucher:email', [1]);

        $this->voucherNotification->__set('file_name', 'test.csv');
        $this->assertEquals('test.csv', $this->voucherNotification->file_name);
    }

    public function testSetWithException()
    {
        $this->voucherNotification = new VoucherNotification(1, 'Generate Voucher Initiated', [1]);

        $this->setExpectedException('\Exception');
        $this->voucherNotification->__set('fake_property', true);
    }
}