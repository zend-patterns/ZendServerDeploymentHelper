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

/**
 * Interface ResultInterface
 * @package ZendServer\DepH\Pipeline
 */
interface ResultInterface
{

    const EXIT_SUCCESS = 0;
    const EXIT_ERROR = 1;

    /**
     * Gets the result status code.
     *
     * @return int Status code.
     */
    public function getStatusCode(): int;

    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated status and reason phrase.
     *
     * @param int    $code
     * @param string $reasonPhrase
     *
     * @return ResultInterface
     */
    public function withStatus(int $code, string $reasonPhrase = ''): ResultInterface;

    /**
     * Gets the result reason phrase associated with the status code.
     *
     * @return string
     */
    public function getReasonPhrase(): string;

    /**
     * Gets the body of the message.
     *
     * @return StreamInterface Returns the body as a stream.
     */
    public function getBody(): StreamInterface;

    /**
     * Return an instance with the specified message body.
     *
     * The body MUST be a StreamInterface object.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new body stream.
     *
     * @param StreamInterface $body Body.
     *
     * @return ResultInterface
     * @throws \InvalidArgumentException When the body is not valid.
     */
    public function withBody(StreamInterface $body): ResultInterface;
}