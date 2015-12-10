<?php namespace Voucher\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Voucher\Repositories\VoucherLogsRepository;
use Voucher\Validators\VoucherValidator;
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
        try {
            $fields = [
                'user_id' => $user_id,
                'order' => Input::get('order', 'ASC'),
                'limit' => Input::get('limit', 100),
                'offset' => Input::get('offset', 1)
            ];

            $rules = VoucherValidator::getUserIdRules();
            $message = VoucherValidator::getMessages();
            $validator = Validator::make($fields, $rules, $message);

            if ($validator->fails()) {
                Log::error(SELF::LOGTITLE, array_merge(
                    ['error' => $validator->errors()],
                    $this->log
                ));
                return $this->errorWrongArgs($validator->errors());
            }
            $voucher = $this->repository->getVoucherClaimedByUserID($fields);

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
                            'error' => 'No claimed voucher by user: ' . $user_id
                        ],
                        $this->log
                    )
                );
                return $this->errorNotFound('No claimed voucher by user: ' . $user_id);
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