<?php

use Illuminate\Database\Seeder;

class VoucherJobsParamsMetadataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('voucher_jobs_params_metadata')
            ->insert([
                [
                    'id' => 1,
                    'voucher_job_id' => 1,
                    'key' => 'status',
                    'value' => 'active',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
                [
                    'id' => 2,
                    'voucher_job_id' => 1,
                    'key' => 'category',
                    'value' => 'new',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
                [
                    'id' => 3,
                    'voucher_job_id' => 1,
                    'key' => 'title',
                    'value' => 'INTERNAL',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
                [
                    'id' => 4,
                    'voucher_job_id' => 1,
                    'key' => 'location',
                    'value' => 'tetete',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
                [
                    'id' => 5,
                    'voucher_job_id' => 1,
                    'key' => 'description',
                    'value' => 'fhfsd',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
                [
                    'id' => 6,
                    'voucher_job_id' => 1,
                    'key' => 'duration',
                    'value' => '1',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
                [
                    'id' => 7,
                    'voucher_job_id' => 1,
                    'key' => 'period',
                    'value' => 'month',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
                [
                    'id' => 8,
                    'voucher_job_id' => 1,
                    'key' => 'valid_from',
                    'value' => '2015-10-08 00:00:00',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
                [
                    'id' => 9,
                    'voucher_job_id' => 1,
                    'key' => 'valid_to',
                    'value' => '2015-11-08 00:00:00',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
                [
                    'id' => 10,
                    'voucher_job_id' => 1,
                    'key' => 'is_limited',
                    'value' => '0',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
                [
                    'id' => 11,
                    'voucher_job_id' => 1,
                    'key' => 'limit',
                    'value' => '3',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
                [
                    'id' => 12,
                    'voucher_job_id' => 1,
                    'key' => 'brand',
                    'value' => 'IN',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
                [
                    'id' => 13,
                    'voucher_job_id' => 1,
                    'key' => 'total',
                    'value' => '30000',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
                [
                    'id' => 14,
                    'voucher_job_id' => null,
                    'key' => '',
                    'value' => null,
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
                [
                    'id' => 15,
                    'voucher_job_id' => 2,
                    'key' => 'status',
                    'value' => 'active',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
                [
                    'id' => 16,
                    'voucher_job_id' => 2,
                    'key' => 'category',
                    'value' => 'new',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
                [
                    'id' => 17,
                    'voucher_job_id' => 2,
                    'key' => 'title',
                    'value' => 'INTERNAL',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
                [
                    'id' => 18,
                    'voucher_job_id' => 2,
                    'key' => 'location',
                    'value' => 'Lagos',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
                [
                    'id' => 19,
                    'voucher_job_id' => 2,
                    'key' => 'description',
                    'value' => 'fhfsd',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
                [
                    'id' => 20,
                    'voucher_job_id' => 2,
                    'key' => 'duration',
                    'value' => '1',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
                [
                    'id' => 21,
                    'voucher_job_id' => 2,
                    'key' => 'period',
                    'value' => 'month',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
                [
                    'id' => 22,
                    'voucher_job_id' => 2,
                    'key' => 'valid_from',
                    'value' => '2015-10-08 00:00:00',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
                [
                    'id' => 23,
                    'voucher_job_id' => 2,
                    'key' => 'valid_to',
                    'value' => '2015-11-08 00:00:00',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
                [
                    'id' => 24,
                    'voucher_job_id' => 2,
                    'key' => 'is_limited',
                    'value' => '0',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
                [
                    'id' => 25,
                    'voucher_job_id' => 2,
                    'key' => 'limit',
                    'value' => '10',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
                [
                    'id' => 26,
                    'voucher_job_id' => 2,
                    'key' => 'brand',
                    'value' => 'IN',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
                [
                    'id' => 27,
                    'voucher_job_id' => 2,
                    'key' => 'total',
                    'value' => '20',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ]
            ]);
    }
}