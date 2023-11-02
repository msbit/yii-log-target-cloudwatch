<?php

namespace msbit\log;

use Aws\CloudWatchLogs\CloudWatchLogsClient;

use yii\base\InvalidConfigException;
use yii\log\Target;

class CloudWatchTarget extends Target
{
    public const TIMESTAMP_INDEX = 3;

    public ?string $group = null;
    public ?string $stream = null;

    private CloudWatchLogsClient $client;

    public function __construct(
        CloudWatchLogsClient $client,
        array $config = []
    ) {
        $this->client = $client;

        parent::__construct($config);
    }

    public function init()
    {
        parent::init();

        if (is_null($this->group)) {
            throw new InvalidConfigException('group must be set');
        }

        if (is_null($this->stream)) {
            throw new InvalidConfigException('stream must be set');
        }
    }

    public function export()
    {
        if (empty($this->messages)) {
            return;
        }

        usort(
            $this->messages,
            fn ($a, $b) => $a[self::TIMESTAMP_INDEX] < $b[self::TIMESTAMP_INDEX] ? -1 : 1,
        );
        $this->client->putLogEvents([
            'logEvents' => array_map([$this, 'formatLogEvent'], $this->messages),
            'logGroupName' => $this->group,
            'logStreamName' => $this->stream,
        ]);
    }

    public function formatLogEvent($message): array
    {
        return [
            'message' => $this->formatMessage($message),
            'timestamp' => intval($message[self::TIMESTAMP_INDEX] * 1000),
        ];
    }
}
