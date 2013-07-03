<?php
/**
 * DepH - Zend Server Deployment Helper
 */

namespace ZendDevOps\DepH\Params;

use ZendDevOps\DepH\Params\Exception;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\EventManager\SharedEventManagerAwareInterface;

class Params implements SharedEventManagerAwareInterface
{
    /**
     * @var SharedEventManagerInterface
     */
    private $events;
    
    /**
     * @var \ZendDevOps\DepH\Deployment\Deployment
     */
    private $deployment;
    
    /**
     * Array of \ZendDevOps\DepH\Params\ParamsContainerInterface items
     * Containers can be used for accessing different kinds of 
     * environment variables (e.g. Set by ZendServer, set by User Param)
     * @var array
     */
    private $paramContainerStack = array();
    
    /**
     * @param SharedEventManagerInterface $events
     */
    public function __construct(SharedEventManagerInterface $events) {
        $this->setSharedManager($events);
        $this->attachTriggers();
    }
    
    /**
     * Attach triggers for env variable listeners 
     */
    private function attachTriggers() {
        $events = $this->getSharedManager();
        
        $events->attach(array('ZendDevOps\DepH\Params\ZendServer'), 'getApplicationBaseDir', array($this, 'stopPropagationIfActionPreStage'));
        $events->attach(array('ZendDevOps\DepH\Params\ZendServer'), array('getPreviousAppVersion', 'getPreviousApplicationBaseDir'), array($this, 'stopPropagationIfNotUpdate'));
        $events->attach(array('ZendDevOps\DepH\Params\ZendServer'), '*', array($this, 'checkEnvVariable'));
        $events->attach(array('ZendDevOps\DepH\Params\Custom'), 'get', array($this, 'checkEnvVariable'));
    }

    /**
     * @see \Zend\EventManager\SharedEventManagerAwareInterface::setSharedManager()
     */
    public function setSharedManager(SharedEventManagerInterface $events)
    {
        $this->events = $events;
        return $this;
    }

    /**
     * @see \Zend\EventManager\SharedEventManagerAwareInterface::getSharedManager()
     * @throws \ZendDevOps\DepH\Params\Exception\MissingPropertyException
     */
    public function getSharedManager()
    {
        if (null === $this->events) {
            throw new Exception\MissingPropertyException("Event Manager has not been set");
        }
        return $this->events;
    }

    /**
     * @see \Zend\EventManager\SharedEventManagerAwareInterface::unsetSharedManager()
     */
    public function unsetSharedManager() {
        $this->events = null;
    }
    
    /**
     * @param \ZendDevOps\DepH\Deployment\Deployment $deployment
     */
    public function setDeployment(\ZendDevOps\DepH\Deployment\Deployment $deployment) {
        $this->deployment = $deployment;
    }
    
    /**
     * Add ParamContainer to stack
     * @param \ZendDevOps\DepH\Params\ParamsContainerInterface $container
     * @throws \ZendDevOps\DepH\Params\Exception\InvalidArgumentException
     */
    public function addParamContainer(\ZendDevOps\DepH\Params\ParamsContainerInterface $container) {
        $class = strtolower(get_class($container));
        if (array_key_exists($class, $this->paramContainerStack)) {
            throw new Exception\InvalidArgumentException("Object of class $class cannot be added twice to paramsContainerStack");
        }
        $this->paramContainerStack[$class] = $container;
    }
    
    /**
     * Check whether given env variable is set. if it's not set an exception is thrown!
     * And so the deployment process will terminate.
     * Method expect Event object as parameter, as it's only called by event listeners 
     * 
     * @param \Zend\EventManager\Event $event
     * @throws Exception\RuntimeException
     * @return bool
     */
    public function checkEnvVariable(\Zend\EventManager\Event $event) {
        if (getenv($event->getParam('envname')) === false) {
            throw new Exception\RuntimeException('env variable '. $event->getParam('envname') . ' is not set');
        }
        
        return true;
    }
    
    /**
     * Called by EventListeners. Sometimes the deployment process should be terminated,
     * as environment variables are used/accessed which are not available in certain
     * steps of the process
     * 
     * @param \Zend\EventManager\Event $event
     * @throws Exception\RuntimeException
     */
    public function stopPropagationIfActionPreStage(\Zend\EventManager\Event $event) {
        if ($this->deployment->isPreStageAction()) {
            // @todo use stop propagation and collect problems 
            throw new Exception\RuntimeException('It\'s not possible to use env variable ' . $event->getParam('envname') . ' in Phase PRE_STAGE!');
        }
    }

    /**
     * Called by EventListeners. Sometimes the deployment process should be terminated,
     * as environment variables are used/accessed which are not available in certain
     * steps of the process
     * 
     * @param \Zend\EventManager\Event $event
     * @throws Exception\RuntimeException
     */
    public function stopPropagationIfNotUpdate(\Zend\EventManager\Event $event) {
        if (!$this->deployment->isUpdate()) {
            // @todo use stop propagation and collect problems 
            throw new Exception\RuntimeException('It\'s not possible to use env variable ' . $event->getParam('envname') . ' on a freshly installed App');
        }
    }

    /**
     * Wrapper for calling Params/methods from all registered containers. 
     * Works with FIFO principle.
     * 
     * @param string $name
     * @param array $arguments
     * @throws Exception\RuntimeException
     */
    public function __call($name, $arguments) {
        foreach ($this->paramContainerStack as $class => $container) {
            if (method_exists($container, $name)) {
                return $container->$name($arguments);
            }
        }
        
        throw new Exception\RuntimeException("Cannot call method $name on registered ParamContainers");
        
    }
}