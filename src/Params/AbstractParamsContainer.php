<?php
/**
 * DepH - Zend Server Deployment Helper
 */

namespace ZendServer\DepH\Params;

use ZendServer\DepH\Params\ParamsContainerInterface; 
use Zend\EventManager\EventManagerInterface;

abstract class AbstractParamsContainer implements ParamsContainerInterface {
    
    /**
     * @var EventManagerInterface
     */
    protected $events;
    
    /**
     * @see \Zend\EventManager\EventManagerAwareInterface::setEventManager()
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers(array(
            __CLASS__,
            get_called_class(),
        ));
        $this->events = $events;
        return $this;
    }

    /**
     * @see \Zend\EventManager\EventsCapableInterface::getEventManager()
     */
    public function getEventManager()
    {
        return $this->events;
    }
}