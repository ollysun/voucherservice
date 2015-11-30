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
            //$data['code'] = strtoupper($data['code']);
            $voucher = $this->isVoucherExistsAndValid($data);
            $subscription = $this->isVoucherValidForUser($data, $voucher);

            $subscription_data = [
                'user_id' => $data['user_id'],
                'platform' => $data['platform'],
                'customer_id' => $subscription['customer_id'],
                'plan_id' => $subscription['plan_id'],
                'voucher_id' => $voucher['id'],
                'code' => $voucher['code'],
                'voucher_status' => $voucher['voucher_status'],
                'subscription_duration' => $voucher['duration'] . ' ' . $voucher['period'],
            ];
            $this->sendSubscribeRequest($subscription_data);
            return $voucher;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
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
            'voucher_id' => null,
            'user_id' => $post_data['user_id'],
            'platform' => $post_data['platform'],
            'action' => 'attempt'
        ];

        if ($voucher['data']) {
            if ($voucher['data']['status'] == 'active' || $voucher['data']['status'] == 'claiming') {
                $data['voucher_id'] = $voucher['data']['id'];

                $expires_on = DateTime::createFromFormat('Y-m-d H:i:s', $voucher['data']['valid_to']);
                $now = DateTime::createFromFormat('Y-m-d H:i:s', Carbon::now());

                if ($expires_on >= $now) {
                    if (!$this->voucher_logs_repository->redeemedByUser($post_data['user_id'], $voucher['data']['id'])) {
                        if ($voucher['data']['limit'] > $voucher['data']['total_redeemed']) {
                            if ($voucher['data']['limit'] == ($voucher['data']['total_redeemed'] + 1)) {
                                $voucher['data']['voucher_status'] = "claimed";
                            } else {
                                $voucher['data']['voucher_status'] = "claiming";
                            }
                            return $voucher['data'];
                        } else {
                            $data['comments'] = 'Voucher limit reached.';
                        }
                    } else {
                        $data['comments'] = 'Voucher already claimed.';
                    }
                } else {
                    $data['comments'] = 'Voucher has expired.';
                }
            } else {
                $data['comments'] = 'Voucher not active.';
            }
        } else {
            $data['comments'] = 'Voucher not found.';
        }
        $this->voucher_logs_repository->addVoucherLog($data);
        throw new \Exception($data['comments'], 400);
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
        $subscription = $this->subscriptions_api->subscriptionApi('/subscriptions/' . $user_data['user_id'], 'get');

        if (isset($subscription['error'])) {
            if ($subscription['error']['http_code'] != 404) {
                throw new \Exception($subscription['error']['message'], $subscription['error']['http_code']);
            }
        }

        if (isset($subscription['data'])) {
            $date = date("Y-m-d H:i:s", time());
            switch ($voucher_data['category']) {
                case 'new_expired':

                    if (!$subscription['data']['is_active'] || $subscription['data']['expiry_time'] < $date) {
                        return $subscription['data'];
                    }
                    break;
                case 'expired':
                    if (!$subscription['data']['is_active'] || $subscription['data']['expiry_time'] < $date ) {
                        return $subscription['data'];
                    }
                    break;
                case 'active':
                    if(isset($subscription['data']['plan_id'])) {
                        $plan = $this->plans_api->plansApi('/plans/' . $subscription['data']['plan_id'], 'get');
                        if (isset($plan['error'])) {
                            throw new \Exception($plan['error']['message'], $plan['error']['http_code']);
                        }
                        if (isset($plan['data'])) {
                            if ($subscription['data']['is_active'] && !$plan['data']['is_recurring']) {
                                return $subscription['data'];
                            }
                            break;
                        }
                    } else {
                        return $subscription['data'];
                    }
            }
        } else {
            if ($voucher_data['category'] == 'new' || $voucher_data['category'] == 'new_expired') {
                $subscription = [
                    'customer_id' => null,
                    'plan_id' => null,
                    'subscription_platform_id' => null,
                ];
                return $subscription;
            }
        }

        $data = [
            'voucher_id' => $voucher_data['id'],
            'user_id' => $user_data['user_id'],
            'platform' => $user_data['platform'],
            'action' => 'attempt',
            'comments' => 'User is not eligible to use the voucher code.',
        ];
        $this->voucher_logs_repository->addVoucherLog($data);
        throw new \Exception('You are not eligible to use this voucher code.', 400);
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
            'user_id' => $data['user_id'],
            'platform' => $data['platform']
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
            throw new \Exception('Something went wrong, please try again in a later time.', 500);
        }
    }
}