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
 * Interface PipelineInterface
 * @package ZendServer\DepH\Pipeline
 */
interface PipelineInterface
{

    /**
     * Create a new pipeline with an appended stage.
     *
     * @param mixed  $payload
     * @param DelegateInterface $delegate
     *
     * @return ResultInterface
     */
    public function process($payload, DelegateInterface $delegate = null): ResultInterface;
}