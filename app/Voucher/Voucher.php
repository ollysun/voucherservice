<?php
namespace Voucher\Voucher;

use Voucher\Event;
use Voucher\Services\SubscriptionService;
use Voucher\Services\PlanService;

class Voucher
{
    protected $repository;
    protected $subscription;
    protected $plans;


    public function __construct(IVouchersRepository $voucher)
    {
        $this->repository = $voucher;
    }

    public function setSubscriptionService(SubscriptionService $subscription)
    {
        $this->subscription = $subscription;
    }

    public function setPlansService(PlanService $plans)
    {
        $this->plans = $plans;
    }

    public function redeem($data)
    {

    }
}
