<?php

return array(

    'enabled' => true, // enable SQS queue logging
    'allow_default_logging' => true, // If we want to enable the default logger too
    'endpoint_url' => 'https://sqs.eu-west-1.amazonaws.com/695925038353/v3-test', // Queue name from laravel queue config
    'aws' => array(
      'credentials' => array(
        'key' => 'AKIAIENXO2TVWVY6CBOA',
        'secret' => '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'
      ),
      'region' => 'eu-west-1',
      'version' => '2012-11-05'
    )

);