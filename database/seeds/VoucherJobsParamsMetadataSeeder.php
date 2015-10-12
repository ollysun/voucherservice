<?php
use Illuminate\Database\Seeder;


class VoucherJobsParamsMetadataSeeder extends Seeder {

    public function run()
    {
        DB::table('voucher_jobs_params_metadata')
            ->insert([
                [
                    'id' => 1,
                    'voucher_job_id' => 1,
                    'key' => 'status',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00',
                    'value' => 'active'
                ],
                [
                    'id' => 2,
                    'voucher_job_id' => 1,
                    'key' => 'category',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00',
                    'value' => 'new'
                ],
                [
                    'id' => 3,
                    'voucher_job_id' => 1,
                    'key' => 'title',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00',
                    'value' => 'INTERNAL'
                ],
                [
                    'id' => 4,
                    'voucher_job_id' => 1,
                    'key' => 'location',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00',
                    'value' => 'tetete'
                ],
                [
                    'id' => 5,
                    'voucher_job_id' => 1,
                    'key' => 'description',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00',
                    'value' => 'fhfsd'
                ],
                [
                    'id' => 6,
                    'voucher_job_id' => 1,
                    'key' => 'duration',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00',
                    'value' => '1'
                ],
                [
                    'id' => 7,
                    'voucher_job_id' => 1,
                    'key' => 'period',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00',
                    'value' => 'month'
                ],
                [
                    'id' => 8,
                    'voucher_job_id' => 1,
                    'key' => 'valid_from',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00',
                    'value' => '2015-10-08 00:00:00'
                ],
                [
                    'id' => 9,
                    'voucher_job_id' => 1,
                    'key' => 'valid_to',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00',
                    'value' => '2015-11-08 00:00:00'
                ],
                [
                    'id' => 10,
                    'voucher_job_id' => 1,
                    'key' => 'is_limited',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00',
                    'value' => '0'
                ],
                [
                    'id' => 11,
                    'voucher_job_id' => 1,
                    'key' => 'limit',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00',
                    'value' => '3'
                ],
                [
                    'id' => 12,
                    'voucher_job_id' => 1,
                    'key' => 'brand',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00',
                    'value' => 'IN'
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
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00',
                    'value' => '30000'
                ],
                [
                    'id' => 14,
                    'voucher_job_id' => NULL,
                    'key' => '',
                    'value' => NULL
                ],
                [
                    'id' => 15,
                    'voucher_job_id' => 2,
                    'key' => 'status',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00',
                    'value' => 'active'
                ],
                [
                    'id' => 16,
                    'voucher_job_id' => 2,
                    'key' => 'category',
                    'value' => 'new',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00',
                ],
                [
                    'id' => 17,
                    'voucher_job_id' => 2,
                    'key' => 'title',
                    'value' => 'INTERNAL',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00',
                ],
                [
                    'id' => 18,
                    'voucher_job_id' => 2,
                    'key' => 'location',
                    'value' => 'Lagos',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00',
                ],
                [
                    'id' => 19,
                    'voucher_job_id' => 2,
                    'key' => 'description',
                    'value' => 'fhfsd',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00',
                ],
                [
                    'id' => 20,
                    'voucher_job_id' => 2,
                    'key' => 'duration',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00',
                    'value' => '1'
                ],
                [
                    'id' => 21,
                    'voucher_job_id' => 2,
                    'key' => 'period',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00',
                    'value' => 'month'
                ],
                [
                    'id' => 22,
                    'voucher_job_id' => 2,
                    'key' => 'valid_from',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00',
                    'value' => '2015-10-08 00:00:00'
                ],
                [
                    'id' => 23,
                    'voucher_job_id' => 2,
                    'key' => 'valid_to',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00',
                    'value' => '2015-11-08 00:00:00'
                ],
                [
                    'id' => 24,
                    'voucher_job_id' => 2,
                    'key' => 'is_limited',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00',
                    'value' => '0'
                ],
                [
                    'id' => 25,
                    'voucher_job_id' => 2,
                    'key' => 'limit',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00',
                    'value' => '10'
                ],
                [
                    'id' => 26,
                    'voucher_job_id' => 2,
                    'key' => 'brand',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00',
                    'value' => 'IN'
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

