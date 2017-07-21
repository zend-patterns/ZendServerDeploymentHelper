[![Build Status](https://travis-ci.org/zend-patterns/ZendServerDeploymentHelper.svg?branch=master)](https://travis-ci.org/zend-patterns/ZendServerDeploymentHelper)
[![Coverage Status](https://coveralls.io/repos/github/zend-patterns/ZendServerDeploymentHelper/badge.svg?branch=master)](https://coveralls.io/github/zend-patterns/ZendServerDeploymentHelper?branch=master)

# Zend Server Deployment Helper
Zend Server Deployment Helper (DepH) is a Zend Framework 2 based set of classes
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
Enabled logging automatically creates an app-name based log file in 
/usr/local/zend/var/log . It extends basic ZF2 Logging, so methods are 
the same as described in [ZF2 
documentation](http://framework.zend.com/manual/2.0/en/modules/zend.log.overview.html#logging-messages).
For convenience log messages can be outputted additionally to the Zend 
Server UI in case of a deployment error.
Full Verbose can be enabled - every steps which is executed is written 
to log file. Useful in case no debugger is available.

### Deployment
Some basic functionality which is mainly used internally. Retrieves 
the action step and script name.
Deployment can also be terminated manually.

### Params
Zend Server Deployment sets some deployment hook script constant which 
can be accesed as environment variables. Besides user constants can be 
defined. Deph throws an exception if any constant is accessed if 
empty, not available or invaild (e.g. not all constants are 
available in each step of deployment) 

### Path
Retrieves general paths which can be useful during deployment, like 
tmp dir, vhost dir, and application installation dir. Also returns 
whether app is installed as default app.

### SystemCall
So far, only shell commands are executed. Executed command and result 
are logged.

### File
So far only a template mechanism is impolemented. Takes a template 
file, optionally substitues values and write the file to a given path.
DryRun is also available.

### Debugger
Allows to start a debug session if Zend Debugger is enabled (default 
in Zend Server)

### Db
MySQL dumps can be imported easily. At the moment a MySQL client has 
to be installed on the server in order to use this feature. 
DB credentials can be taken automatically from Zend Server user 
constants if set in the following format: DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_HOSTNAME

## Installation 
Directory Zx and deph.php have to placed in the same directory as the 
hook script, so of course they have to be included in the deployment 
package.

## Examples
### Initialization
```
<?php
require_once 'deph.php';
$deph = new DepH();
```

In case of verbose logging initialization should look like this
```
<?php
require_once 'deph.php';
declare(ticks=1);
$deph = new DepH();
```

### Log
```
<?php
// init ...
$log = $deph->get('log');
$log->addGuiOutput();
$log->info('My message');
```

Syntax Highlighting available
```
<?php
// init ...
$log = $deph->startLog();
$log->addGuiOutput();
```

Shortcut:
```
<?php
// init ...
$log = $deph->startGuiLog();
```

Full verbose:
```
<?php
// init ...
$log->fullVerbose();
```

Phpinfo:
```
<?php
// init ...
$log->phpInfo();
```

### Deployment
```
<?php
// init ...
$deployment = $deph->get('Deployment');
// or for Sytax Highlighting
$deployment = $deph->getDeployment();
echo $deployment->getCurrentAction();
echo $deployment->getCurrentActionScript();
if ($deployment->isPreStageAction()) {};
// ...
if ($deployment->isUpdate()) {};
$deployment->terminate('Because we have a good reason...');
```

### Params
```
<?php
// init ...
$params = $deph->get('ZSParams');
// or for Sytax Highlighting
$params = $deph->getParams();
echo $params->getApplicationBaseDir();
echo $params->getCurrentAppVersion();
echo $params->getPhpVersion();
echo $params->getPreviousAppVersion();
echo $params->getPreviousApplicationBaseDir();
echo $params->getRunOnceNode();
echo $params->getWebserverGid();
echo $params->getWebserverType();
echo $params->getWebserverUid();
echo $params->getWebserverVersion();
echo $params->getBaseUrl();
// User Params
echo $params->get('MyParam');
// or 
echo $params->get('ZS_MyParam'); // All env vars are prefixed with 'ZS_'
```

### Path
```
<?php
// init ...
$path = $deph->get('Path');
// or for Sytax Highlighting
$path = $deph->getPath();
echo $path->getVirtualHostFile();
if ($path->isDefaultSite()) {};
echo $path->getDeployTempFolder();
echo $path->getUrlPath();
```

### SystemCall
```
<?php
// init ...
$shell = $deph->get('Shell');
// or for Sytax Highlighting
$shell = $deph->getShell();
echo $shell->exec('ls -la /tmp');
```

### File
```
<?php
// init ...
$template = $deph->get('Template');
// or for Sytax Highlighting
$template = $deph->getTemplate();
$template->write('tpl/test.txt', '/tmp/test.txt', array('abc', 'xyz'), array(123, 456));
$content = $template->dryRun('tpl/test.txt', '/tmp/test2.txt', array('abc', 'xyz'), array(123, 456));
print_r($content);
```

### Debugger
```
<?php
// init ...
$debugger = $deph->get('ZendDebugger');
// or for Sytax Highlighting
$debugger = $deph->getDebugger();
$debugger->start('192.168.0.1');
```
After a debug session has been started, the script terminates and is 
restarted in debug mode.

### Db
```
<?php
// init ...
$db = $deph->get('DB');
// or for Sytax Highlighting
$db = $deph->getDb();
// no credntials are necessary, taken from User Constants 
// DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_HOSTNAME
$db->importCl('tpl/mydatabase.sql', array('#value1#', '#value2#'), array('abc', 'xyz'));
```
Please note that MySQL client has to be installed.
