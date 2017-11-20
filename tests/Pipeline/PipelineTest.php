<?php
declare(strict_types=1);
/**
 * Zend Server Deployment Helper (https://github.com/zend-patterns/ZendServerDeploymentHelper)
 *
 * @link      https://github.com/zend-patterns/ZendServerDeploymentHelper for the canonical source repository
 * @copyright https://github.com/zend-patterns/ZendServerDeploymentHelper/blob/master/COPYRIGHT.md Copyright
 * @license   https://github.com/zend-patterns/ZendServerDeploymentHelper/blob/master/LICENSE.md New BSD License
 */

namespace ZendServerTest\DepH\Pipeline;

use PHPUnit\Framework\TestCase;
use ZendServer\DepH\Pipeline\Exception;
use ZendServer\DepH\Pipeline\Payload;
use ZendServer\DepH\Pipeline\Pipeline;
use ZendServer\DepH\Pipeline\PipelineInterface;
use ZendServer\DepH\Pipeline\ResultInterface;
use ZendServerTest\DepH\Pipeline\stubs\InvalidMiddleware;
use ZendServerTest\DepH\Pipeline\stubs\InvokableMiddleware;
use ZendServerTest\DepH\Pipeline\stubs\MiddlewareImpl;

/**
 * Class PipelineTest
 * @package ZendServerTest\DepH\Pipeline
 */
class PipelineTest extends TestCase
{

    /**
     * @var Pipeline
     */
    private $pipeline;

    protected function setUp()
    {
        $this->pipeline = new Pipeline();
    }

    public function middlewareDataProvider(): array
    {
        require_once __DIR__ . '/_files/stubs.php';

        return [
            'anonymous function'            => [
                function () {
                },
            ],
            'callable function name'        => ['ZendServerTest\DepH\Pipeline\stubs\middleware'],
            'callable array with object'    => [[new InvokableMiddleware(), 'doPublic']],
            'callable array with class'     => [[InvokableMiddleware::class, 'doStaticPublic']],
            'invokable object'              => [new InvokableMiddleware()],
            'invokable class'               => [InvokableMiddleware::class],
            'class implementing interface'  => [MiddlewareImpl::class],
            'object implementing interface' => [new MiddlewareImpl()],
            'list of valid middlewares'     => [
                [
                    function () {
                    },
                    'ZendServerTest\DepH\Pipeline\stubs\middleware',
                    [new InvokableMiddleware(), 'doPublic'],
                    [InvokableMiddleware::class, 'doStaticPublic'],
                    new InvokableMiddleware(),
                    InvokableMiddleware::class,
                    MiddlewareImpl::class,
                    new MiddlewareImpl(),
                ],
            ],
            'non-callable function name'    => ['non_existent_function', Exception\MiddlewareResolverException::class],
            'non-callable object-method'    => [
                [new InvokableMiddleware(), 'doNonePublic'],
                Exception\MiddlewareResolverException::class,
            ],
            'non-callable class-method'     => [
                [InvokableMiddleware::class, 'doNonePublic'],
                Exception\MiddlewareResolverException::class,
            ],
            'non-invokable object'          => [new InvalidMiddleware(), Exception\MiddlewareResolverException::class],
            'non-invokable class'           => [InvalidMiddleware::class, Exception\MiddlewareResolverException::class],

        ];
    }

    /**
     * @dataProvider middlewareDataProvider
     *
     * @param mixed       $middleware
     * @param string|null $expectedException
     */
    public function testCanBePiped($middleware, $expectedException = null)
    {
        if (null !== $expectedException) {
            $this->expectException($expectedException);
        }
        $this->pipeline->pipe($middleware);
        $reflection = new \ReflectionProperty(get_class($this->pipeline), 'queue');
        $reflection->setAccessible(true);
        /** @var \Zend\Stdlib\SplQueue $queue */
        $queue = $reflection->getValue($this->pipeline);
        $this->assertCount(1, $queue);
        $this->assertInstanceOf(PipelineInterface::class, $queue->dequeue());
    }

    /**
     * @expectedException \ZendServer\DepH\Pipeline\Exception\MissingResultException
     */
    public function testPipeWillExhaustIfNoMiddlewareAvailable()
    {
        $this->pipeline->process(null);
    }

    /**
     * @expectedException \ZendServer\DepH\Pipeline\Exception\MissingResultException
     */
    public function testPipeWillExhaustIfNoNextMiddlewareAvailable()
    {
        $this->pipeline->pipe(function ($payload, $delegate) {
            return $delegate->process($payload);
        });
        $this->pipeline->process(null);
    }

    public function testShortCircuit()
    {
        $mock = $this->getMockBuilder(ResultInterface::class)->getMock();

        $this->pipeline
            ->pipe(function ($payload, $delegate) use ($mock) {
                return $mock;
            })
            ->pipe(function ($payload, $delegate) use ($mock) {
                throw new \Exception();
            });
        $result = $this->pipeline->process(null);
        $this->assertSame($result, $mock);
    }

    public function testPayloadIsPassedThrough()
    {
        /** @var ResultInterface $mock */
        $mock = $this->getMockBuilder(ResultInterface::class)->getMock();
        $test = $this;

        $this->pipeline
            ->pipe(function ($payload, $delegate) use ($mock) {
                return $delegate->process($payload);
            })
            ->pipe(function ($payload, $delegate) use ($mock, $test) {
                $test->assertSame('payload', $payload);

                return $mock;
            });
        $result = $this->pipeline->process('payload');
        $this->assertSame($result, $mock);
    }

    public function payloadTypeDataProvider(): array
    {
        $payloadObj = new Payload();
        $result = $this->getMockBuilder(ResultInterface::class)->getMock();
        $test = $this;

        return [
            'payload int'    => [
                1234,
                function ($payload, $delegate) use ($test, $payloadObj, $result) {
                    $test->assertSame(1234, $payload);

                    return $result;
                },
            ],
            'payload float'  => [
                12.34,
                function ($payload, $delegate) use ($test, $result) {
                    $test->assertSame(12.34, $payload);

                    return $result;
                },
            ],
            'payload string' => [
                'foobar',
                function ($payload, $delegate) use ($test, $result) {
                    $test->assertSame('foobar', $payload);

                    return $result;
                },
            ],
            'payload bool'   => [
                true,
                function ($payload, $delegate) use ($test, $result) {
                    $test->assertSame(true, $payload);

                    return $result;
                },
            ],
            'payload object' => [
                $payloadObj,
                function ($payload, $delegate) use ($test, $payloadObj, $result) {
                    $test->assertSame($payloadObj, $payload);

                    return $result;
                },
            ],
        ];
    }

    /**
     * @dataProvider payloadTypeDataProvider
     *
     * @param $payload
     * @param $middleware
     */
    public function testPayloadCanBeAnyType($payload, $middleware)
    {
        $this->pipeline->pipe($middleware);
        $result = $this->pipeline->process($payload);
    }

}
