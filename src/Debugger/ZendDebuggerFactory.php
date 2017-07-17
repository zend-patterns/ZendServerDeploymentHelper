<?php
namespace ZendServer\DepH\Debugger;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ZendDebuggerFactory implements FactoryInterface
{

    /**
     * {@inheritdoc}
     *
     * @return ZendDebugger
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new ZendDebugger($serviceLocator->get('Deployment'));
    }
}