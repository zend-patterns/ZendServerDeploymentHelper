<?php
namespace ZendServer\DepH\ServiceManager\Initializer;

use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EventManagerInitializer implements InitializerInterface
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
        if ($instance instanceof EventManagerAwareInterface) {
            if (!$instance->getEventManager() instanceof EventManagerInterface) {
                $instance->setEventManager($serviceLocator->get('EventManager'));
            }

            $instance->getEventManager()->setSharedManager(
                $serviceLocator->get('SharedEventManager')
            );
        }
    }
}