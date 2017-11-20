<?php
declare(strict_types=1);
/**
 * Zend Server Deployment Helper (https://github.com/zend-patterns/ZendServerDeploymentHelper)
 *
 * @link      https://github.com/zend-patterns/ZendServerDeploymentHelper for the canonical source repository
 * @copyright https://github.com/zend-patterns/ZendServerDeploymentHelper/blob/master/COPYRIGHT.md Copyright
 * @license   https://github.com/zend-patterns/ZendServerDeploymentHelper/blob/master/LICENSE.md New BSD License
 */

namespace ZendServer\DepH\Log\Factory;

use Interop\Container\ContainerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Zend\ServiceManager\Factory\FactoryInterface;
use ZendServer\DepH\Deployment\Deployment;

/**
 * Class MonologFactory
 * @package ZendServer\DepH\Log\Factory
 */
class MonologFactory implements FactoryInterface
{

    /**
     * {@inheritdoc}
     * @return \Psr\Log\LoggerInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var Deployment $deployment */
        $deployment = $container->get(Deployment::class);
        $logger = new Logger('default');
        $handler = new StreamHandler($deployment->getApplicationLogFile());
        $logger->pushHandler($handler);

        return $logger;
    }
}