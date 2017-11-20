<?php
declare(strict_types=1);
/**
 * Zend Server Deployment Helper (https://github.com/zend-patterns/ZendServerDeploymentHelper)
 *
 * @link      https://github.com/zend-patterns/ZendServerDeploymentHelper for the canonical source repository
 * @copyright https://github.com/zend-patterns/ZendServerDeploymentHelper/blob/master/COPYRIGHT.md Copyright
 * @license   https://github.com/zend-patterns/ZendServerDeploymentHelper/blob/master/LICENSE.md New BSD License
 */

namespace ZendServerTest\DepH\Pipeline\stubs;

use Psr\Http\Message\StreamInterface;
use ZendServer\DepH\Pipeline\DelegateInterface;
use ZendServer\DepH\Pipeline\PayloadInterface;
use ZendServer\DepH\Pipeline\PipelineInterface;
use ZendServer\DepH\Pipeline\Result;
use ZendServer\DepH\Pipeline\ResultInterface;

function middleware()
{
}

class InvalidMiddleware
{

    protected function doNonePublic()
    {

    }
}

class InvokableMiddleware extends InvalidMiddleware
{

    public function __invoke()
    {
    }

    public function doPublic()
    {

    }

    public static function doStaticPublic()
    {

    }

}

class MiddlewareImpl implements PipelineInterface
{

    /**
     * {@inheritdoc}
     */
    public function process($payload, DelegateInterface $delegate = null): ResultInterface
    {
        return new Result();
    }
}