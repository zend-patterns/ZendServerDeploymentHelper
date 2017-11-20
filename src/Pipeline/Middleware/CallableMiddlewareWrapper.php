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

use ZendServer\DepH\Pipeline\DelegateInterface;
use ZendServer\DepH\Pipeline\PayloadInterface;
use ZendServer\DepH\Pipeline\PipelineInterface;
use ZendServer\DepH\Pipeline\ResultInterface;

/**
 * Class CallableMiddlewareWrapper
 * @package ZendServer\DepH\Pipeline\Middleware
 */
class CallableMiddlewareWrapper implements PipelineInterface
{

    /**
     * @param callable
     */
    private $middleware;

    /**
     * @param callable $middleware
     */
    public function __construct(callable $middleware)
    {
        $this->middleware = $middleware;
    }

    /**
     * Create a new pipeline with an appended stage.
     *
     * @param PayloadInterface  $payload
     * @param DelegateInterface $delegate
     *
     * @return ResultInterface
     */
    public function process($payload, DelegateInterface $delegate = null): ResultInterface
    {
        $middleware = $this->middleware;

        return $middleware($payload, $delegate);
    }
}