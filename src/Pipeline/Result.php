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
 * Class Result
 * @package ZendServer\DepH\Pipeline
 */
class Result implements ResultInterface
{

    use BodyTrait;

    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var string
     */
    private $reasonPhrase;

    /**
     * @param string|resource|StreamInterface $body   Stream identifier and/or actual stream resource
     * @param int                             $status Status code for the result, if any.
     *
     * @throws Exception\InvalidArgumentException on any invalid element.
     */
    public function __construct($body = 'php://memory', $status = 0)
    {
        $this->setStatusCode($status);
        $this->stream = $this->getStream($body, 'wb+');
    }

    /**
     * Set a valid status code.
     *
     * @param int $code
     */
    private function setStatusCode(int $code)
    {
        $this->statusCode = $code;
    }

    /**
     * Gets the result status code.
     *
     * @return int Status code.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

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
     * @return mixed
     */
    public function withStatus(int $code, string $reasonPhrase = ''): ResultInterface
    {
        $new = clone $this;
        $new->setStatusCode($code);
        $new->reasonPhrase = $reasonPhrase;

        return $new;
    }

    /**
     * Gets the result reason phrase associated with the status code.
     *
     * @return string
     */
    public function getReasonPhrase(): string
    {
        if (!$this->reasonPhrase && $this->statusCode === 0) {
            $this->reasonPhrase = 'Success';
        } elseif (!$this->reasonPhrase && $this->statusCode !== 0) {
            $this->reasonPhrase = 'Error';
        }

        return $this->reasonPhrase;
    }
}