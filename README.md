[![Build Status](https://travis-ci.org/zend-patterns/ZendServerDeploymentHelper.svg?branch=master)](https://travis-ci.org/zend-patterns/ZendServerDeploymentHelper)
[![Coverage Status](https://coveralls.io/repos/github/zend-patterns/ZendServerDeploymentHelper/badge.svg?branch=master)](https://coveralls.io/github/zend-patterns/ZendServerDeploymentHelper?branch=master)

# Zend Server Deployment Helper
Zend Server Deployment Helper (DepH) is a Zend Framework based set of classes
which supports in creating reliable hook scripts of Zend Server 
Deployment feature.

More information about Zend Server Deployment can be found on
[zend.com](http://www.zend.com/en/products/server/features) or on 
[YouTube](http://www.youtube.com/embed/gA7VhHd_4Z8).

Full documentation is avaliable 
[here](http://files.zend.com/help/Zend-Server-6/zend-server.htm#zend_deployment.htm).

Deployment allows to customize the process by hook script which are 
implemented in PHP. The following is a list of examples for operations 
that may be defined in each hook script:

* Pre-stage - Validate and applying user customized parameter values, verify the existence of generic prerequisites, etc.
* Post-stage - Create a new database schema, modify a file or directory permissions on staged source files, etc.
* Pre-activate - Upgrade an existing database schema, etc.
* Post-activate - Remove a temporary banner ("Down for Maintenance"), reset a cache, etc.
* Pre-rollback - Return configuration files or the database to their previous version, etc.
* Post-rollback - Take the site out of maintenance mode, return the load balancer settings, etc.
* Pre-deactivate - Put up a banner ("Down for Maintenance") for the previous version, etc.
* Post-deactivate - Modify external configuration files, etc.
* Pre-unstage - Back up specific applications files such as audit logs and other data files, etc.
* Post-unstage - Clean up external files referencing the application (which are no longer needed), etc.

When implementing deployment packages one quickly notice that some 
basic functionality like logging or DB import would be helpful.

The ZPK package of the Zend Server Deployment Helper can be found in the release section. The ZPK file can be deployed as a library prior to the app deployment on a Zend Server system.

## Features
### Log
Logging is implemented using the PSR-3 standard.
A `Psr\Log\LoggerInterface` instance is automatically injected to all classes implementing `Psr\Log\LoggerAwareInterface`.
You can change the implementation by override the configuration for service `Psr\Log\LoggerInterface`.
If service `Psr\Log\LoggerInterface` is not configured a `Psr\Log\NullLogger` is used by default instead.

To enable file level logging you can use any component which provides `psr/log-implementation`.
We recommend to use [monolog](https://github.com/Seldaek/monolog/) for logging.

This package provides factories for both of them. `ZendServer\DepH\Log\Factory\MonologFactory` and `ZendServer\DepH\Log\Factory\ZendFactory`.
To enable logging just add `ZendServer\DepH\Log\Factory\MonologFactory` as factory for `\Psr\Log\LoggerInterface`.
```php
<?php
[
    'dependencies' => [
        \Psr\Log\LoggerInterface::class => \ZendServer\DepH\Log\Factory\MonologFactory::class,
    ],
];
```

For convenience log messages can be outputted additionally to the Zend 
Server UI in case of a deployment error. Just add `ZendServer\DepH\Log\Delegator\EnableZSGuiDelegatorFactory` 
as delegator to `Psr\Log\LoggerInterface`. e.g.
```php
<?php
[
    'dependencies' => [
        'delegators' => [
            \Psr\Log\LoggerInterface::class => [
                \ZendServer\DepH\Log\Delegator\EnableZSGuiDelegatorFactory::class
            ]
        ]
    ],
];
```

### Deployment
The service `Zend\DepH\Deployment\Deployment` provides all information during deployment. You can use it by calling `$container->get('Deployment')`.

### Executing shell commands
To execute shell commands we recommend to use [symfony/process](https://github.com/symfony/process).

### File
So far only a template mechanism is implemented. Takes a template 
file, optionally substitues values and write the file to a given path.
You can use it by calling `$container->get('Template')`.

### Debugger
Allows to start a debug session if Zend Debugger is enabled (default 
in Zend Server)
You can use it by calling `$container->get('ZendDebugger')`.

## Installation
The recommended way to install this package, is through composer.

`composer require zend-pattern/ZendServerDeploymentHelper`

## Examples
### Initialization
```php
<?php
declare(strict_types=1);

require_once 'vendor/autoload.php';
```

In case you want to use the service container
```php
<?php
declare(strict_types=1);

require_once 'vendor/autoload.php';

/** @var \Psr\Container\ContainerInterface $container */
$container = include 'config/container.php';
```
`config/container.php` should look like this.
```php
<?php
declare(strict_types=1);
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;
use Zend\ConfigAggregator\ConfigAggregator;

$aggregator = new ConfigAggregator(
    [
        \ZendServer\DepH\ConfigProvider::class,
    ]
);
$config = new ArrayObject($aggregator->getMergedConfig());
// Build container
$container = new ServiceManager();
$container->setService('config', $config);
(new Config($config['dependencies']))->configureServiceManager($container);
```

### Using the pipeline
```php
<?php
declare(strict_types=1);

require_once 'vendor/autoload.php';

$pipeline = new \ZendServer\DepH\Pipeline\Pipeline();
$pipeline->pipe(function($payload, \ZendServer\DepH\Pipeline\DelegateInterface $delegate) {
    // do things, e.g. clear cache
    return $delegate->process($payload);
});
$pipeline->pipe('Foo\Bar\Middleware');
$pipeline->pipe(function($payload, \ZendServer\DepH\Pipeline\DelegateInterface $delegate) {
    // return result (status is 0 by default which indicates success in command line context)
    return new \ZendServer\DepH\Pipeline\Result();
});

/** @var \ZendServer\DepH\Pipeline\ResultInterface $result */
$result = $pipeline->process($payload);
// optionally you can use \ZendServer\DepH\Pipeline\Payload as payload

exit($result->getStatusCode());
```

In case you want to be able to resolve the middleware automatically from the container
```php
<?php
declare(strict_types=1);

require_once 'vendor/autoload.php';

/** @var \Psr\Container\ContainerInterface $container */
$container = include 'config/container.php';

$resolver = new \ZendServer\DepH\Pipeline\MiddlewareContainerResolver($container);

$pipeline = new \ZendServer\DepH\Pipeline\Pipeline($resolver);
$pipeline->pipe(function($payload, \ZendServer\DepH\Pipeline\DelegateInterface $delegate) {
    // do things, e.g. clear cache
    return $delegate->process($payload);
});
$pipeline->pipe('Foo\Bar\Middleware');
$pipeline->pipe(function($payload, \ZendServer\DepH\Pipeline\DelegateInterface $delegate) {
    // return result (status is 0 by default which indicates success in command line context)
    return new \ZendServer\DepH\Pipeline\Result();
});

/** @var \ZendServer\DepH\Pipeline\ResultInterface $result */
$result = $pipeline->process($payload);
// optionally you can use \ZendServer\DepH\Pipeline\Payload as payload

exit($result->getStatusCode());
```

### Log
```php
<?php
declare(strict_types=1);

// init ...

/* @var $log \Psr\Log\LoggerInterface */
$log = $container->get('Log');
$log->info('My message');
```

### Deployment
```php
<?php
declare(strict_types=1);

// init ...

/* @var $deployment \ZendServer\DepH\Deployment\Deployment */
$deployment = $container->get('Deployment');
echo $deployment->getCurrentAction();
echo $deployment->getCurrentActionScript();
if ($deployment->isPreStageAction()) {};
// ...
if ($deployment->isUpdate()) {};
$deployment->terminate('Because we have a good reason...');
```

### Params
```php
<?php
declare(strict_types=1);

// init ...

/* @var $deployment \ZendServer\DepH\Deployment\Deployment */
$deployment = $container->get('Deployment');

echo $deployment->getApplicationBaseDir();
echo $deployment->getCurrentAppVersion();
echo $deployment->getPhpVersion();
echo $deployment->getPreviousAppVersion();
echo $deployment->getPreviousApplicationBaseDir();
echo $deployment->getRunOnceNode();
echo $deployment->getWebserverGid();
echo $deployment->getWebserverType();
echo $deployment->getWebserverUid();
echo $deployment->getWebserverVersion();
echo $deployment->getBaseUrl();
// User Params
echo $deployment->getParam('MyParam');
// or 
echo $deployment->getParam('ZS_MyParam'); // All env vars are prefixed with 'ZS_'
```

### Path
```php
<?php
declare(strict_types=1);

// init ...

/* @var $deployment \ZendServer\DepH\Deployment\Deployment */
$deployment = $container->get('Deployment');

echo $deployment->getVirtualHostFile();
if ($deployment->isDefaultSite()) {};
echo $deployment->getDeploymentTempDir();
echo $deployment->getUrlPath();
```

### SystemCall
See https://symfony.com/doc/current/components/process.html

### File
```php
<?php
declare(strict_types=1);

// init ...
/* @var $template \ZendServer\DepH\File\Template */
$template = $container->get('Template');
$content = $template->write('tpl/test.txt', '/tmp/test.txt', array('abc' => 123, 'xyz' => 456));
print_r($content);
```

### Debugger
```php
<?php
declare(strict_types=1);

// init ...
/* @var $debugger \ZendServer\DepH\Debugger\ZendDebugger */
$debugger = $container->get('ZendDebugger');
$debugger->start('192.168.0.1');
```
After a debug session has been started, the script terminates and is 
restarted in debug mode.
