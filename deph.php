<?php
/**
 * DepH - Zend Server Deployment Helper
 */

/**
 * DepH Bootstrapping and desaster error handling
 */

define('SCRIPT_ROOT', dirname(__FILE__));
chdir(__DIR__);

// Setup autoloading
require 'init_autoloader.php';

use \ZendServer\DepH\ServiceManager\ServiceManager as ZendServerDepH;

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

class DepH extends ZendServerDepH{
    public function __construct($displayErrors = true) {
        global $GlobalDepH;
        if ($displayErrors) ini_set('display_errors', 'on');
        parent::__construct();
        $GlobalDepH = $this;
    }
}
