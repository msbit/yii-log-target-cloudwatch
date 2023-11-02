# Yii2 log target for CloudWatch

A simple Yii2 logging target implementation for submitting log messages as
events to AWS CloudWatch.

Requires the existence of a CloudWatch group and stream (this will not attempt to
create either).

## Manual instantiation

```php
use Aws\CloudWatchLogs\CloudWatchLogsClient;

use msbit\log\CloudWatchTarget;

Yii::$container->setSingleton(CloudWatchLogsClient::class, new CloudWatchLogsClient([
    'region' => 'ap-southeast-2',
    'version' => '2014-03-28',
]));

$target = Yii::createObject([
    'class' => CloudwatchTarget::class,
    'group' => 'log-group-name',
    'stream' => 'log-stream-name',
]);
```
