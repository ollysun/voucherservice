<?php
use Voucher\Notification\VoucherBusinessNotification;

/**
 * Created by PhpStorm.
 * User: tech7
 * Date: 10/16/15
 * Time: 10:23 AM
 */
class VoucherBusinessNotificationTest extends TestCase
{
    protected $voucherBusinessNotification;

    public function testSet()
    {
        $this->voucherBusinessNotification = new VoucherBusinessNotification(1, 'Generate Voucher Initiated', [1]);
        $this->assertNull($this->voucherBusinessNotification->file_name);

        $this->voucherBusinessNotification->__set('file_name', 'test.csv');
        $this->assertEquals('test.csv', $this->voucherBusinessNotification->file_name);
    }

    public function testSetWithException()
    {
        $this->voucherBusinessNotification = new VoucherBusinessNotification(1, 'Generate Voucher Initiated', [1]);

        $this->setExpectedException('\Exception');
        $this->voucherBusinessNotification->__set('fake_property', true);
    }
}