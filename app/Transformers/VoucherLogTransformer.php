<?php namespace Voucher\Transformers;

use League\Fractal\TransformerAbstract;
use Voucher\Models\VoucherLog;

/**
 * Created by PhpStorm.
 * User: tech7
 * Date: 10/8/15
 * Time: 3:07 PM
 */

class VoucherLogTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'voucher'
    ];

    public static function transform(VoucherLog $voucherLog)
    {
        return [
            'id' => (int) $voucherLog->id,
            'voucher_id' => (int) $voucherLog->voucher_id,
            'user_id' => (string) $voucherLog->user_id,
            'action' => (string) $voucherLog->action,
            'platform' => (string) $voucherLog->action,
            'comments' => (string) $voucherLog->platform,
            'created_at' => $voucherLog->created_at,
            'updated_at' => $voucherLog->updated_at,
            '_links' => [
                [
                    'rel' => 'self',
                    'uri' => '/logs/' . $voucherLog->id,
                ],
                [
                    'rel' => 'voucher',
                    'uri' => '/vouchers/' . $voucherLog->user_id,
                ]
            ]
        ];
    }

    public function includeVoucherLogs(VoucherLog $voucherLog)
    {
        $voucherLogs = $voucherLog->voucher();
        return $this->item($voucherLogs, new VoucherLogTransformer());
    }
}