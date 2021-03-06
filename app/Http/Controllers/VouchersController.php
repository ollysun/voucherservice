<?php namespace Voucher\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Voucher\Repositories\VouchersRepository;
use Voucher\Services\PlanService;
use Voucher\Validators\VoucherValidator;
use Voucher\Validators\VoucherJobValidator;
use Voucher\Voucher\Voucher;
use Illuminate\Support\Facades\Input;
use Voucher\Services;
use Log;

class VouchersController extends Controller
{
    protected $repository;

    protected $voucher;

    public function __construct(Request $request, VouchersRepository $repository, Voucher $voucher)
    {
        parent::__construct($request);

        $this->repository = $repository;

        $this->voucher = $voucher;
    }

    public function index()
    {
        try {
            $fields['query'] = Input::get('query', null);
            $fields['sort'] = Input::get('sort', 'created_at');
            $fields['order'] = Input::get('order', 'ASC');
            $fields['limit'] = Input::get('limit', 5);
            $fields['offset'] = Input::get('offset', 1);

            $rules = VoucherValidator::getParamsRules();
            $messages = VoucherValidator::getMessages();
            $validator = Validator::make($fields, $rules, $messages);

            if ($validator->fails()) {
                Log::error(SELF::LOGTITLE, array_merge(
                    [
                        'error' => $validator->errors()
                    ],
                    $this->log
                ));
                return $this->errorWrongArgs($validator->errors());
            } else {
                $data = $this->repository->getVouchers($fields);
                if (is_null($data)) {
                    Log::error(SELF::LOGTITLE, array_merge(
                        [
                            'error' => 'No Vouchers were found.'
                        ],
                        $this->log
                    ));
                    return $this->errorNotFound('No Vouchers were found.');

                } else {
                    Log::info(SELF::LOGTITLE, array_merge(
                        [
                            'Create Successful' => 'Voucher successfully created'
                        ],
                        $this->log
                    ));
                    return $this->respondWithArray($data);
                }
            }
        } catch (\Exception $e) {
            Log::error(SELF::LOGTITLE, array_merge(
                [
                    'error' => $e->getMessage()
                ],
                $this->log
            ));
            return $this->errorInternalError($e->getMessage());
        }
    }

    public function create()
    {
        try {
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
                $voucherCode = $this->repository->getVoucherCodeByStatus("new");
                if (!$voucherCode) {
                    Log::error(SELF::LOGTITLE, array_merge(
                        [
                            'error' => 'No voucher codes were found.'
                        ],
                        $this->log
                    ));
                    return $this->errorNotFound('No voucher codes were found.');
                } else {
                    $firstTwoLetter = substr($inputs['title'], 0, 2);
                    $inputs['code'] = strtoupper($firstTwoLetter . $voucherCode['data']['voucher_code']);

                    DB::begintransaction();
                    try {
                        $voucher = $this->repository->create($inputs);//@TODO transaction
                        $this->repository->updateVoucherCodeStatusByID($voucherCode['data']['id']);
                        Log::info(SELF::LOGTITLE, array_merge(
                            ['success' => 'Voucher successfully created'],
                            $this->log
                        ));
                        $response = $this->respondCreated($voucher);

                    } catch (\Exception $e) {
                        DB::rollback();
                        throw new \Exception($e->getMessage());
                    }
                    DB::commit();
                    return $response;
                }
            }
        } catch (\Exception $e) {
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
        try {
            $fields = $this->request->all();
            $fields['id'] = $voucher_id;

            $rules = array_merge(
                VoucherValidator::getUpdateRules(),
                VoucherValidator::getIdRules()
            );
            $messages = VoucherValidator::getMessages();
            $validator = Validator::make($fields, $rules, $messages);
            if ($validator->fails()) {
                Log::error(SELF::LOGTITLE, array_merge(
                    [
                        'error' => $validator->errors()
                    ],
                    $this->log
                ));
                return $this->errorWrongArgs($validator->errors());
            } else {
                $voucher = $this->repository->getVoucherById($voucher_id);
                if (!$voucher) {
                    return $this->errorNotFound('Check Id, voucher detail not found');
                } else {
                    $voucher_update =  $this->repository->update($voucher_id, $fields);
                    Log::info(SELF::LOGTITLE, array_merge(
                        [
                            'Update Successful' => 'Voucher successfully updated'
                        ],
                        $this->log
                    ));
                    return $this->respondWithArray($voucher_update);
                }
            }
        } catch (\Exception $e) {
            Log::error(SELF::LOGTITLE, array_merge(
                ['error' => $e->getMessage()],
                $this->log
            ));
            return $this->errorInternalError($e->getMessage());
        }
    }

    public function redeem()
    {
        try {
            $inputs = $this->request->all();
            $rules = VoucherValidator::getRedeemRules();
            $messages = VoucherValidator::getMessages();

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
                $this->voucher->setSubscriptionService(new Services\SubscriptionService());
                $this->voucher->setPlansService(new PlanService());
                $voucher = $this->voucher->redeem($inputs);
                return $this->respondWithArray($voucher);
            }
        } catch (\Exception $e) {
            return $this->buildExceptionResponse($e);
        }
    }

    public function bulkCreate()
    {
        try {
            $fields = $this->request->all();
            $rules = array_merge(
                VoucherValidator::getVoucherRules(),
                VoucherJobValidator::getBrandAndTotalRules()
            );
            $messages = VoucherValidator::getMessages();
            $validator = Validator::make($fields, $rules, $messages);
            if ($validator->fails()) {
                Log::error(SELF::LOGTITLE, array_merge(
                    ['error' => $validator->errors()],
                    $this->log
                ));
                return $this->errorWrongArgs($validator->errors());
            } else {
                DB::begintransaction();
                try {
                    $voucherJob = $this->repository->insertVoucherJob('new');
                    $this->repository->insertVoucherJobParamMetadata($fields, $voucherJob['data']['id']);
                    Log::info(SELF::LOGTITLE, array_merge(
                        [
                            'Create Successful' => 'Vouchers will be created and notified to Business team soon!'
                        ],
                        $this->log
                    ));
                    $response = $this->respondCreated([
                        'Bulk Voucher order is created, you will be notified once vouchers are generated!'
                    ]);
                } catch (\Exception $e) {
                    DB::rollback();
                    throw new \Exception($e->getMessage());
                }
                DB::commit();
                return $response;
            }
        } catch (\Exception $e) {
            Log::error(SELF::LOGTITLE, array_merge(
                [
                    'error' => $e->getMessage()
                ],
                $this->log
            ));
            return $this->errorInternalError($e->getMessage());
        }
    }
}
