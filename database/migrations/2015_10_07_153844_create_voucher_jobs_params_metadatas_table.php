<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVoucherJobsParamsMetadatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('voucher_jobs_params_metadata', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collate = 'utf8_unicode_ci';

            $table->increments('id')->unsigned();
            $table->integer('voucher_job_id')->unsigned()->index('voucher_job_id')->nullable();
            $table->foreign('voucher_job_id')->references('id')->on('voucher_jobs');
            $table->string('key', 30)->index('voucher_jobs_params_metadata_key');
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('voucher_jobs_params_metadata');
    }
}
