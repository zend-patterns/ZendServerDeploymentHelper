<?php
declare(strict_types=1);
/**
 * Zend Server Deployment Helper (https://github.com/zend-patterns/ZendServerDeploymentHelper)
 *
 * @link      https://github.com/zend-patterns/ZendServerDeploymentHelper for the canonical source repository
 * @copyright https://github.com/zend-patterns/ZendServerDeploymentHelper/blob/master/COPYRIGHT.md Copyright
 * @license   https://github.com/zend-patterns/ZendServerDeploymentHelper/blob/master/LICENSE.md New BSD License
 */

namespace ZendServer\DepH;

use Psr\Log\LoggerInterface;
use Zend\EventManager;

/**
 * Class ConfigProvider
 * @package ZendServer\DepH
 */
class ConfigProvider
{

    /**
     * @return array
     */
    public function getDependencyConfig()
    {
        return [
            'factories'    => [
                Debugger\ZendDebugger::class                => Debugger\ZendDebuggerFactory::class,
                Pipeline\MiddlewareContainerResolver::class => Pipeline\MiddlewareContainerResolverFactory::class,
                Pipeline\Pipeline::class                    => Pipeline\PipelineFactory::class,
            ],
            'invokables'   => [
                EventManager\SharedEventManager::class,
                Deployment\Deployment::class,
                File\Template::class,
            ],
            'aliases'      => [
                'SharedEventManager' => EventManager\SharedEventManager::class,
                'Deployment'         => Deployment\Deployment::class,
                'Template'           => File\Template::class,
                'Log'                => LoggerInterface::class,
                'ZendDebugger'       => Debugger\ZendDebugger::class,
            ],
            'initializers' => [
                Initializer\EventManagerInitializer::class,
                Log\Initializer\PsrLogInitializer::class,
            ],
        ];
    }

    /**
     * @return array
     */
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
        ];
    }

}