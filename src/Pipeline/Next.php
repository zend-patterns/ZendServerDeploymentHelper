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

use InvalidArgumentException;
use SplQueue;
use function get_class;
use function sprintf;

/**
 * Class Next
 * @package ZendServer\DepH\Pipeline
 */
class Next implements DelegateInterface
{

    /**
     * @var callable|DelegateInterface
     */
    private $nextDelegate;

    /**
     * @var SplQueue
     */
    private $queue;

    /**
     * Constructor.
     *
     * Clones the queue provided to allow re-use.
     *
     * @param SplQueue               $queue
     * @param null|DelegateInterface $nextDelegate Next delegate to invoke when the
     *                                             queue is exhausted.
     *
     * @throws InvalidArgumentException for a non-callable, non-delegate $done
     *     argument.
     */
    public function __construct(SplQueue $queue, DelegateInterface $nextDelegate = null)
    {
        $this->queue = clone $queue;
        $this->nextDelegate = $nextDelegate;
    }

    /**
     * @inheritdoc
     * @throws Exception\MissingResultException
     */
    public function process($payload): ResultInterface
    {
        // No middleware remains; done
        if ($this->queue->isEmpty()) {
            if ($this->nextDelegate) {
                return $this->nextDelegate->process($payload);
            }
            throw new Exception\MissingResultException(sprintf(
                'Queue provided to %s was exhausted, with no response returned',
                get_class($this)
            ));
        }

        /** @var \ZendServer\DepH\Pipeline\PipelineInterface $middleware */
        $middleware = $this->queue->dequeue();

        $response = $middleware->process($payload, $this);
        if (! $response instanceof ResultInterface) {
            throw new Exception\MissingResultException(sprintf(
                "Last middleware executed did not return a response.\nHandler: %s",
                get_class($middleware)
            ));
        }

        return $response;
    }
}