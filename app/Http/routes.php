<?php

// Voucher routes
$app->get('/vouchers', 'VouchersController@index');
$app->post('/vouchers/bulk', 'VouchersController@bulkCreate');
$app->post('/vouchers', 'VouchersController@create');
$app->put('/vouchers/{voucher_id}', 'VouchersController@update');
$app->post('/vouchers/redeem', 'VouchersController@redeem');

$app->post('/vouchers/process', 'TaskController@generateVouchers');

$app->post('/vouchers/generateCodes', 'TaskController@generateVoucherCodes');
