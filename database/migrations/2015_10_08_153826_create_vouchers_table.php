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
            $table->enum('status', ['active', 'claiming', 'claimed', 'deleted', 'expired', 'in_active'])->default('in_active');
            $table->enum('category', ['new', 'expired', 'active', 'new_expired'])->default('new');
            $table->enum('title', [
                'INTERNAL',
                'VODAFONE_GHANA_STAFF_MOBILE',
                'VODAFONE_GHANA_CUSTOMER_MOBILE',
                'VODAFONE_GHANA_STAFF_FIXEDLINE'
            ])->default('INTERNAL');
            $table->string('location');
            $table->string('description');
            $table->integer('duration');
            $table->enum('period', ['day','week','month','year'])->default('day');
            $table->dateTime('valid_from');
            $table->dateTime('valid_to')->index('valid_to');
            $table->boolean('is_limited');
            $table->integer('limit');
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
