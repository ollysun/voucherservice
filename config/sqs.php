<?php

return array(
    'aws_credentials' => array(
        'credentials' => array(
            'key' => env('AWS_CREDENTIAL_KEY', ''),
            'secret' => env('AWS_CREDENTIAL_SECRET', ''),
        ),
        'region' => env('AWS_REGION', 'eu-west-1'),
        'version' => env('AWS_VERSION', '2012-11-05')
    ),

    'incoming_queue' => array(
        'sqs_endpoint_url' => env('INCOMING_SQS_SUBSCRIPTION_ENDPOINT'),
        'max_num_messages' => env('INCOMING_MAX_MESSAGES', 10),
        'sleep_time_when_no_messages' => env('INCOMING_SLEEP_WHEN_EMPTY', 5)
    ),
);