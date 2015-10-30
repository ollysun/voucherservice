<?php namespace Voucher\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Log;

class PlanService
{
    protected $client;

    protected $response;

    public function __construct()
    {
        $this->client = new Client(
            array(
                'base_uri' => getenv('PLANS_API_URL'),
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'x-api-key'=>'d78efa0e-4ea2-426d-90da-ac5fe06d956f'
                )
            )
        );
    }

    public function plansApi($routes, $method, $bodyParams = '')
    {
        try {
            $this->response = $this->client->$method($routes);
        } catch (RequestException $e) {
            if ($e->getResponse()->getStatusCode() != 200 && $e->getResponse()->getStatusCode() != 201) {
                return false;
            }
        }

        $resp = $this->response->getBody()->getContents();
        $response = json_decode($resp, true);
        Log::info('Calling PLAN - SERVICE - FROM VOUCHER Production plan_data', array(
            'plan_data' => $response
        ));
        return $response;
    }
}
