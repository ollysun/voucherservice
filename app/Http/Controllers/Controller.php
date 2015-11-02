<?php namespace Voucher\Http\Controllers;

use Iroko\Common\Http\Controllers\ApiController;
use Illuminate\Http\Request;

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
}
