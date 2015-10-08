<?php
/**
 * Created by PhpStorm.
 * User: Tech-1
 * Date: 10/8/15
 * Time: 3:28 PM
 */

namespace Voucher\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Voucher\Repositories\VouchersRepository;
use Voucher\Validators\VoucherValidator;
use League\Csv\Writer;

class VouchersController extends Controller {

    protected $repository;


    public function __construct(Request $request, VouchersRepository $repository)
    {
        parent::__construct($request);
        $this->repository = $repository;
    }

    public function store()
    {
        try{
            $inputs = $this->request->all();
            $messages = VoucherValidator::getMessages();
            $rules = VoucherValidator::getVoucherRules();

            $validators = Validator::make($inputs, $rules, $messages);

        }catch (\Exception $ex)
        {

        }
    }

    
}