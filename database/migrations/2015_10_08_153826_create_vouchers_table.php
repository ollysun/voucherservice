<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collate = 'utf8_unicode_ci';

            $table->increments('id')->unsigned();
            $table->string('code')->unique('code');
            $table->enum('type', ['time', 'discount'])->default('time');
            $table->enum('status', ['active', 'claiming', 'claimed', 'deleted', 'expired', 'inactive'])->default('inactive');
            $table->enum('category', ['new', 'expired', 'active', 'new_expired'])->default('new');
            $table->enum('title', [
                'INTERNAL',
                'VODAFONE_GHANA_STAFF_MOBILE',
                'VODAFONE_GHANA_CUSTOMER_MOBILE',
                'VODAFONE_GHANA_STAFF_FIXEDLINE'
            ])->default('INTERNAL');
            $table->string('location')->nullable();
            $table->string('description')->nullable();
            $table->integer('duration');
            $table->enum('period', ['day','week','month','year'])->default('day');
            $table->dateTime('valid_from');
            $table->dateTime('valid_to')->index('valid_to');
            $table->boolean('is_limited')->default(true);
            $table->integer('limit')->default(1);
            $table->integer('voucher_job_id')->unsigned()->index('voucher_job_id')->nullable();
            $table->foreign('voucher_job_id')->references('id')->on('voucher_jobs');
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
        Schema::drop('vouchers');
    }
}
