<?php namespace Voucher\Voucher;

use Voucher\Repositories\VoucherLogsRepository;
use Voucher\Services\SubscriptionService;
use Voucher\Services\PlanService;
use Voucher\Repositories\IVouchersRepository;
use Carbon\Carbon;

class Voucher
{
    protected $voucher_repository;
    protected $voucher_logs_repository;
    protected $subscriptions_api;
    protected $plans_api;


    public function __construct(IVouchersRepository $voucher, VoucherLogsRepository $voucher_logs_repository)
    {
        $this->voucher_repository = $voucher;
        $this->voucher_logs_repository = $voucher_logs_repository;
    }

    public function setSubscriptionService(SubscriptionService $subscription)
    {
        $this->subscriptions_api = $subscription;
    }

    public function setPlansService(PlanService $plans)
    {
        $this->plans_api = $plans;
    }

    public function redeem($data)
    {
        try {
            $voucher = $this->isVoucherExistsAndValid($data);
            $user_valid_data = $this->isVoucherValidForUser($data, $voucher);

            $subscription_data = [
                'user_id'   => $data['user_id'],
                'platform'  => $data['platform'],
                'customer_id' => $user_valid_data['customer_id'], //get or generate this.
            ];

            $this->subscribeUserUsingVoucher($subscription_data, $voucher);
            return true;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    protected function isVoucherExistsAndValid($data)
    {
        $voucher = $this->voucher_repository->getVoucherByCode($data['code']);

        if (!$voucher) {
            $data = [
                'voucher_id' => NULL,
                'user_id' => $data['user_id'],
                'platform' => $data['platform'],
                'action' => 'attempt',
                'comment'   => 'User tried using a non existing voucher code.',
            ];
            $this->voucher_logs_repository->addVoucherLog($data);
            throw new \Exception('The voucher code is invalid.');

        } else {
            if ($voucher->valid_to < Carbon::now()) {
                $data = [
                    'voucher_id' => $voucher['data']['id'],
                    'user_id' => $data['user_id'],
                    'platform' => $data['platform'],
                    'action' => 'attempt',
                    'comment'   => 'User tried using a voucher code whose validity period has passed.',
                ];
                $this->voucher_logs_repository->addVoucherLog($data);
                throw new \Exception('The voucher code is invalid.');
            } else {
                if ($voucher->status == 'active' || $voucher->status == 'claiming') {
                    return $voucher['data'];
                } else {
                    $data = [
                        'voucher_id' => $voucher['data']['id'],
                        'user_id' => $data['user_id'],
                        'platform' => $data['platform'],
                        'action' => 'attempt',
                        'comment'   => 'User tried using a voucher code that is not active.',
                    ];
                    $this->voucher_logs_repository->addVoucherLog($data);
                    throw new \Exception('The voucher code is invalid.');
                }
            }
        }
    }

    protected function isVoucherValidForUser($user_data, $voucher_data)
    {
        $subscription = $this->subscriptions_api->subscriptionApi('/subscriptions/'. $user_data['user_id'], 'get');

        if($subscription['data']) {
            $plan = $this->plans_api->plansApi('/plans/'. $subscription['data']['plan_id'], 'get');

            switch ($voucher_data['category']) {
                case 'new_expire':
                    if (!$subscription['data']['is_active']){
                        return $subscription;
                    }
                    break;

                case 'expired':
                    if (!$subscription['data']['is_active']){
                        return $subscription;
                    }
                    break;

                case 'active':
                    if ($subscription['data']['is_active'] && !$plan['data']['is_recurring']){
                        return $subscription;
                    }
                    break;
            }

            $data = [
                'voucher_id' => $voucher_data['data']['id'],
                'user_id' => $user_data['user_id'],
                'platform' => $user_data['platform'],
                'action' => 'attempt',
                'comment'   => 'User tried using a voucher code not meant for it\'s subscription status.',
            ];
            $this->voucher_logs_repository->addVoucherLog($data);
            throw new \Exception('The voucher code is invalid.');

        } else {
            if ($voucher_data['category'] == 'new' || $voucher_data['category'] == 'new_expire') {
                return $subscription;
            } else {
                $data = [
                    'voucher_id' => $voucher_data['data']['id'],
                    'user_id' => $user_data['user_id'],
                    'platform' => $user_data['platform'],
                    'action' => 'attempt',
                    'comment'   => 'User tried using a voucher code not meant for it\'s subscription status.',
                ];
                $this->voucher_logs_repository->addVoucherLog($data);
                throw new \Exception('The voucher code is invalid.');
            }
        }
    }

    protected function subscribeUserUsingVoucher($subscription_data, $voucher_data)
    {
        $subscribe = $this->subscriptions_api->subscriptionApi('/subscriptions/using-voucher', 'post');

        if ($subscribe['data']) {
            $data = [
                'voucher_id' => $voucher_data['id'],
                'user_id'   => $subscription_data['user_id'],
                'platform'  => $subscription_data['platform'],
                'action' => 'success',
                'comment' => 'User successfully subscribed using a valid voucher code.',
            ];

            $this->voucher_logs_repository->addVoucherLog($data);
            return true;

        } else {
            $data = [
                'voucher_id' => $voucher_data['id'],
                'user_id'   => $subscription_data['user_id'],
                'platform'  => $subscription_data['platform'],
                'action' => 'attempt',
                'comment' => 'User tried subscribing with valid voucher, but something went wrong on the subscription service.',
            ];

            $this->voucher_logs_repository->addVoucherLog($data);
            throw new \Exception('Something went wrong on the subscription service.');
        }
    }
}
