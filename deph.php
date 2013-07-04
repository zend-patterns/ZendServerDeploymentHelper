<?php
/**
 * DepH - Zend Server Deployment Helper
 */

/**
 * DepH Bootstrapping and desaster error handling
 */

use \ZendDevOps\DepH\ServiceManager\ServiceManager as ZendDevOpsDepH;

define('SCRIPT_ROOT', dirname(__FILE__));
set_include_path(join(PATH_SEPARATOR, array(
	'/usr/local/zend/share/ZendFramework2/library', // Zend Server 6.0
	'/usr/local/zend/var/libraries/Zend_Framework_2/default/library', // Zend Server 6.1
	get_include_path()
)));
require_once 'Zend/Loader/StandardAutoloader.php';

$autoLoader = new \Zend\Loader\StandardAutoloader(array(
    'namespaces' => array(
        'ZendDevOps\DepH' => __DIR__ . '/ZendDevOps/DepH',
    ),

    'fallback_autoloader' => true,
));

// register our StandardAutoloader with the SPL autoloader
$autoLoader->register(); 

set_exception_handler(function(\Exception $exception) {
    global $GlobalDepH;
    if (!$GlobalDepH) {
        echo "Obviously the master DepH class couldn't be created (should not have happened)... \nthat's why we have to stop the script here the hard way.\nMaybe you can find a solution with the attached message and stack below.\n" . $exception->getMessage() . "\n" . $exception->getTraceAsString();
        exit(1);  
    }
   
    $deployment = $GlobalDepH->get('Deployment');
    $em = $deployment->getEventManager();
    $em->attach('terminate', array($GlobalDepH->get('Log'), 'exception'));
    $deployment->terminate($exception->getMessage()); 
});

$GlobalDepH;

class DepH extends ZendDevOpsDepH{
    public function __construct($displayErrors = true) {
        global $GlobalDepH;
        if ($displayErrors) ini_set('display_errors', 'on');
        parent::__construct();
        $GlobalDepH = $this;
    }
}