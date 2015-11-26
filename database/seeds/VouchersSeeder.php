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
                    'title' => 'Afrimax Ghana',
                    'location' => 'Nigeria',
                    'description' => 'A voucher',
                    'duration' => 3,
                    'period' => 'day',
                    'valid_from' => '2015-10-08 00:00:00',
                    'valid_to' => '2015-12-30 00:00:00',
                    'is_limited' => true,
                    'limit' => 0,
                    'voucher_job_id' => 1
                ],
                [
                    'id' => 2,
                    'code' => 'bsdf1234',
                    'type' => 'time',
                    'status' => 'claiming',
                    'category' => 'expired',
                    'title' => 'Dealdey Nigeria',
                    'location' => 'Nigeria',
                    'description' => 'A voucher',
                    'duration' => 1,
                    'period' => 'week',
                    'valid_from' => '2015-10-08 00:00:00',
                    'valid_to' => '2015-12-30 00:00:00',
                    'is_limited' => true,
                    'limit' => 0,
                    'voucher_job_id' => 1
                ],
                [
                    'id' => 3,
                    'code' => 'csdf1234',
                    'type' => 'time',
                    'status' => 'claimed',
                    'category' => 'active',
                    'title' => 'iROKO Facebook',
                    'location' => 'Nigeria',
                    'description' => 'A voucher',
                    'duration' => 2,
                    'period' => 'month',
                    'valid_from' => '2015-10-08 00:00:00',
                    'valid_to' => '2015-12-30 00:00:00',
                    'is_limited' => true,
                    'limit' => 0,
                    'voucher_job_id' => 1
                ],
                [
                    'id' => 4,
                    'code' => 'dsdf1234',
                    'type' => 'time',
                    'status' => 'deleted',
                    'category' => 'new_expired',
                    'title' => 'iROKO Film Festival 2014',
                    'location' => 'Nigeria',
                    'description' => 'A voucher',
                    'duration' => 1,
                    'period' => 'year',
                    'valid_from' => '2015-10-08 00:00:00',
                    'valid_to' => '2015-12-30 00:00:00',
                    'is_limited' => false,
                    'limit' => 5,
                    'voucher_job_id' => 2
                ],
                [
                    'id' => 5,
                    'code' => 'esdf1234',
                    'type' => 'time',
                    'status' => 'expired',
                    'category' => 'new',
                    'title' => 'Rancard VAS Nigeria Test',
                    'location' => 'Nigeria',
                    'description' => 'A voucher',
                    'duration' => 4,
                    'period' => 'day',
                    'valid_from' => '2015-10-08 00:00:00',
                    'valid_to' => '2015-12-30 00:00:00',
                    'is_limited' => false,
                    'limit' => 10,
                    'voucher_job_id' => 2
                ],
                [
                    'id' => 6,
                    'code' => 'fsdf1234',
                    'type' => 'time',
                    'status' => 'inactive',
                    'category' => 'expired',
                    'title' => 'Spectranet Nigeria Test',
                    'location' => 'Nigeria',
                    'description' => 'A voucher',
                    'duration' => 1,
                    'period' => 'week',
                    'valid_from' => '2015-10-08 00:00:00',
                    'valid_to' => '2015-12-30 00:00:00',
                    'is_limited' => false,
                    'limit' => 15,
                    'voucher_job_id' => 2
                ]

            ]);
    }
}
