<?php

return array(
    'credentials' => array(
        'key' => getenv('AWS_CREDENTIAL_KEY'),
        'secret' => getenv('AWS_CREDENTIAL_SECRET')
    ),
    'region' => 'eu-west-1',
    'version' => 'latest'
);