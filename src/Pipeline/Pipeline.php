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

use Zend\Stdlib\SplQueue;

/**
 * Class Pipeline
 * @package ZendServer\DepH\Pipeline
 */
class Pipeline implements PipelineInterface
{

    /**
     * @var SplQueue
     */
    protected $queue;

    /**
     * @var MiddlewareResolverInterface
     */
    protected $resolver;

    /**
     * Constructor
     *
     * Initializes the queue.
     *
     * @param \ZendServer\DepH\Pipeline\MiddlewareResolverInterface|null $resolver
     */
    public function __construct(MiddlewareResolverInterface $resolver = null)
    {
        $this->queue = new SplQueue();
        $this->resolver = $resolver;
    }

    /**
     * @return \ZendServer\DepH\Pipeline\MiddlewareResolverInterface
     */
    public function getResolver(): MiddlewareResolverInterface
    {
        if (null === $this->resolver) {
            $this->resolver = new MiddlewareResolver();
        }

        return $this->resolver;
    }

    /**
     * @inheritdoc
     */
    public function process($payload, DelegateInterface $delegate = null): ResultInterface
    {
        $next = new Next($this->queue, $delegate);

        return $next->process($payload);
    }

    /**
     * Attach middleware to the pipeline.
     *
     * Each middleware can be associated with a particular path; if that
     * path is matched when that middleware is invoked, it will be processed;
     * otherwise it is skipped.
     *
     * No path means it should be executed every request cycle.
     *
     * A handler CAN implement MiddlewareInterface, but MUST be callable.
     *
     * @see MiddlewareInterface
     * @see Next
     *
     * @param null|callable|object $middleware Middleware
     *
     * @return self
     */
    public function pipe($middleware): Pipeline
    {
        $middleware = $this->getResolver()->resolve($middleware);
        $this->queue->enqueue($middleware);

        return $this;
    }
}