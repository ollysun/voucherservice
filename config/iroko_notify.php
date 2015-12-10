<?php

return array(
    'client' => 'sqs', // enable SQS queue logging
    'high_priority_endpoint_url' => getenv('NOTIFICATIONS_SQS_ENDPOINT_HIGH'), // Queue name from laravel queue config
    'normal_priority_endpoint_url' => getenv('NOTIFICATIONS_SQS_ENDPOINT_NORMAL'), // Queue name from laravel queue config
    'low_priority_endpoint_url' => getenv('NOTIFICATIONS_SQS_ENDPOINT_LOW'), // Queue name from laravel queue config
    'aws_ec2_instanceid' => (getenv('AWS_EC2_INSTANCE_ID')) ? getenv('AWS_EC2_INSTANCE_ID') : '',
    'aws' => array(
        'region' => getenv('AWS_DEFAULT_REGION'),
        'version' => getenv('AWS_VERSION')
    )
);