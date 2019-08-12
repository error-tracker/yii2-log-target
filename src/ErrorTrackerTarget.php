<?php

namespace ErrorTracker\Yii2;

use ErrorTracker\Client;

use Yii;
use yii\helpers\VarDumper;
use yii\log\LogRuntimeException;
use yii\log\Logger;
use yii\web\Request;

/**
 * Error Tracker log target
 *
 * @package   error-tracker/yii2-log-target
 * @author    Ade Attwood <ade@practically.io>
 * @copyright 2019 Practically.io
 *
 * @property Client $client
 */
class ErrorTrackerTarget extends \yii\log\Target
{

    /**
     * The application key for linking the error
     *
     * @var string
     */
    public $app_key = null;

    /**
     * The URL of the error tracker application
     *
     * @var string
     */
    public $postUrl = '';

    /**
     * The internal error tracker client to reporting the errors
     *
     * @var Client|null
     */
    private $client = null;

    /**
     * Initializes the log target
     *
     * @return void
     */
    public function init(): void
    {
        parent::init();

        if ($this->client === null && $this->app_key === null) {
            throw new LogRuntimeException('app_key must be set');
        }
    }

    /**
     * Gets the internal instance of the error tracker client. If it is not set
     * it will set up the default client to interact with the error tracker API
     *
     * @return Client
     */
    public function getClient(): Client
    {

        if ($this->client === null) {
            $this->client = new Client($this->app_key);
        }

        return $this->client;
    }

    /**
     * Set the internal client
     *
     * @see self::setClient()
     *
     * @return void
     */
    public function setClient(?Client $client): void
    {
        $this->client = $client;
    }

    /**
     * Exports the error and send it so error logger
     *
     * @return void
     */
    public function export(): void
    {
        foreach ($this->messages as $message) {
            list($text, $level, $category, $timestamp) = $message;


            if ($level > $this->getLevels()) {
                continue;
            }

            $description = null;
            $file = null;
            $line = null;

            if (!is_string($text)) {
                if ($text instanceof \Throwable || $text instanceof \Exception) {
                    $description = $text->getMessage();
                    $file = $text->getFile();
                    $line = $text->getLine();

                    $text = (string) $text;
                } else {
                    $text = VarDumper::export($text);
                }
            }



            $user = Yii::$app->has('user', true) ? Yii::$app->get('user') : null;
            if ($user && ($identity = $user->getIdentity(false))) {
                $userID = $identity->getId();
            } else {
                $userID = '-';
            }

            $session = Yii::$app->has('session', true) ? Yii::$app->get('session') : null;
            $sessionID = $session && $session->getIsActive() ? $session->getId() : '-';

            $request = Yii::$app->getRequest();

            $this->getClient()->report([
                'type' => $level === Logger::LEVEL_ERROR ? 1 : 2,
                'name' => $category,
                'text' => $text,
                'description' => $description,
                'file' => $file,
                'line_number' => $line,
                'reference' => $sessionID,
                'url' => ($request instanceof Request) ? $request->getAbsoluteUrl() : 'null',
                'user_agent' => $request instanceof Request ? $request->getUserAgent() : 'null',
                'ip' => $request instanceof Request ? $request->getUserIP() : 'null'
            ]);
        }
    }
}
