<?php namespace Voucher\Validators;

use Illuminate\Validation\Validator as IlluminateValidator;

class VoucherValidator extends IlluminateValidator
{
    public static function getVoucherRules()
    {
        return [
            'user_id' => 'regex:/(^[0-9]+$)+/|required',
            'platform' => 'string|required',
            'valid_form' => 'date|date_format:Y-m-d H:i:s',
            'valid_to' => 'date|date_format:Y-m-d H:i:s|after:valid_from',
            'status' => 'required|regex:/(^[0-9]+$)+/',
            'title' => 'required|regex:/(^[0-9]+$)+/',
            'description' => 'required|string',
            'location' => 'required|string',
            'is_limited' => 'required|boolean',
            'limit' => 'required|integer',
            'period' => 'required|regex:/(^[0-9]+$)+/',
            'duration' => 'required|regex:/(^[0-9]+$)+/',
            'category' => 'required|regex:/(^[0-9]+$)+/',
            'type' => 'required|integer',
            'code' => 'required|string'
        ];
    }

    public static function getIdRules()
    {
        return [
            'id' => 'required|regex:/^[0-9]+$/'
        ];
    }

    public static function getMessages()
    {
        return [
            'platform.required' => 'The platform required.',
            'platform.string' => 'The platform can on be a string.',
            'user_id.regex' => 'The User Id can only be number',
            'user_id.required' => 'The User id is required.',
            'valid_from.date_format' => 'Provide the voucher valid From date',
            'valid_to.date_format' => 'Provide the voucher valid To date',
            'status.required' => 'Voucher status is required',
            'status.regex' => 'Voucher status can only be number',
            'title.required' => 'Voucher title is required',
            'title.string' => 'Voucher title can only be string',
            'description.string' => 'Voucher description can only be string',
            'location.string' => 'Provide the Voucher location',
            'is_limit.boolean' => 'Provide the is_limit condition',
            'limit.integer' => 'Provide the limit number',
            'period.regex' => 'Provide the period for the voucher',
            'duration.integer' => 'Specify the Voucher duration',
            'category.integer' => 'Provide the category status',
            'code.required' => 'Provide the voucher code',
            'code.string' => 'Voucher code can only be string',
            'type.integer' => 'Provide the Voucher Type',
            'id.required' => 'The voucher Id is required',
            'id.regex' => 'Voucher Id can only be integer.',
        ];
    }
}