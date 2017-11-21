<?php
declare(strict_types=1);
/**
 * Zend Server Deployment Helper (https://github.com/zend-patterns/ZendServerDeploymentHelper)
 *
 * @link      https://github.com/zend-patterns/ZendServerDeploymentHelper for the canonical source repository
 * @copyright https://github.com/zend-patterns/ZendServerDeploymentHelper/blob/master/COPYRIGHT.md Copyright
 * @license   https://github.com/zend-patterns/ZendServerDeploymentHelper/blob/master/LICENSE.md New BSD License
 */

namespace ZendServer\DepH\Log\Initializer;

use Interop\Container\ContainerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Zend\ServiceManager\Initializer\InitializerInterface;

/**
 * Class PsrLogInitializer
 * @package ZendServer\DepH\Log\Initializer
 */
class PsrLogInitializer implements InitializerInterface
{

    /**
     * {@inheritdoc}
     */
    public function __invoke(ContainerInterface $container, $instance)
    {
        if ($instance instanceof LoggerAwareInterface) {
            $logger = $container->has(LoggerInterface::class) ? $container->get(LoggerInterface::class) : new NullLogger();
            $instance->setLogger($logger);
        }
    }
}