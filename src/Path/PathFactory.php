<?php
namespace ZendServer\DepH\Path;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PathFactory implements FactoryInterface
{

    /**
     * {@inheritdoc}
     *
     * @return Path
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $params = $serviceLocator->get('Params');
        $shell = $serviceLocator->get('Shell');

        return new Path($params,  $shell);
    }
}