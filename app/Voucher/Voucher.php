<?php namespace Voucher\Voucher;

use Voucher\Repositories\VoucherLogsRepository;
use Voucher\Services\SubscriptionService;
use Voucher\Services\PlanService;
use Voucher\Repositories\VouchersRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Aws\Sqs\SqsClient;

class Voucher
{
    protected $voucher_repository;

    protected $voucher_logs_repository;

    protected $subscriptions_api;

    protected $plans_api;

    protected $config;

    protected $sqs_client;

    public function __construct(VouchersRepository $voucher, VoucherLogsRepository $voucher_logs_repository)
    {
        $this->voucher_repository = $voucher;
        $this->voucher_logs_repository = $voucher_logs_repository;
        $this->config = Config::get('sqs');
        $this->sqs_client = new SqsClient($this->config['aws_credentials']);
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
            $user_eligible = $this->isVoucherValidForUser($data, $voucher);

            $subscription_data = [
                'user_id'   => $data['user_id'],
                'platform'  => $data['platform'],
                'customer_id' => $user_eligible['customer_id'],
                'plan_id' => $user_eligible['plan_id'],
                'voucher_id' => $voucher['id'],
                'code' => $voucher['code']
            ];
            $this->subscribeUserUsingVoucher($subscription_data);
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
        } else {
            if ($voucher->status == 'active' || $voucher->status == 'claiming') {
                if ($voucher->valid_to >= Carbon::now()) {
                    return $voucher['data'];
                } else {
                    $data = [
                        'voucher_id' => $voucher['data']['id'],
                        'user_id' => $data['user_id'],
                        'platform' => $data['platform'],
                        'action' => 'attempt',
                        'comment'   => 'User tried using a voucher code whose validity period has passed.',
                    ];
                }
            } else {
                $data = [
                    'voucher_id' => $voucher['data']['id'],
                    'user_id' => $data['user_id'],
                    'platform' => $data['platform'],
                    'action' => 'attempt',
                    'comment'   => 'User tried using a voucher code that is not active.',
                ];
            }
        }
        $this->voucher_logs_repository->addVoucherLog($data);
        throw new \Exception('The voucher code is invalid.');
    }

    protected function isVoucherValidForUser($user_data, $voucher_data)
    {
        $subscription = $this->subscriptions_api->subscriptionApi('/subscriptions/'. $user_data['user_id'], 'get');

        if($subscription['data']) {
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
                    $plan = $this->plans_api->plansApi('/plans/'. $subscription['data']['plan_id'], 'get');
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
                $subscription = [
                    'customer_id' => NULL,
                    'plan_id' => NULL,
                    'subscription_platform_id'  => NULL,
                ];
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

    protected function subscribeUserUsingVoucher($subscription_data)
    {
        $subscribe_response = $this->sqs_client->sendMessage(array(
                'QueueUrl' => $this->config['outgoing_queue']['endpoint_url'],
                'MessageBody' => base64_encode(json_encode($subscription_data))
        ));

        if ($subscribe_response->get('MessageId')) {
            $data = [
                'voucher_id' => $subscription_data['voucher_id'],
                'user_id'   => $subscription_data['user_id'],
                'platform'  => $subscription_data['platform'],
                'action' => 'success',
                'comment' => 'User successfully subscribed using a valid voucher code.',
            ];

            $this->voucher_logs_repository->addVoucherLog($data);
            return true;

        } else {
            $data = [
                'voucher_id' => $subscription_data['id'],
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
