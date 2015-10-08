<?php

return array(
    'client' => 'sqs', // enable SQS queue logging
    'endpoint_url' => getenv('ANALYTICS_SQS_ENDPOINT'), // Queue name from laravel queue config
    'aws_ec2_instanceid' => (getenv('AWS_EC2_INSTANCE_ID')) ? getenv('AWS_EC2_INSTANCE_ID') : '',
    'aws' => array(
        'credentials' => array(
            'key' => getenv('AWS_CREDENTIAL_KEY'),
            'secret' => getenv('AWS_CREDENTIAL_SECRET')
        ),
        'region' => 'eu-west-1',
        'version' => '2012-11-05'
    )
);