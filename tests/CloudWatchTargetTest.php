<?php

namespace msbit\log;

use Aws\CloudWatchLogs\CloudWatchLogsClient;

use yii\base\InvalidConfigException;
use yii\log\Logger;

final class CloudWatchTargetTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructionRequiresGroup(): void
    {
        $this->expectException(InvalidConfigException::class);

        new CloudWatchTarget(
            $this->mockClient(),
            [
                'stream' => 'L9UHlBihlRKn42wQMRyqS',
            ],
        );
    }

    public function testConstructionRequiresStream(): void
    {
        $this->expectException(InvalidConfigException::class);

        new CloudWatchTarget(
            $this->mockClient(),
            [
                'group' => 'e6s268DLMErwN-stPIgVl',
            ],
        );
    }

    public function testExportWithoutMessagesDoesNothing(): void
    {
        $target = new CloudWatchTarget(
            $client = $this->mockClient(),
            [
                'group' => 'e6s268DLMErwN-stPIgVl',
                'stream' => 'L9UHlBihlRKn42wQMRyqS',
            ],
        );

        $client->expects($this->never())->method('putLogEvents');

        $target->export();
    }

    public function testExportFormatsMessage(): void
    {
        $target = new CloudWatchTarget(
            $client = $this->mockClient(),
            [
                'group' => 'e6s268DLMErwN-stPIgVl',
                'stream' => 'L9UHlBihlRKn42wQMRyqS',
            ],
        );

        $client->expects($this->once())->method('putLogEvents')
            ->with(
                [
                    'logEvents' => [
                      [
                        'message' => '2023-11-02 08:08:29 [info][E7f1gyCjNG8hNRJ041GS7] 7ImnPfF9vib01y4LbRqK-',
                        'timestamp' => 1698912509915,
                      ],
                    ],
                    'logGroupName' => 'e6s268DLMErwN-stPIgVl',
                    'logStreamName' => 'L9UHlBihlRKn42wQMRyqS',
                ],
            );

        $target->collect(
            [
                [
                    '7ImnPfF9vib01y4LbRqK-',
                    Logger::LEVEL_INFO,
                    'E7f1gyCjNG8hNRJ041GS7',
                    1698912509.9155,
                    [],
                    0,
                ],
            ],
            false,
        );

        $target->export();
    }

    public function testExportSortsMessages(): void
    {
        $target = new CloudWatchTarget(
            $client = $this->mockClient(),
            [
                'group' => 'e6s268DLMErwN-stPIgVl',
                'stream' => 'L9UHlBihlRKn42wQMRyqS',
            ],
        );

        $client->expects($this->once())->method('putLogEvents')
            ->with(
                [
                    'logEvents' => [
                      [
                        'message' => '2023-11-02 08:08:29 [warning][E7f1gyCjNG8hNRJ041GS7] 7ImnPfF9vib01y4LbRqK-',
                        'timestamp' => 1698912509915,
                      ],
                      [
                        'message' => '2023-11-02 08:17:38 [error][6V4vYiIiXrS50DG4B6fvj] J3uGcJFqdjLvc3a5GgXg5',
                        'timestamp' => 1698913058792,
                      ],
                      [
                        'message' => '2023-11-02 08:25:30 [info][91K97HayyEsee8c0iTBxO] EIgIagEbrjFHucZA65sEz',
                        'timestamp' => 1698913530398,
                      ],
                    ],
                    'logGroupName' => 'e6s268DLMErwN-stPIgVl',
                    'logStreamName' => 'L9UHlBihlRKn42wQMRyqS',
                ],
            );

        $target->collect(
            [
                [
                    'J3uGcJFqdjLvc3a5GgXg5',
                    Logger::LEVEL_ERROR,
                    '6V4vYiIiXrS50DG4B6fvj',
                    1698913058.7921,
                    [],
                    0,
                ],
                [
                    '7ImnPfF9vib01y4LbRqK-',
                    Logger::LEVEL_WARNING,
                    'E7f1gyCjNG8hNRJ041GS7',
                    1698912509.9155,
                    [],
                    0,
                ],
                [
                    'EIgIagEbrjFHucZA65sEz',
                    Logger::LEVEL_INFO,
                    '91K97HayyEsee8c0iTBxO',
                    1698913530.3989,
                    [],
                    0,
                ],
            ],
            false,
        );

        $target->export();
    }

    private function mockClient(): CloudWatchLogsClient
    {
        return $this->getMockBuilder(CloudWatchLogsClient::class)
            ->addMethods(
                [
                    'putLogEvents',
                ],
            )
            ->disableOriginalConstructor()
            ->getMock();
    }
}
