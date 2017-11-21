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

use Psr\Http\Message\StreamInterface;
use Zend\Diactoros\Stream;
use function is_resource;
use function is_string;

/**
 * Trait BodyTrait
 * @package ZendServer\DepH\Pipeline
 */
trait BodyTrait
{

    /**
     * @var StreamInterface
     */
    private $stream;

    /**
     * @param $stream
     * @param $modeIfNotInstance
     *
     * @return StreamInterface
     * @throws Exception\InvalidArgumentException
     */
    private function getStream($stream, $modeIfNotInstance): StreamInterface
    {
        if ($stream instanceof StreamInterface) {
            return $stream;
        }

        if (!is_string($stream) && !is_resource($stream)) {
            throw new Exception\InvalidArgumentException(
                'Stream must be a string stream resource identifier, '
                . 'an actual stream resource, '
                . 'or a Psr\Http\Message\StreamInterface implementation'
            );
        }

        return new Stream($stream, $modeIfNotInstance);
    }

    /**
     * @inheritdoc
     */
    public function getBody(): StreamInterface
    {
        return $this->stream;
    }

    /**
     * @inheritdoc
     */
    public function withBody(StreamInterface $body): PayloadInterface
    {
        $new = clone $this;
        $new->stream = $body;

        return $new;
    }
}