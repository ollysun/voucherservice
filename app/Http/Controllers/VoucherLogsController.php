<?php namespace Voucher\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Voucher\Repositories\VoucherLogsRepository;
use Voucher\Services\PlanService;
use Voucher\Validators\VoucherValidator;
use Voucher\Validators\VoucherJobValidator;
use Voucher\Voucher\Voucher;
use Illuminate\Support\Facades\Input;
use Voucher\Services;
use Log;


class VoucherLogsController extends Controller {

    protected $repository;

    protected $voucher;

    public function __construct(Request $request, VoucherLogsRepository $repository, Voucher $voucher)
    {
        parent::__construct($request);

        $this->repository = $repository;

        $this->voucher = $voucher;
    }

    public function show($user_id)
    {
        $fields = [
            'id' => $user_id
        ];
        $rules = VoucherValidator::getIdRules();
        $message = VoucherValidator::getMessages();
        try {
            $validator = Validator::make($fields, $rules, $message);

            if ($validator->fails()) {
                Log::error(SELF::LOGTITLE, array_merge(
                    ['error' => $validator->errors()],
                    $this->log
                ));
                return $this->errorWrongArgs($validator->errors());

            } else {
                $voucher = $this->repository->getVoucherUserID($user_id);

                if ($voucher) {
                    Log::info(
                        SELF::LOGTITLE,
                        array(
                            [
                                'success' => 'Retrieve list of vouchers per user'
                            ],
                            $this->log
                        )
                    );
                    return $this->respondWithArray($voucher);

                } else {
                    Log::error(
                        SELF::LOGTITLE,
                        array_merge(
                            [
                                'error' => 'Vouchers detail Not Found ' . $user_id
                            ],
                            $this->log
                        )
                    );
                    return $this->errorNotFound('Voucher Users detail Not Found');
                }
            }
        } catch (\Exception $e) {
            Log::error(SELF::LOGTITLE, array_merge(
                ['error' => $e->getMessage()],
                $this->log
            ));
            return $this->buildExceptionResponse($e);
        }
    }

}