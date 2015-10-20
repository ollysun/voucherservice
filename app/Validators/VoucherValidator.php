<?php namespace Voucher\Validators;

use Illuminate\Validation\Validator as IlluminateValidator;

class VoucherValidator extends IlluminateValidator
{
    public static function getParamsRules()
    {
        return [
            'query' => 'string',
            'sort' => 'in:created_at,updated_at,user_id,type,status,category,is_limited,valid_from,valid_to',
            'limit' => 'regex:/(^[0-9]+$)+/',
            'order' => 'in:ASC,DESC',
            'offset' => 'regex:/(^[0-9]+$)+/'
        ];
    }

    public static function getVoucherRules()
    {
        return [
            'type' => 'required|string|in:time,discount',
            'status' => 'required|string|in:active,claiming,claimed,deleted,expired,inactive',
            'category' => 'required|string|in:new,expired,active,new_expired',
            'title' => 'required|string|in:INTERNAL,VODAFONE_GHANA_STAFF_MOBILE,VODAFONE_GHANA_CUSTOMER_MOBILE,VODAFONE_GHANA_STAFF_FIXEDLINE',
            'location' => 'string',
            'description' => 'string',
            'duration' => 'required|regex:/(^[0-9]+$)+/',
            'period' => 'required|string|in:day,week,month,year',
            'valid_from' => 'required|date|date_format:Y-m-d H:i:s',
            'valid_to' => 'required|date|date_format:Y-m-d H:i:s|after:valid_from',
            'is_limited' => 'boolean',
            'limit' => 'required|regex:/(^[0-9]+$)+/'
        ];
    }

    public static function getUpdateRules()
    {
        return [
            'id' => 'required|regex:/(^[0-9]+$)+/',
            'status' => 'string|in:active,claiming,claimed,deleted,expired,inactive',
            'title' => 'string|in:INTERNAL,VODAFONE_GHANA_STAFF_MOBILE,VODAFONE_GHANA_CUSTOMER_MOBILE,VODAFONE_GHANA_STAFF_FIXEDLINE',
            'location' => 'string',
            'description' => 'string'
        ];
    }

    public static function getIdRules()
    {
        return [
            'id' => 'required|regex:/(^[0-9]+$)+/',
        ];
    }

    public static function getVoucherCodeRules()
    {
        return [
            'total' => 'required|integer|min:1',
        ];
    }

    public static function getRedeemRules()
    {
        return [
            'user_id' => 'required|regex:/(^[0-9]+$)+/',
            'platform' => 'required|string|in:web,cms,mobile',
            'code' => 'required|string'
        ];
    }

    public static function getMessages()
    {
        return [
            'query.string' => 'The search query can only contain numbers or alphabets.',
            'sort.in' => 'The sort parameter is invalid.',
            'limit.regex' => 'Pagination limit must be an integer.',
            'order.in' => 'order can only be ASC or DESC',
            'offset.regex' => 'Pagination offset must be an integer.',
            'type.required' => 'The Voucher Type is required',
            'type.string' => 'The Voucher Type can only be a string',
            'type.in' => 'The Voucher Type is invalid',
            'status.required' => 'The Voucher Status is required',
            'status.string' => 'The Voucher Status can only be a string',
            'status.in' => 'The Voucher Status is invalid',
            'category.required' => 'The Category Status is required',
            'category.string' => 'The Category Status can only be a string',
            'category.in' => 'The Category Status is invalid',
            'title.required' => 'The Voucher Title is required',
            'title.string' => 'The Voucher Title can only be a string',
            'title.in' => 'The Voucher Title is invalid',
            'location.required' => 'The Voucher Location is required',
            'location.string' => 'The Voucher Location can only be a string',
            'description.required' => 'The Voucher Description is required',
            'description.string' => 'The Voucher Description can only be a string',
            'duration.required' => 'The Voucher Duration is required',
            'duration.regex' => 'The Voucher Duration can only be a number',
            'period.required' => 'The Voucher Period is required',
            'period.string' => 'The Voucher Period can only be a string',
            'period.in' => 'The Voucher Period is invalid',
            'valid_from.date_format' => 'Provide the Voucher valid from date',
            'valid_to.date_format' => 'Provide the Voucher valid to date',
            'is_limited.required' => 'The Voucher is-limited is required',
            'is_limited.boolean' => 'The Voucher is-limited must be boolean',
            'id.required' => 'The Voucher Id is required',
            'id.regex' => 'The Voucher Id can only be an integer',
            'id.exists' => 'The specified Voucher Id does not exist',
            'user_id.required' => 'The User Id is required',
            'user_id.regex' => 'The User Id can only be an integer',
            'user_id.exists' => 'The specified User Id does not exist',
            'platform.required' => 'The Platform is required',
            'platform.string' => 'The Platform can only be a string',
            'platform.in' => 'The Platform is invalid',
            'code.required' => 'The Code is required',
            'code.string' => 'The Code can only be a string',
            'brand.required' => 'The Brand is required',
            'brand.string' => 'The Brand must be a string',
            'total.required' => 'The Total is required',
            'total.regex' => 'The Total must be an integer',
            'total.min' => 'The Total must be positive',
            'voucher_job_id.required' => 'The Voucher job id is required',
            'voucher_job_id.regex' => 'The Voucher job id must be integer'
        ];
    }
}