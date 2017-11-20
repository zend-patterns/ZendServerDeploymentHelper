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
 * Interface DelegateInterface
 * @package ZendServer\DepH\Pipeline
 */
interface DelegateInterface
{

    /**
     * Dispatch the next available middleware and return the response.
     *
     * @param mixed $payload
     *
     * @return ResultInterface
     */
    public function process($payload): ResultInterface;
}