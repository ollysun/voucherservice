## vhost setting


<VirtualHost *:80>

	DocumentRoot "/Applications/MAMP/htdocs/voucher-service/public/"

	ServerName voucher.local

	ServerAlias www.voucher.local

 	SetEnv ENVIRONMENT development

</VirtualHost>


## host file

127.0.0.1 voucher.local


## Add/update below env file

APP_ENV=local

APP_DEBUG=true

APP_KEY=SomeRandomKey!!!


APP_LOCALE=en

APP_FALLBACK_LOCALE=en


DB_CONNECTION=mysql

DB_HOST=localhost

DB_DATABASE=voucher

DB_USERNAME=root

DB_PASSWORD=root


CACHE_DRIVER=memcached

SESSION_DRIVER=memcached

QUEUE_DRIVER=database


AWS_CREDENTIAL_KEY=AKIAIENXO2TVWVY6CBOA

AWS_CREDENTIAL_SECRET=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



AWS_S3_BUCKET=v3-voucher

AWS_S3_BUCKET_FOLDER=dev


AWS_REGION=eu-west-1

AWS_VERSION=2012-11-05


ANALYTICS_SQS_ENDPOINT=https://sqs.eu-west-1.amazonaws.com/695925038353/v3-analytics

INCOMING_SQS_VOUCHER_ENDPOINT=https://sqs.eu-west-1.amazonaws.com/695925038353/voucher-dev

PLANS_API_URL = http://www.plans.local

SUBSCRIPTION_API_URL = http://www.subscription.local


NOTIFICATIONS_SQS_ENDPOINT_HIGH=https://sqs.eu-west-1.amazonaws.com/695925038353/notifications_high
NOTIFICATIONS_SQS_ENDPOINT_NORMAL=https://sqs.eu-west-1.amazonaws.com/695925038353/notifications_normal
NOTIFICATIONS_SQS_ENDPOINT_LOW=https://sqs.eu-west-1.amazonaws.com/695925038353/notifications_low