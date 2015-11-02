<?php namespace Voucher\Notification;

use Iroko\Notify\Messages\Notification;

class VoucherNotification extends Notification
{
    public $file_name;

    public $s3_url;

    public function __construct($priority, $type, $recipients)
    {
        parent::__construct($priority, $type, $recipients);
    }

    public function __set($property, $value)
    {
        try {
            if (property_exists($this, $property)) {
                $this->$property = $value;
            } else {
                throw new \Exception('Property can\'t be set');
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
