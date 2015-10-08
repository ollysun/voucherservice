<?php

use Illuminate\Database\Seeder;

class VoucherLogsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('voucher_logs')
            ->insert([
                [
                    'voucher_id' => 1,
                    'user_id' => 500,
                    'action' => 'success',
                    'platform' => 'web',
                    'comments' => 'just a test comment',
                ],
                [
                    'voucher_id' => 2,
                    'user_id' => 501,
                    'action' => 'attempt',
                    'platform' => 'cms',
                    'comments' => 'just a test comment',
                ],
                [
                    'voucher_id' => 3,
                    'user_id' => 500,
                    'action' => 'success',
                    'platform' => 'mobile',
                    'comments' => 'just a test comment',
                ],
                [
                    'voucher_id' => 4,
                    'user_id' => 501,
                    'action' => 'attempt',
                    'platform' => 'web',
                    'comments' => 'just a test comment',
                ],
                [
                    'voucher_id' => 5,
                    'user_id' => 500,
                    'action' => 'success',
                    'platform' => 'cms',
                    'comments' => 'just a test comment',
                ],
                [
                    'voucher_id' => 6,
                    'user_id' => 501,
                    'action' => 'attempt',
                    'platform' => 'mobile',
                    'comments' => 'just a test comment',
                ]
            ]);
    }
}
