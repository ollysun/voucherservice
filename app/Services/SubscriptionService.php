<?php namespace Voucher\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Log;
use Illuminate\Http\Request;

class SubscriptionService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client(
            array(
                'base_uri' => getenv('SUBSCRIPTION_API_URL'),
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'x-api-key'=>'d78efa0e-4ea2-426d-90da-ac5fe06d956f'
                )
            )
        );
    }

    public function subscriptionApi($routes, $method, $bodyParams = '')
    {
        try {
            $res = $this->client->$method($routes);
        } catch (RequestException $e) {
            if ($e->getResponse()->getStatusCode() != 200 && $e->getResponse()->getStatusCode() != 201) {
                return false;
            }
        }

        $resp = $res->getBody()->getContents();
        $response = json_decode($resp, true);
        Log::info('Processing SUB - SERVICE - Production plan_data', array(
            'susbcription_data' => $response
        ));
        return $response;
    }
}
