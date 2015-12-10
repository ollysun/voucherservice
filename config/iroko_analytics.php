<?php

return array(
    'client' => 'sqs', // enable SQS queue logging
    'endpoint_url' => getenv('ANALYTICS_SQS_ENDPOINT'), // Queue name from laravel queue config
    'aws_ec2_instanceid' => (getenv('AWS_EC2_INSTANCE_ID')) ? getenv('AWS_EC2_INSTANCE_ID') : '',
    'aws' => array(
        'region' => getenv('AWS_DEFAULT_REGION'),
        'version' => getenv('AWS_VERSION')
    )

);