<?php

namespace adeattwood\errortracker;

use Yii;
use yii\log\Logger;
use yii\log\LogRuntimeException;
use yii\web\Request;

/**
 * 
 */
class ErrorLoggerTarget extends \yii\log\Target
{

    /**
     * The application key for linking the error
     * 
     * @var string
     */
    public $app_key = null;

    /**
     * The url of the error logger application
     * 
     * @var string
     */
    public $postUrl = 'https://erorr-tracking.adeattwood.co.uk/report';

    /**
     * Initializes the log target
     * 
     * @return void
     */
    public function init()
    {
        if ($this->app_key === null) {
            throw new LogRuntimeException('app_key must be set');
        }

        return parent::init();
    }

    /**
     * Exports the error and send it so error logger
     * 
     * @return void
     */
    public function export()
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

            $data = [
                'app_key' => $this->app_key,
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
            ];

            $ch = curl_init($this->postUrl);

            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json'
                ],
                CURLOPT_POSTFIELDS => json_encode($data)
            ]);

            $response = curl_exec($ch);
        }
    }

}
