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

/**
 * Interface MiddlewareResolverInterface
 * @package ZendServer\DepH\Pipeline
 */
interface MiddlewareResolverInterface
{

    /**
     * Resolves a $middleware to PipelineInterface
     *
     * @param mixed $middleware
     *
     * @return \ZendServer\DepH\Pipeline\PipelineInterface
     * @throws Exception\MiddlewareResolverException if unable to resolve $middleware
     */
    public function resolve($middleware): PipelineInterface;
}