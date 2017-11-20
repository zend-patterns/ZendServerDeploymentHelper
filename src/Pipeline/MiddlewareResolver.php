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

use function class_exists;
use function get_class;
use function gettype;
use function is_array;
use function is_callable;
use function is_object;
use function is_string;
use function sprintf;

/**
 * Class MiddlewareResolver
 * @package ZendServer\DepH\Pipeline
 */
class MiddlewareResolver implements MiddlewareResolverInterface
{

    /**
     * {@inheritdoc}
     *
     * @param PipelineInterface|callable|string|array $middleware
     * @throws Exception\MiddlewareResolverException if unable to resolve $middleware
     */
    public function resolve($middleware): PipelineInterface
    {
        if ($middleware instanceof PipelineInterface) {
            return $middleware;
        }

        if (is_callable($middleware)) {
            return $this->resolveCallable($middleware);
        }

        if (is_string($middleware)) {
            return $this->resolveClass($middleware);
        }

        if (is_array($middleware)) {
            return $this->resolveArray($middleware);
        }

        throw new Exception\MiddlewareResolverException(sprintf(
            'Unable to resolve middleware "%s" to a callable or %s',
            is_object($middleware) ? get_class($middleware) . '[Object]' : gettype($middleware) . '[Scalar]',
            PipelineInterface::class
        ));
    }

    /**
     * @param string $middleware
     *
     * @return PipelineInterface
     * @throws Exception\MiddlewareResolverException
     */
    private function resolveClass(string $middleware): PipelineInterface
    {
        if (!class_exists($middleware)) {
            throw new Exception\MiddlewareResolverException(sprintf(
                'Unable to create middleware "%s". Not a valid class.',
                $middleware
            ));
        }

        $instance = new $middleware();
        if ($instance instanceof PipelineInterface) {
            return $instance;
        }

        if (is_callable($instance)) {
            return $this->resolveCallable($instance);
        }

        throw new Exception\MiddlewareResolverException(sprintf(
            'Unable to resolve "%s" to a callable instance.',
            $middleware
        ));
    }

    /**
     * @param callable $middleware
     *
     * @return \ZendServer\DepH\Pipeline\Middleware\CallableMiddlewareWrapper
     */
    private function resolveCallable(callable $middleware): Middleware\CallableMiddlewareWrapper
    {
        return new Middleware\CallableMiddlewareWrapper($middleware);
    }

    /**
     * @param array $middlewares
     *
     * @return \ZendServer\DepH\Pipeline\Pipeline
     */
    private function resolveArray(array $middlewares): Pipeline
    {
        $pipeline = new Pipeline();
        foreach ($middlewares as $middleware) {
            $pipeline->pipe($this->resolve($middleware));
        }

        return $pipeline;
    }
}