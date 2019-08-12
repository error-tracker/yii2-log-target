# [Error Tracker](https://error-tracker.com) Yii2 Log Target

## Who is this for?

This is for Yii2 developers that need to integrate their applications with
[Error Tracker](https://error-tracker.com). This extension uses the Yii2 log
component to send errors and warnings direct to Error Tracker.

## Installation

Install this package with composer.

```bash
composer require error-tracker/yii2-log-target
```

## Configuration

To configure the [log
target](https://www.yiiframework.com/doc/guide/2.0/en/runtime-logging#log-targets)
in your application, add the below config. Whenever there is a
server side error this will be added to the file log as it normally would. Additionally this will be
sent to the error tracker dashboard, for easy searches, alerts and other handy tools.

```php
'log' => [
    'targets' => [
        [
            'class' => 'yii\log\FileTarget',
            'levels' => ['error', 'warning'],
        ],
        [
            'class' => 'ErrorTracker\Yii2\ErrorTrackerTarget',
            'levels' => ['error', 'warning'],
            'app_key' => 'YOUR_APP_KEY'
        ],
    ],
],
```

When an error is reported onto Error Tracker, it will be saved with a
`Reference`. This is your user's session id, and is also the id you can use to
trace the error in your file log where you can find more information about that error
error.

## Using Yii2's log functions

You can use Yii2's [error and
warning](https://www.yiiframework.com/doc/guide/2.0/en/runtime-logging#log-messages)
methods to log errors to Error Tracker without throwing exceptions. This will
still be logged in the same way, and will additionally and automatically sent if you have the log
target configured. The below code will send an error without throwing an
exception.

```php
try {
    $this->willBreake();
} catch (\Exception $e) {
    Yii::error($e->getMessage(), $e->getName());
}
```

## Disabling

Disable your log target by using the [method documented by
Yii2](https://www.yiiframework.com/doc/guide/2.0/en/runtime-logging#toggling-log-targets).
Optionally disable the target by setting the `enabled` property in the
configuration. The below config only enables the logger if your application
is in a production environment.

```php
[
    'class' => 'ErrorTracker\Yii2\ErrorTrackerTarget',
    'levels' => ['error', 'warning'],
    'app_key' => 'YOUR_APP_KEY',
    'enabled' => YII_ENV_PROD,
],
```

## Contributing

### Getting set up

Clone the repo and run `composer install`.
Then start hacking!

### Testing

All new features of bug fixes must be tested. Testing is with phpunit and can
be run with the following command.

```bash
composer run-script test
```

### Coding Standards

This library uses psr2 coding standards and `squizlabs/php_codesniffer` for
linting. There is a composer script for this:

```bash
composer run-script lint
```

### Pull Requests

Before creating a pull request with your changes, the pre-commit script must
pass. That can be run as follows:

```bash
composer run-script pre-commit
```

## Credits

This package is created and maintained by [Practically.io](https://practically.io/)
