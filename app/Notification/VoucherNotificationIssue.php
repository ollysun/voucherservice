<?php
namespace Voucher\Notification;

use Iroko\Notify\Messages\Notification;

class VoucherNotificationIssue extends Notification
{
    public $error;
    public $job_id;
    public $job_status;

    public function __construct($priority, $type, $recipients, $channels)
    {
        parent::__construct($priority, $type, $recipients, $channels);
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
