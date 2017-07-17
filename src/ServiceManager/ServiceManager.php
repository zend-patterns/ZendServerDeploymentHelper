<?php

namespace ZendServer\DepH\ServiceManager;

use Zend\Config\Config;
use Zend\ServiceManager\ConfigInterface;
use ZendServer\DepH\ConfigProvider;

class ServiceManager extends \Zend\ServiceManager\ServiceManager
{

    /**
     * Ramp up the system...
     *
     * @param string|null                               $currentActionScript
     * @param \Zend\ServiceManager\ConfigInterface|null $config
     */
    public function __construct($currentActionScript = null, ConfigInterface $config = null)
    {
        if (null === $config) {
            $provider = new ConfigProvider();
            $config = new Config($provider->getDependencyConfig());
        }

        parent::__construct($config);

        /** @var \ZendServer\DepH\Deployment\Deployment $deployment */
        $deployment = $this->get('Deployment');

        /** @var \ZendServer\DepH\Log\Log $logger */
        $logger = $this->get('Log');

        $action = $deployment->getCurrentActionScript();
        $logger->info('*** Script ' . $action . ' started ***');
    }

    /**
     * Shortcut for starting the Log
     * @return \ZendServer\DepH\Log\Log
     */
    public function startLog()
    {
        return $this->get('Log');
    }

    /**
     * Shortcut for starting the log with GUI Output
     * @return \ZendServer\DepH\Log\Log
     */
    public function startGuiLog()
    {
        $log = $this->get('Log');
        $log->addGuiOutput();

        return $log;
    }

    /**
     * @return \ZendServer\DepH\Db\AdapterMysqli
     */
    public function getDb()
    {
        return $this->get('DB');
    }

    /**
     * @return \ZendServer\DepH\Debugger\ZendDebugger
     */
    public function getDebugger()
    {
        return $this->get('ZendDebugger');
    }

    /**
     * @return \ZendServer\DepH\Deployment\Deployment
     */
    public function getDeployment()
    {
        return $this->get('Deployment');
    }

    /**
     * @return \ZendServer\DepH\File\Template
     */
    public function getTemplate()
    {
        return $this->get('Template');
    }

    /**
     * @return \ZendServer\DepH\Params\Params
     */
    public function getParams()
    {
        return $this->get('Params');
    }

    /**
     * @return \ZendServer\DepH\Path\Path
     */
    public function getPath()
    {
        return $this->get('Path');
    }

    /**
     * @return \ZendServer\DepH\SystemCall\Shell
     */
    public function getShell()
    {
        return $this->get('Shell');
    }

    /**
     * @return \ZendServer\DepH\Vhost\Vhost
     */
    public function getVhost()
    {
        return $this->get('Vhost');
    }
}