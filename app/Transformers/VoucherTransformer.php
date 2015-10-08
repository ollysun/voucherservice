<?php namespace Voucher\Transformers;

use League\Fractal\TransformerAbstract;
use Voucher\Models\Voucher;

/**
 * Created by PhpStorm.
 * User: tech7
 * Date: 10/8/15
 * Time: 3:06 PM
 */

class VoucherTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'voucherLogs'
    ];

    public static function transform(Voucher $voucher)
    {
        return [
            'id' => (int) $voucher->id,
            'code'  => (string) $voucher->code,
            'type'  => (string) $voucher->type,
            'status'  =>  (string) $voucher->status,
            'category' => (string) $voucher->category,
            'title' => (string) $voucher->title,
            'location' =>  (string) $voucher->location,
            'description' => (string) $voucher->description,
            'duration' => (int) $voucher->duration,
            'period' => (string) $voucher->period,
            'valid_from' => $voucher->valid_from,
            'valid_to' => $voucher->valid_to,
            'is_limited' => (boolean) $voucher->is_limited,
            'limit' => (int) $voucher->limit,
            'created_at' => $voucher->created_at,
            'updated_at' => $voucher->updated_at,
            '_links' => [
                [
                    'rel' => 'self',
                    'uri' => '/vouchers/' . $voucher->id
                ],
                [
                    'rel' => 'voucherLogs',
                    'uri' => '/vouchers/' . $voucher->id . '/logs'
                ]
            ]
        ];
    }

    public function includeVoucherLogs(Voucher $voucher)
    {
        $voucherLogs = $voucher->voucherLog();
        return $this->collection($voucherLogs, new VoucherLogTransformer());
    }
}