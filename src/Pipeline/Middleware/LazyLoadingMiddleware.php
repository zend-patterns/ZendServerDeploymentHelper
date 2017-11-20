<?php
declare(strict_types=1);
/**
 * Zend Server Deployment Helper (https://github.com/zend-patterns/ZendServerDeploymentHelper)
 *
 * @link      https://github.com/zend-patterns/ZendServerDeploymentHelper for the canonical source repository
 * @copyright https://github.com/zend-patterns/ZendServerDeploymentHelper/blob/master/COPYRIGHT.md Copyright
 * @license   https://github.com/zend-patterns/ZendServerDeploymentHelper/blob/master/LICENSE.md New BSD License
 */

namespace ZendServer\DepH\Pipeline\Middleware;

use function is_callable;
use Psr\Container\ContainerInterface;
use ZendServer\DepH\Pipeline\DelegateInterface;
use ZendServer\DepH\Pipeline\PipelineInterface;
use ZendServer\DepH\Pipeline\ResultInterface;
use ZendServer\DepH\Pipeline\Exception;

/**
 * Class LazyLoadingMiddleware
 * @package ZendServer\DepH\Pipeline\Middleware
 */
class LazyLoadingMiddleware implements PipelineInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    private $middlewareName;

    /**
     * LazyLoadingMiddleware constructor.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param                                   $middlewareName
     */
    public function __construct(
        ContainerInterface $container,
        $middlewareName
    ) {
        $this->container = $container;
        $this->middlewareName = $middlewareName;
    }

    /**
     * Create a new pipeline with an appended stage.
     *
     * @param mixed             $payload
     * @param DelegateInterface $delegate
     *
     * @return ResultInterface
     * @throws Exception\MiddlewareResolverException
     */
    public function process($payload, DelegateInterface $delegate = null): ResultInterface
    {
        $middleware = $this->container->get($this->middlewareName);
        if ($middleware instanceof PipelineInterface) {
            return $middleware->process($payload, $delegate);
        }
        // Unknown - invalid!
        if (!is_callable($middleware)) {
            throw new Exception\MiddlewareResolverException(sprintf(
                'Lazy-loaded middleware "%s" is neither invokable nor implements %s',
                $this->middlewareName,
                PipelineInterface::class
            ));
        }

        return $middleware($payload, $delegate);
    }
}