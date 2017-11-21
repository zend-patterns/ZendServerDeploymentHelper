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
use Zend\Diactoros\PhpInputStream;
use function array_key_exists;

/**
 * Class Payload
 * @package ZendServer\DepH\Pipeline
 */
class Payload implements PayloadInterface
{

    use BodyTrait;

    /**
     * @var array
     */
    private $attributes;

    /**
     * @var array
     */
    private $envParams;

    /**
     * @var array
     */
    private $serverParams;


    /**
     * Payload constructor.
     *
     * @param array                           $serverParams Server parameters, typically from $_SERVER
     * @param string|resource|StreamInterface $body         Message body, if any.
     * @param array                           $attributes
     * @param array                           $envParams    Environment variables for the message, if any.
     */
    public function __construct(
        array $serverParams = [],
        $body = 'php://input',
        array $attributes = [],
        array $envParams = []
    ) {
        $this->attributes = $attributes;
        $this->envParams = $envParams;
        $this->serverParams = $serverParams;

        if ($body === 'php://input') {
            $body = new PhpInputStream();
        }

        $this->stream = $this->getStream($body, 'wb+');
    }

    /**
     * @inheritdoc
     */
    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    /**
     * @inheritdoc
     */
    public function getServerParam(string $name, $default = null)
    {
        if (!array_key_exists($name, $this->serverParams)) {
            return $default;
        }

        return $this->serverParams[$name];
    }

    /**
     * @inheritdoc
     */
    public function getEnvironmentParams(): array
    {
        return $this->envParams;
    }

    /**
     * @inheritdoc
     */
    public function getEnvironmentParam(string $name, $default = null)
    {
        if (!array_key_exists($name, $this->envParams)) {
            return $default;
        }

        return $this->envParams[$name];
    }

    /**
     * @inheritdoc
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @inheritdoc
     */
    public function getAttribute(string $name, $default = null)
    {
        if (!array_key_exists($name, $this->attributes)) {
            return $default;
        }

        return $this->attributes[$name];
    }

    /**
     * @inheritdoc
     */
    public function withAttribute(string $name, $value): PayloadInterface
    {
        $new = clone $this;
        $new->attributes[$name] = $value;

        return $new;
    }

    /**
     * @inheritdoc
     */
    public function withoutAttribute(string $name): PayloadInterface
    {
        $new = clone $this;
        unset($new->attributes[$name]);

        return $new;
    }
}