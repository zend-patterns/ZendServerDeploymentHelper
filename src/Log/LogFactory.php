<?php
namespace ZendServer\DepH\Log;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZendServer\DepH\Log\Log as Logger;

class LogFactory implements FactoryInterface
{

    /**
     * {@inheritdoc}
     *
     * @return Logger
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var \ZendServer\DepH\Deployment\Deployment $deployment */
        $deployment = $serviceLocator->get('Deployment');
        $params = $serviceLocator->get('ZendServer\DepH\Params\Params');
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
            'deph.php',
        ));

        $logger->setCurrentActionScriptName($deployment->getCurrentActionScript());
        $deploymentEm = $deployment->getEventManager();
        $deploymentEm->attach('terminate', array($logger, 'termination'));

        return $logger;
    }
}