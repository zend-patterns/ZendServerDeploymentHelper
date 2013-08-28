<?php
namespace ZendDevOps\DepH\ServiceManager;

use \ZendDevOps\DepH\Log\Log as Logger;
use \ZendDevOps\DepH\Log\LogAwareInterface;
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
        
        $this->setInvokableClass('Deployment', '\ZendDevOps\DepH\Deployment\Deployment');
        $this->setInvokableClass('Template', '\ZendDevOps\DepH\File\Template', false);
        $this->setInvokableClass('Shell', '\ZendDevOps\DepH\SystemCall\Shell');
        
        $this->setInvokableClass('ZSParams', '\ZendDevOps\DepH\Params\ZendServer');
        $this->setInvokableClass('CustomParams', '\ZendDevOps\DepH\Params\Custom');
        
        $this->setFactory('Params', function () use ($sm) {
            $params = new \ZendDevOps\DepH\Params\Params($sm->get('SharedEventManager'));
            
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
                '/ZendDevOps/',
                '/ZendFramework2/',
            	'/Zend_Framework_2/',
                'deph.php'
            ));
            
            return $logger; 
        });
        
        $this->setFactory('Path', function() use ($sm) {
            $params = $sm->get('Params');
            $shell = $sm->get('Shell');
            $path = new \ZendDevOps\DepH\Path\Path();
            $path->setParams($params);
            $path->setShell($shell);
            return $path;
        });
        
        $this->setFactory('ZendDebugger', function () use ($sm) {
            $debugger = new \ZendDevOps\DepH\Debugger\ZendDebugger();
            $debugger->setDeployment($sm->get('Deployment'));
            return $debugger;
        });
        
        $this->setFactory('Vhost', function () use ($sm) {
            $vhost = new \ZendDevOps\DepH\Vhost\Vhost();
            $vhost->setEventManager($sm->get('EventManager'));
            $vhost->setShell($sm->get('Shell'));
            $vhost->setPath($sm->get('Path'));
            $vhost->setTemplate($sm->get('Template'));
            $vhost->setDeployment($sm->get('Deployment'));
            
            $vhost->getEventManager()->attach('write', array($vhost, 'allowedToWrite'));
            $vhost->getEventManager()->attach('finalize', array($vhost, 'preparingFinalize'));

            return $vhost;
        });
        
        $this->setInvokableClass('MysqliFactory', '\ZendDevOps\DepH\Db\MysqliFactory');
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
        
        $vhost = $sm->get('Vhost');
        if (is_file($vhost->getCustomVhostAwareFile()) &&
            $deployment->isPostActivateAction()) {
            $vhost->finalize();
        }
    }
    
    /**
     * Shortcut for starting the Log
     * @return \ZendDevOps\DepH\Log\Log
     */
    public function startLog() {
        return $this->get('Log');
    }
    
    /**
     * Shortcut for starting the log with GUI Output
     * @return \ZendDevOps\DepH\Log\Log
     */
    public function startGuiLog() {
        $log = $this->get('Log');
        $log->addGuiOutput();
        return $log;
    }
    
    /**
     * @return \ZendDevOps\DepH\Db\AdapterMysqli
     */
    public function getDb() {
        return $this->get('DB');
    }
    
    /**
     * @return \ZendDevOps\DepH\Debugger\ZendDebugger
     */
    public function getDebugger() {
        return $this->get('ZendDebugger');
    }
    
    /**
     * @return \ZendDevOps\DepH\Deployment\Deployment
     */
    public function getDeployment() {
        return $this->get('Deployment');
    }
    
    /**
     * @return \ZendDevOps\DepH\File\Template
     */
    public function getTemplate() {
        return $this->get('Template');
    }
    
    /**
     * @return \ZendDevOps\DepH\Params\Params
     */
    public function getParams() {
        return $this->get('Params');
    }
    
    /**
     * @return \ZendDevOps\DepH\Path\Path
     */
    public function getPath() {
        return $this->get('Path');
    }
    
    /**
     * @return \ZendDevOps\DepH\SystemCall\Shell
     */
    public function getShell() {
        return $this->get('Shell');
    }
    
    /**
     * @return \ZendDevOps\DepH\Vhost\Vhost
     */
    public function getVhost() {
        return $this->get('Vhost');
    }    
}