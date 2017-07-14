<?php
namespace ZendServer\DepH\ServiceManager;

use \ZendServer\DepH\Log\Log as Logger;
use \ZendServer\DepH\Log\LogAwareInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;

class ServiceManager extends \Zend\ServiceManager\ServiceManager {
    /**
     * Ramp up the system...
     * 
     * @param string $currentActionScript
     */
    public function __construct($currentActionScript = null) {
        $sm = $this;
        
        $this->setInvokableClass('EventManager', '\Zend\EventManager\EventManager', false);
        $this->setInvokableClass('SharedEventManager', '\Zend\EventManager\SharedEventManager');
        $this->addInitializer(function ($instance) use ($sm) {
            if ($instance instanceof EventManagerAwareInterface) {
                if (! $instance->getEventManager() instanceof EventManagerInterface) {
                    $instance->setEventManager($sm->get('EventManager'));
                } 
                $instance->getEventManager()->setSharedManager(
                    $sm->get('SharedEventManager')
                );
            }
        });
        
        $this->addInitializer(function ($instance) use ($sm) {
            if ($instance instanceof LogAwareInterface) {
                $instance->setLog($sm->get('Log'));
            }
        });
        
        $this->setInvokableClass('Deployment', '\ZendServer\DepH\Deployment\Deployment');
        $this->setInvokableClass('Template', '\ZendServer\DepH\File\Template', false);
        $this->setInvokableClass('Shell', '\ZendServer\DepH\SystemCall\Shell');
        
        $this->setInvokableClass('ZSParams', '\ZendServer\DepH\Params\ZendServer');
        $this->setInvokableClass('CustomParams', '\ZendServer\DepH\Params\Custom');
        
        $this->setFactory('Params', function () use ($sm) {
            $params = new \ZendServer\DepH\Params\Params($sm->get('SharedEventManager'));
            
            $params->addParamContainer($sm->get('ZSParams'));
            $params->addParamContainer($sm->get('CustomParams'));
            return $params;
        });
        
        $this->setFactory('Log', function () use ($sm) {
            $params = $sm->get('Params');
            $appName = $params->getApplicationName();
            $appVersion = $params->getCurrentAppVersion();
            $logFilePath = Logger::getLogFilePath($appName, $appVersion);
            $writer = new \Zend\Log\Writer\Stream($logFilePath);
            $logger = new Logger();
            $logger->addWriter($writer);
            $logger->setFullVerboseExcludeList(array(
                '/ZendServer/',
                '/ZendFramework2/',
            	'/Zend_Framework_2/',
                'deph.php'
            ));
            
            return $logger; 
        });
        
        $this->setFactory('Path', function() use ($sm) {
            $params = $sm->get('Params');
            $shell = $sm->get('Shell');
            $path = new \ZendServer\DepH\Path\Path();
            $path->setParams($params);
            $path->setShell($shell);
            return $path;
        });
        
        $this->setFactory('ZendDebugger', function () use ($sm) {
            $debugger = new \ZendServer\DepH\Debugger\ZendDebugger();
            $debugger->setDeployment($sm->get('Deployment'));
            return $debugger;
        });       
 
        $this->setInvokableClass('MysqliFactory', '\ZendServer\DepH\Db\MysqliFactory');
        $this->setFactory('DB', $sm->get('MysqliFactory'));
        
        $deployment = $sm->get('Deployment');
        $logger = $sm->get('Log');
        $params = $sm->get('Params');
        
        $deploymentEm = $deployment->getEventManager();
        $deploymentEm->attach('terminate', array($logger, 'termination'));
        
        $action = $deployment->getCurrentActionScript();
        
        $logger->info('*** Script ' . $action . ' started ***');
        $logger->setCurrentActionScriptName($action);
        
        $params->setDeployment($deployment);
    }
    
    /**
     * Shortcut for starting the Log
     * @return \ZendServer\DepH\Log\Log
     */
    public function startLog() {
        return $this->get('Log');
    }
    
    /**
     * Shortcut for starting the log with GUI Output
     * @return \ZendServer\DepH\Log\Log
     */
    public function startGuiLog() {
        $log = $this->get('Log');
        $log->addGuiOutput();
        return $log;
    }
    
    /**
     * @return \ZendServer\DepH\Db\AdapterMysqli
     */
    public function getDb() {
        return $this->get('DB');
    }
    
    /**
     * @return \ZendServer\DepH\Debugger\ZendDebugger
     */
    public function getDebugger() {
        return $this->get('ZendDebugger');
    }
    
    /**
     * @return \ZendServer\DepH\Deployment\Deployment
     */
    public function getDeployment() {
        return $this->get('Deployment');
    }
    
    /**
     * @return \ZendServer\DepH\File\Template
     */
    public function getTemplate() {
        return $this->get('Template');
    }
    
    /**
     * @return \ZendServer\DepH\Params\Params
     */
    public function getParams() {
        return $this->get('Params');
    }
    
    /**
     * @return \ZendServer\DepH\Path\Path
     */
    public function getPath() {
        return $this->get('Path');
    }
    
    /**
     * @return \ZendServer\DepH\SystemCall\Shell
     */
    public function getShell() {
        return $this->get('Shell');
    }
    
    /**
     * @return \ZendServer\DepH\Vhost\Vhost
     */
    public function getVhost() {
        return $this->get('Vhost');
    }    
}