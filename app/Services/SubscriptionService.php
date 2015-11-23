<?php namespace Voucher\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Log;

class SubscriptionService
{
    protected $client;

    protected $response;

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
            $this->response = $this->client->$method($routes);
        } catch (RequestException $e) {
            if ($e->getResponse()->getStatusCode() != 200 && $e->getResponse()->getStatusCode() != 201) {
                return json_decode($e->getResponse()->getBody()->getContents(), true);
            }
        }

        $resp = $this->response->getBody()->getContents();
        $response = json_decode($resp, true);
        Log::info('Calling SUB - SERVICE - FROM - VOUCHER Production susbcription_data', array(
            'susbcription_data' => $response
        ));
        return $response;
    }
}
