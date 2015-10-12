<?php

use Illuminate\Database\Seeder;

class VoucherJobsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('voucher_jobs')
            ->insert([
                [
                    'id' => 1,
                    'status' => 'new',
                    'comments' => 'first',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ],
                [
                    'id' => 2,
                    'status' => 'new',
                    'comments' => 'second',
                    'created_at' => '0000-00-00 00:00:00',
                    'updated_at' => '0000-00-00 00:00:00'
                ]
            ]);
    }
}
