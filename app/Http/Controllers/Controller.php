<?php namespace Voucher\Http\Controllers;

use Iroko\Common\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Log;

class Controller extends ApiController
{
    protected $request;

    protected $log = array();

    const LOGTITLE = "VOUCHER-SERVICE-DEVELOPMENT";

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->log = ['uri'=>$request->getUri(),
            'resource'=>$request->getRequestUri(),
            'method'=>$request->getMethod()
        ];
    }

    protected function buildExceptionResponse(\Exception $exception)
    {
        Log::error(self::LOGTITLE, array_merge(
            [
                'error' => $exception->getMessage()
            ],
            $this->log
        ));
        switch ($exception->getCode()) {
            case 400:
                return $this->errorWrongArgs(new MessageBag([$exception->getMessage()]));
            case 404:
                return $this->errorNotFound($exception->getMessage());
            case 403:
                return $this->errorForbidden($exception->getMessage());
            case 401:
                return $this->errorUnauthorized($exception->getMessage());
            default:
                return $this->errorInternalError($exception->getMessage());
        }
    }

    protected function buildSuccessResponse($data, $code)
    {
        Log::info(self::LOGTITLE, array_merge(
            [
                'success' => $data
            ],
            $this->log
        ));
        switch ($code) {
            case 201:
                return $this->respondCreated($data);
            case 200:
                return $this->respondWithArray($data);
            default:
                return $this->respondWithArray($data);
        }
    }
}
