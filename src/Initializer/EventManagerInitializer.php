<?php
declare(strict_types=1);
/**
 * Zend Server Deployment Helper (https://github.com/zend-patterns/ZendServerDeploymentHelper)
 *
 * @link      https://github.com/zend-patterns/ZendServerDeploymentHelper for the canonical source repository
 * @copyright https://github.com/zend-patterns/ZendServerDeploymentHelper/blob/master/COPYRIGHT.md Copyright
 * @license   https://github.com/zend-patterns/ZendServerDeploymentHelper/blob/master/LICENSE.md New BSD License
 */

namespace ZendServer\DepH\Initializer;

use Interop\Container\ContainerInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\SharedEventManager;
use Zend\ServiceManager\Initializer\InitializerInterface;

/**
 * Class EventManagerInitializer
 * @package ZendServer\DepH\Initializer
 */
class EventManagerInitializer implements InitializerInterface
{

    /**
     * {@inheritdoc}
     */
    public function __invoke(ContainerInterface $container, $instance)
    {
        if ($instance instanceof EventManagerAwareInterface && $container->has(SharedEventManager::class)) {
            $instance->setEventManager(new EventManager($container->get(SharedEventManager::class)));
        }
    }
}