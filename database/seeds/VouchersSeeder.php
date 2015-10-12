<?php

use Illuminate\Database\Seeder;

class VouchersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('vouchers')
            ->insert([
                [
                    'id' => 1,
                    'code' => 'asdf1234',
                    'type' => 'time',
                    'status' => 'active',
                    'category' => 'new',
                    'title' => 'Internal',
                    'location' => 'Nigeria',
                    'description' => 'A voucher',
                    'duration' => 3,
                    'period' => 'day',
                    'valid_from' => '2015-10-08 00:00:00',
                    'valid_to' => '2015-12-30 00:00:00',
                    'is_limited' => true,
                    'limit' => 0
                ],
                [
                    'id' => 2,
                    'code' => 'bsdf1234',
                    'type' => 'time',
                    'status' => 'claiming',
                    'category' => 'expired',
                    'title' => 'VODAFONE_GHANA_STAFF_MOBILE',
                    'location' => 'Nigeria',
                    'description' => 'A voucher',
                    'duration' => 1,
                    'period' => 'week',
                    'valid_from' => '2015-10-08 00:00:00',
                    'valid_to' => '2015-12-30 00:00:00',
                    'is_limited' => true,
                    'limit' => 0
                ],
                [
                    'id' => 3,
                    'code' => 'csdf1234',
                    'type' => 'time',
                    'status' => 'claimed',
                    'category' => 'active',
                    'title' => 'VODAFONE_GHANA_CUSTOMER_MOBILE',
                    'location' => 'Nigeria',
                    'description' => 'A voucher',
                    'duration' => 2,
                    'period' => 'month',
                    'valid_from' => '2015-10-08 00:00:00',
                    'valid_to' => '2015-12-30 00:00:00',
                    'is_limited' => true,
                    'limit' => 0
                ],
                [
                    'id' => 4,
                    'code' => 'dsdf1234',
                    'type' => 'time',
                    'status' => 'deleted',
                    'category' => 'new_expired',
                    'title' => 'VODAFONE_GHANA_STAFF_FIXEDLINE',
                    'location' => 'Nigeria',
                    'description' => 'A voucher',
                    'duration' => 1,
                    'period' => 'year',
                    'valid_from' => '2015-10-08 00:00:00',
                    'valid_to' => '2015-12-30 00:00:00',
                    'is_limited' => false,
                    'limit' => 5
                ],
                [
                    'id' => 5,
                    'code' => 'esdf1234',
                    'type' => 'time',
                    'status' => 'expired',
                    'category' => 'new',
                    'title' => 'Internal',
                    'location' => 'Nigeria',
                    'description' => 'A voucher',
                    'duration' => 4,
                    'period' => 'day',
                    'valid_from' => '2015-10-08 00:00:00',
                    'valid_to' => '2015-12-30 00:00:00',
                    'is_limited' => false,
                    'limit' => 10
                ],
                [
                    'id' => 6,
                    'code' => 'fsdf1234',
                    'type' => 'time',
                    'status' => 'inactive',
                    'category' => 'expired',
                    'title' => 'VODAFONE_GHANA_STAFF_MOBILE',
                    'location' => 'Nigeria',
                    'description' => 'A voucher',
                    'duration' => 1,
                    'period' => 'week',
                    'valid_from' => '2015-10-08 00:00:00',
                    'valid_to' => '2015-12-30 00:00:00',
                    'is_limited' => false,
                    'limit' => 15
                ]

            ]);
    }
}
