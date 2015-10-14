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

        $this->sqs_client = new SqsClient($this->config['aws_credentials']);
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
            $user_eligible = $this->isVoucherValidForUser($data, $voucher);

            $subscription_data = [
                'user_id'   => $data['user_id'],
                'platform'  => $data['platform'],
                'customer_id' => $user_eligible['customer_id'],
                'plan_id' => $user_eligible['plan_id'],
                'voucher_id' => $voucher['id'],
                'code' => $voucher['code'],
                'voucher_status' => 'claiming'
            ];
            $this->subscribeUserUsingVoucher($subscription_data);
            return true;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Checks if a voucher code exist and still valid.
     *
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    protected function isVoucherExistsAndValid($data)
    {
        $voucher = $this->voucher_repository->getVoucherByCode($data['code']);

        if (empty($voucher['data'])) {
            $data = [
                'voucher_id' => NULL,
                'user_id' => $data['user_id'],
                'platform' => $data['platform'],
                'action' => 'attempt',
                'comment'   => 'User tried redeeming a non existing voucher code.',
            ];
        } else {
            if ($voucher['data']['status'] == 'active') {
                $code_redeem_count = $this->voucher_logs_repository->getVoucherRedeemedCount($data['data']['id']);

                if (Carbon::createFromFormat('Y-m-d H:i:s', $voucher['data']['valid_to']) >= Carbon::now()) {
                    if ($voucher['data']['limit'] <= $code_redeem_count) {
                        return $voucher['data'];
                    } else {
                        $data = [
                            'voucher_id' => $voucher['data']['id'],
                            'user_id' => $data['user_id'],
                            'platform' => $data['platform'],
                            'action' => 'attempt',
                            'comment'   => 'User tried redeeming a voucher code that has reached its redeem limit.',
                        ];
                    }
                } else {
                    $data = [
                        'voucher_id' => $voucher['data']['id'],
                        'user_id' => $data['user_id'],
                        'platform' => $data['platform'],
                        'action' => 'attempt',
                        'comment'   => 'User tried redeeming a voucher code whose validity period has passed.',
                    ];
                }
            } else {
                $data = [
                    'voucher_id' => $voucher['data']['id'],
                    'user_id' => $data['user_id'],
                    'platform' => $data['platform'],
                    'action' => 'attempt',
                    'comment'   => 'User tried redeeming a voucher code that has been used or not active.',
                ];
            }
        }
        $this->voucher_logs_repository->addVoucherLog($data);
        throw new \Exception('The voucher code is invalid.');
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
                'comment'   => 'User is not eligible to use the voucher code.',
            ];

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
                    'comment'   => 'User is not eligible to use the voucher code.',
                ];
            }
        }

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
    protected function subscribeUserUsingVoucher($data)
    {
        $subscribe_response = $this->sqs_client->sendMessage(array(
                'QueueUrl' => $this->config['outgoing_queue']['endpoint_url'],
                'MessageBody' => base64_encode(json_encode($data))
        ));

        if ($subscribe_response->get('MessageId')) {
            $log_data = [
                'voucher_id' => $data['voucher_id'],
                'user_id'   => $data['user_id'],
                'platform'  => $data['platform'],
                'action' => 'success',
                'comment' => 'User successfully subscribed using a valid voucher code.',
            ];

            $this->voucher_repository->setVoucherStatusToClaiming($data);
            $this->voucher_logs_repository->addVoucherLog($log_data);
            return true;

        } else {
            $log_data = [
                'voucher_id' => $data['id'],
                'user_id'   => $data['user_id'],
                'platform'  => $data['platform'],
                'action' => 'attempt',
                'comment' => 'User used a valid voucher code, but something went wrong on queuing the subscribe request.',
            ];
            $this->voucher_logs_repository->addVoucherLog($log_data);
            throw new \Exception('Something went wrong on sending a message to sqs, while subscribing by voucher.');
        }
    }
}
