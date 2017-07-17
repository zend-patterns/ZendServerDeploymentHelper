<?php
namespace ZendServer\DepH\Params;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ParamsFactory implements FactoryInterface
{

    /**
     * {@inheritdoc}
     *
     * @return Params
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $params = new \ZendServer\DepH\Params\Params($serviceLocator->get('Zend\EventManager\SharedEventManager'));

        $params->addParamContainer($serviceLocator->get('ZendServer\DepH\Params\ZendServer'));
        $params->addParamContainer($serviceLocator->get('ZendServer\DepH\Params\Custom'));

        $params->setDeployment($serviceLocator->get('Deployment'));

        return $params;
    }
}