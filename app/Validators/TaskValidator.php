<?php namespace Voucher\Validators;
use Illuminate\Validation\Validator as IlluminateValidator;

class TaskValidator extends IlluminateValidator
{
    public static function getVoucherCodeRules()
    {
        return [
            'total' => 'required|regex:/(^[0-9]+$)+/|min:1',
        ];
    }

    public static function getMessages()
    {
        return [
            'total.required' => 'The Total is required',
            'total.regex' => 'The Total must be an integer',
            'total.min' => 'The Total must be positive'
        ];
    }
}
