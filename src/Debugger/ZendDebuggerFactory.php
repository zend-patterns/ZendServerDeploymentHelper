<?php
declare(strict_types=1);
/**
 * Zend Server Deployment Helper (https://github.com/zend-patterns/ZendServerDeploymentHelper)
 *
 * @link      https://github.com/zend-patterns/ZendServerDeploymentHelper for the canonical source repository
 * @copyright https://github.com/zend-patterns/ZendServerDeploymentHelper/blob/master/COPYRIGHT.md Copyright
 * @license   https://github.com/zend-patterns/ZendServerDeploymentHelper/blob/master/LICENSE.md New BSD License
 */

namespace ZendServer\DepH\Debugger;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use ZendServer\DepH\Deployment\Deployment;

/**
 * Class ZendDebuggerFactory
 * @package ZendServer\DepH\Debugger
 */
class ZendDebuggerFactory implements FactoryInterface
{

    /**
     * {@inheritdoc}
     *
     * @return ZendDebugger
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ZendDebugger($container->get(Deployment::class));
    }
}