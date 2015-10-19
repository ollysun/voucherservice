<?php namespace Voucher\Voucher;

use Voucher\Repositories\VoucherLogsRepository;
use Voucher\Services\SubscriptionService;
use Voucher\Services\PlanService;
use Voucher\Repositories\VouchersRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Aws\Sqs\SqsClient;
use DateTime;

class Voucher
{
    /**
     * Voucher repository model.
     *
     * @var VouchersRepository
     */
    protected $voucher_repository;

    /**
     * Voucher Logs repository model.
     *
     * @var VoucherLogsRepository
     */
    protected $voucher_logs_repository;

    /**
     * Subscription service api end-point.
     *
     * @var SubscriptionService
     */
    protected $subscriptions_api;

    /**
     * Plans service api end-point.
     *
     * @var PlanService
     */
    protected $plans_api;

    /**
     * Sqs Configuration.
     *
     * @var Config
     */
    protected $config;

    /**
     * Sqs Client.
     *
     * @var SqsClient
     */
    protected $sqs_client;

    /**
     * Creates a new voucher instance
     *
     * @param VouchersRepository $voucher
     * @param VoucherLogsRepository $voucher_logs_repository
     */
    public function __construct(VouchersRepository $voucher, VoucherLogsRepository $voucher_logs_repository)
    {
        $this->voucher_repository = $voucher;

        $this->voucher_logs_repository = $voucher_logs_repository;

        $this->config = Config::get('sqs');

        $this->sqs_client = new SqsClient($this->config['aws']);
    }

    /**
     * Sets the subscription-service api end-point.
     *
     * @param SubscriptionService $subscription
     */
    public function setSubscriptionService(SubscriptionService $subscription)
    {
        $this->subscriptions_api = $subscription;
    }

    /**
     * Sets the plans-service api end-point.
     *
     * @param PlanService $plans
     */
    public function setPlansService(PlanService $plans)
    {
        $this->plans_api = $plans;
    }

    /**
     * Redeem a voucher code for subscription.
     *
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function redeem($data)
    {
        try {
            $voucher = $this->isVoucherExistsAndValid($data);
            $subscription = $this->isVoucherValidForUser($data, $voucher);

            $subscription_data = [
                'user_id'   => $data['user_id'],
                'platform'  => $data['platform'],
                'customer_id' => $subscription['customer_id'],
                'plan_id' => $subscription['plan_id'],
                'voucher_id' => $voucher['id'],
                'code' => $voucher['code'],
                'voucher_status' => $voucher['voucher_status'],
                'subscription_duration' => $voucher['duration'].' '. $voucher['period'],
            ];
            $this->sendSubscribeRequest($subscription_data);
            return true;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Checks if a voucher code exist and still valid.
     *
     * @param $post_data
     * @return mixed
     * @throws \Exception
     */
    protected function isVoucherExistsAndValid($post_data)
    {
        $voucher = $this->voucher_repository->getVoucherByCode($post_data['code']);

        $data = [
            'voucher_id' => NULL,
            'user_id' => $post_data['user_id'],
            'platform' => $post_data['platform'],
            'action' => 'attempt'
        ];

        if (!empty($voucher['data'])) {
            if ($voucher['data']['status'] == 'active' || $voucher['data']['status'] == 'claiming') {
                $data['voucher_id'] = $voucher['data']['id'];

                $expires_on = DateTime::createFromFormat('Y-m-d H:i:s', $voucher['data']['valid_to']);
                $now = DateTime::createFromFormat('Y-m-d H:i:s', Carbon::now());
                
                if ($expires_on >= $now) {
                    if ($voucher['data']['limit'] > $voucher['data']['total_redeemed']) {
                        if ($voucher['data']['limit'] ==  ($voucher['data']['total_redeemed'] + 1)) {
                            $voucher['data']['voucher_status'] = "claimed";
                        } else {
                            $voucher['data']['voucher_status'] = "claiming";
                        }
                        return $voucher['data'];
                    } else {
                        $data['comments'] = 'User tried redeeming a voucher code that has reached its usage limit.';
                    }
                } else {
                    $data['comments'] = 'User tried redeeming a voucher code whose validity period has passed.';
                }
            } else {
                $data['comments'] = 'User tried redeeming a voucher code that has been used or not active.';
            }
        } else {
            $data['comments'] = 'User tried redeeming a non existing voucher code.';
        }
        $this->voucher_logs_repository->addVoucherLog($data);
        throw new \Exception('The voucher code is invalid. '. $data['comments']);
    }

    /**
     * Checks if a user is eligible to subscribe using a specific voucher code.
     *
     * @param $user_data
     * @param $voucher_data
     * @return array
     * @throws \Exception
     */
    protected function isVoucherValidForUser($user_data, $voucher_data)
    {
        $subscription = $this->subscriptions_api->subscriptionApi('/subscriptions/'. $user_data['user_id'], 'get');

        if ($subscription) {
            switch ($voucher_data['category']) {
                case 'new_expire':
                    if (!$subscription['data']['is_active']){
                        return $subscription['data'];
                    }
                    break;

                case 'expired':
                    if (!$subscription['data']['is_active']){
                        return $subscription['data'];
                    }
                    break;

                case 'active':
                    if ($plan = $this->plans_api->plansApi('/plans/'. $subscription['data']['plan_id'], 'get')) {
                        if ($subscription['data']['is_active'] && !$plan['data']['is_recurring']) {
                            return $subscription['data'];
                        }
                        break;
                    }
            }
        } else {
            if ($voucher_data['category'] == 'new' || $voucher_data['category'] == 'new_expire') {
                $subscription = [
                    'customer_id' => NULL,
                    'plan_id' => NULL,
                    'subscription_platform_id'  => NULL,
                ];
                return $subscription;
            }
        }

        $data = [
            'voucher_id' => $voucher_data['id'],
            'user_id' => $user_data['user_id'],
            'platform' => $user_data['platform'],
            'action' => 'attempt',
            'comments'   => 'User is not eligible to use the voucher code.',
        ];
        $this->voucher_logs_repository->addVoucherLog($data);
        throw new \Exception('You are not eligible to use this voucher code.');
    }

    /**
     * Push a subscription request/job to sqs queue, to activate a
     * subscription by voucher code.
     *
     * @param $data
     * @return bool
     * @throws \Exception
     */
    protected function sendSubscribeRequest($data)
    {
        $subscribe_response = $this->sqs_client->sendMessage(array(
            'QueueUrl' => $this->config['endpoint_url'],
            'MessageBody' => json_encode($data)
        ));

        $log_data = [
            'voucher_id' => $data['voucher_id'],
            'user_id'   => $data['user_id'],
            'platform'  => $data['platform']
        ];

        if ($subscribe_response->get('MessageId')) {
            $this->voucher_repository->updateVoucherStatus($data);
            $log_data['comments'] = 'Successfully subscribed using a valid voucher code.';
            $log_data['action'] = "success";
            $this->voucher_logs_repository->addVoucherLog($log_data);
            return true;
        } else {
            $log_data['comments'] = 'Something went wrong on queueing the subscribe by voucher request.';
            $log_data['action'] = "attempt";
            $this->voucher_logs_repository->addVoucherLog($log_data);
            throw new \Exception('Something went wrong, please try again in a later time.');
        }
    }
}
