<?php

// Voucher routes
$app->get('/vouchers', 'VouchersController@index');
$app->get('/vouchers/{user_id}/logs', 'VouchersController@show');
$app->post('/vouchers/bulk', 'VouchersController@bulkCreate');
$app->post('/vouchers', 'VouchersController@create');
$app->put('/vouchers/{voucher_id}', 'VouchersController@update');
$app->post('/vouchers/redeem', 'VouchersController@redeem');

$app->post('/vouchers/issue-vouchers', 'TaskController@issueVouchers');
$app->post('/vouchers/generate-codes', 'TaskController@generateVoucherCodes');

