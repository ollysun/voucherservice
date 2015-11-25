<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateGenerateVoucherStoredProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        DB::unprepared(' CREATE PROCEDURE generate_voucher(
                               IN `status` varchar(20),
                               IN `category` varchar(10),
                               IN `title` varchar(50),
                               IN `location` varchar(50),
                               IN `description` varchar(50),
                               IN `duration` int(10),
                               IN `period` varchar(10),
                               IN `valid_from` varchar(20),
                               IN `valid_to` varchar(20),
                               IN `limit` int(10),
                               IN `brand` varchar(20),
                               IN `total` int(10),
                               IN `voucher_job_id` int(10)
                           )
            BEGIN

              DECLARE i INT DEFAULT 1;
              DECLARE $voucher_code varchar(20);
              DECLARE $voucher_code_id INT DEFAULT 0;
              DECLARE code varchar(20);
              DECLARE code_id INT  DEFAULT 1;

              WHILE (i<= total) DO

                SELECT voucher_code INTO $voucher_code FROM voucher_codes WHERE code_status = "new" limit 1;
                SELECT id INTO $voucher_code_id FROM voucher_codes WHERE code_status = "new" LIMIT 1;
                SELECT $voucher_code_id;

                #SET $vget_code=concat(voucher_code = ",$voucher_code,");
                UPDATE voucher_codes set code_status = "used" WHERE id = $voucher_code_id;

                #select $vget_code;
                SET @vcode=concat(UPPER(brand) ,$voucher_code);

                INSERT INTO vouchers (
                      `type`,
                      `title`,
                      `description`,
                      `status`,
                      `location`,
                      `category`,
                      `code`,
                      `duration`,
                      `period`,
                      `limit`,
                      `valid_from`,
                      `valid_to`,
                      `voucher_job_id`
                )
                VALUES (
                      \'time\',
                      `title`,
                      `description`,
                      `status`,
                      `location`,
                      `category`,
                      @vcode,
                      `duration`,
                      `period`,
                      `limit`,
                      `valid_from`,
                      `valid_to`,
                      `voucher_job_id`
                );

                SET i=i+1;
              END WHILE;
            END;'
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        DB::unprepared('DROP PROCEDURE IF EXISTS generate_voucher');
    }
}
