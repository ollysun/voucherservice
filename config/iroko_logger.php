<?php

return array(

    'enabled' => getenv('SQS_LOGGING'), // enable SQS queue logging
    'allow_default_logging' => getenv('DEFAULT_LOGGING'), // If we want to enable the default logger too
    'endpoint_url' => getenv('LOGGING_SQS_ENDPOINT'), // Queue name from laravel queue config
    'aws' => array(
        'region' => getenv('AWS_DEFAULT_REGION'),
        'version' => getenv('AWS_VERSION')
    )

);