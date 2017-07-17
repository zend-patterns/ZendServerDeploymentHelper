<?php
namespace ZendServer\DepH\ServiceManager\Initializer;

use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZendServer\DepH\Log\LogAwareInterface;

class LogInitializer implements InitializerInterface
{

    /**
     * Initialize
     *
     * @param                         $instance
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return mixed
     */
    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {
        if ($instance instanceof LogAwareInterface) {
            $instance->setLog($serviceLocator->get('Log'));
        }
    }
}