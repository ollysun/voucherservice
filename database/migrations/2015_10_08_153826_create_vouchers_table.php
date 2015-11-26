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
                'Afrimax Ghana',
                'Airtel Ghana',
                'iROKO Film Festival 2014',
                'iROKO BD Test',
                'Brandless',
                'iROKO Test',
                'iROKO CS',
                'Dealdey Nigeria',
                'Etisalat Nigeria Test',
                'iROKO Facebook',
                'Lowe Staff',
                'MTN Nigeria Staff',
                'New Africa',
                'Rancard VAS Nigeria Test',
                'Rancard VAS Nigeria',
                'Smile',
                'Spectranet Nigeria',
                'Spectranet Nigeria Test',
                'Spectranet Nigeria Staff',
                'Swift',
                'Teledatal Ghana',
                'Teledatal Ghana Test',
                'Tigo Rwanda',
                'Tigo Tanzania Staff',
                'Tigo Rwanda Staff',
                'Tigo Rwanda Test',
                'Tigo Tanzania',
                'Travelstart Nigeria',
                'Uber Nigeria',
                'Vodafone Ghana Fixed',
                'Vodafone Ghana Fixed Test',
                'Vodafone Ghana Mobile',
                'Vodafone Ghana Fixed Staff',
                'Vodafone Ghana Mobile Test',
                'Vodafone Staff',
                'Wish South Africa',
                'iROKO Christmas 2014'
            ])->default('Afrimax Ghana')->index('title');
            $table->string('location')->nullable();
            $table->string('description')->nullable();
            $table->integer('duration')->index('duration');
            $table->enum('period', ['day','week','month','year'])->default('day')->index('period');
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
