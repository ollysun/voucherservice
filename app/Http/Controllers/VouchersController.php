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

            $validator = Validator::make($inputs, $rules, $messages);
            if ($validator->fails()) {
                Log::error(SELF::LOGTITLE, array_merge(
                    [
                        'error' => $validator->errors()
                    ],
                    $this->log
                ));
                return $this->errorWrongArgs($validator->errors());
            } else {
                $voucher = $this->repository->createOrUpdate(null,$inputs);
                Log::info(SELF::LOGTITLE, array_merge(
                    ['success' => 'Voucher successfully created'],
                    $this->log
                ));
                return $this->respondCreated($voucher);
            }
        }catch (\Exception $e)
        {
            Log::error(SELF::LOGTITLE, array_merge(
                [
                    'error' => $e->getMessage()
                ],
                $this->log
            ));
            return $this->errorInternalError($e->getMessage());
        }
    }

    public function update($voucher_id)
    {
        $fields = $this->request->all();
        $fields['id'] = $voucher_id;

        $rules = array_merge(
            VoucherValidator::getVoucherRules(),
            VoucherValidator::getIdRules()
        );
        $messages = VoucherValidator::getMessages();
        try{
            $validator = Validator::make($fields, $rules, $messages);
            if ($validator->fails()) {
                Log::error(SELF::LOGTITLE, array_merge(
                    ['error' => $validator->errors()],
                    $this->log
                ));
                return $this->errorWrongArgs($validator->errors());
            }else{
                if (!$this->repository->getVoucherById($voucher_id)) {
                    return $this->errorNotFound('Check Id, voucher detail not found');
                }else{
                    $voucher_update =  $this->repository->createOrUpdate($voucher_id, $fields);
                    Log::info(SELF::LOGTITLE, array_merge(
                        [
                            'successfully Update' => 'Voucher successfully update'
                        ],
                        $this->log
                    ));
                    return $this->respondWithArray($voucher_update);
                }
            }
        }catch (\Exception $e)
        {
            Log::error(SELF::LOGTITLE, array_merge(
                ['error' => $e->getMessage()],
                $this->log
            ));
            return $this->errorInternalError($e->getMessage());
        }

    }

    
}