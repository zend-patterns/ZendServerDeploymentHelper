<?php
declare(strict_types=1);
/**
 * Zend Server Deployment Helper (https://github.com/zend-patterns/ZendServerDeploymentHelper)
 *
 * @link      https://github.com/zend-patterns/ZendServerDeploymentHelper for the canonical source repository
 * @copyright https://github.com/zend-patterns/ZendServerDeploymentHelper/blob/master/COPYRIGHT.md Copyright
 * @license   https://github.com/zend-patterns/ZendServerDeploymentHelper/blob/master/LICENSE.md New BSD License
 */

namespace ZendServer\DepH\Pipeline;

use function is_callable;
use function is_string;
use Psr\Container\ContainerInterface;
use ZendServer\DepH\Pipeline\Middleware\LazyLoadingMiddleware;

/**
 * Class MiddlewareContainerResolver
 * @package ZendServer\DepH\Pipeline
 */
class MiddlewareContainerResolver extends MiddlewareResolver
{

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * MiddlewareContainerResolver constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     *
     * @param PipelineInterface|callable|string|array $middleware
     * @throws Exception\MiddlewareResolverException if unable to resolve $middleware
     */
    public function resolve($middleware): PipelineInterface
    {
        if (is_string($middleware) && !is_callable($middleware) && $this->container->has($middleware)) {
            $middleware = new LazyLoadingMiddleware($this->container, $middleware);
        }

        return parent::resolve($middleware);
    }

}