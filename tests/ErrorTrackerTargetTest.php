<?php


declare(strict_types = 1);

namespace ErrorTracker\Yii2\Tests;

use ErrorTracker\Client;

use Yii;

/**
 * @package   error-tracker/yii2-log-target
 * @author    Ade Attwood <ade@practically.io>
 * @copyright 2019 Practically.io
 */
class ErrorTrackerTargetTest extends BaseTestCase
{

    public function testAppKeyMustBeSet(): void
    {
        try {
            $this->mockWebApp();
            $this->fail('Exception must be thrown you dont set an application key');
        } catch (\Exception $e) {
            $this->assertEquals($e->getMessage(), 'app_key must be set');

            return;
        }

        $this->fail('An LogRuntimeException was not thrown');
    }

    public function testBasicLogging(): void
    {
        $http = $this->mockHttp()->getMock();
        $http->expects($this->once())
            ->method('post')
            ->willReturn(null)
            ->with(
                '/report',
                [
                    'app_key' => 'YII_TEST_APP_KEY',
                    'description' => null,
                    'file' => null,
                    'ip' => null,
                    'line_number' => null,
                    'name' => 'application',
                    'reference' => '-',
                    'text' => 'this is an error',
                    'type' => 1,
                    'url' => 'my/test/request',
                    'user_agent' => null,
                ]
            );

        $this->mockWebApp(new Client('YII_TEST_APP_KEY', $http));
        $logger = Yii::$app->log->targets['error-tracker'];
        $logger->messages = [
            [
                'this is an error',
                1,
                'application',
                1565374077.012531,
                [],
                5079424,
            ],
        ];

        $logger->export();
    }

    public function testReportingAnArray(): void
    {
        $http = $this->mockHttp()->getMock();
        $http->expects($this->once())
            ->method('post')
            ->willReturn(null)
            ->with(
                '/report',
                [
                    'app_key' => 'YII_TEST_APP_KEY',
                    'description' => null,
                    'file' => null,
                    'ip' => null,
                    'line_number' => null,
                    'name' => 'application',
                    'reference' => '-',
                    'text' => "[\n    0 => 'key',\n    1 => 1,\n    'value' => 2,\n]",
                    'type' => 1,
                    'url' => 'my/test/request',
                    'user_agent' => null,
                ]
            );

        $this->mockWebApp(new Client('YII_TEST_APP_KEY', $http));
        $logger = Yii::$app->log->targets['error-tracker'];
        $logger->messages = [
            [
                ['key', 1, 'value' => 2],
                1,
                'application',
                1565374077.012531,
                [],
                5079424,
            ],
        ];

        $logger->export();
    }

    public function testReportingAnException(): void
    {
        $exception = new \Exception('This is an exception');

        $http = $this->mockHttp()->getMock();
        $http->expects($this->once())
            ->method('post')
            ->willReturn(null)
            ->with(
                '/report',
                [
                    'app_key' => 'YII_TEST_APP_KEY',
                    'description' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'ip' => null,
                    'line_number' => $exception->getLine(),
                    'name' => 'application',
                    'reference' => '-',
                    'text' => (string)$exception,
                    'type' => 1,
                    'url' => 'my/test/request',
                    'user_agent' => null,
                ]
            );

        $this->mockWebApp(new Client('YII_TEST_APP_KEY', $http));
        $logger = Yii::$app->log->targets['error-tracker'];
        $logger->messages = [
            [
                $exception,
                1,
                'application',
                1565374077.012531,
                [],
                5079424,
            ],
        ];

        $logger->export();
    }
}
