<?php

declare(strict_types = 1);

namespace ErrorTracker\Yii2\Tests;

use ErrorTracker\Client;
use ErrorTracker\Http;

use PHPUnit\Framework\MockObject\MockBuilder;

use yii\web\Application;

/**
 * @package   error-tracker/yii2-log-target
 * @author    Ade Attwood <ade@practically.io>
 * @copyright 2019 Practically.io
 */
class BaseTestCase extends \PHPUnit\Framework\TestCase
{

    /**
     * Creates a mock of the http class
     *
     */
    public function mockHttp(): MockBuilder
    {
        Http::$trackerUrl = 'http:localhost';
        return $this->getMockBuilder('Http')->setMethods(['post']);
    }

    /**
     * Creates the yii2 web application global instance available via `Yii`
     *
     * @return void
     */
    public function mockWebApp(?Client $client = null): void
    {
        $tmpDir = dirname(__DIR__) . '/tmp';
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir);
        }

        if (!is_dir($tmpDir . '/assets')) {
            mkdir($tmpDir . '/assets');
        }

        $_SERVER['REQUEST_URI'] = 'my/test/request';

        new Application([
            'id' => 'moc-app',
            'basePath' => dirname(__DIR__),
            'bootstrap' => ['log'],
            'aliases' => [
                '@bower' => '@vendor/bower-asset',
                '@npm' => '@vendor/npm-asset',
            ],
            'components' => [
                'assetManager' => [
                    'bundles' => false,
                    'basePath' => $tmpDir
                ],
                'request' => [
                    'cookieValidationKey' => 'mock_app_csrf',
                    'scriptFile' => __DIR__ . '/index.php',
                    'scriptUrl' => '/index.php',
                ],
                'urlManager' => [
                    'enablePrettyUrl' => true,
                    'showScriptName' => false,
                    'baseUrl' => 'http://localhost',
                    'hostInfo' => 'http://localhost',
                    'rules' => [],
                ],
                'log' => [
                    'targets' => [
                        'error-tracker' => [
                            'class' => 'ErrorTracker\Yii2\ErrorTrackerTarget',
                            'levels' => ['error', 'warning'],
                            'client' => $client,
                        ],
                    ],
                ],
            ],
        ]);
    }
}
