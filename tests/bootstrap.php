<?php
$paths = array(
    dirname(__DIR__),
    dirname(__FILE__) . '/lib',
    get_include_path()
);

set_include_path(join(PATH_SEPARATOR, $paths));
require_once 'Zend/Loader/StandardAutoloader.php';

$autoLoader = new \Zend\Loader\StandardAutoloader(array(
    'namespaces' => array(
        'ZendDevOps\DepH' => __DIR__ . '/ZendDevOps/DepH'
    ),

    'fallback_autoloader' => true,
));

// register our StandardAutoloader with the SPL autoloader
$autoLoader->register(); 

define('SCRIPT_ROOT', dirname(__FILE__));