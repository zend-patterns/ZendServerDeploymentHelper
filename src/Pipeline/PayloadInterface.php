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
 * Interface PayloadInterface
 * @package ZendServer\DepH\Pipeline
 */
interface PayloadInterface
{

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
     * @return PayloadInterface
     * @throws \InvalidArgumentException When the body is not valid.
     */
    public function withBody(StreamInterface $body): PayloadInterface;

    /**
     * Retrieve server parameters.
     *
     * Retrieves data related to the incoming request environment,
     * typically derived from PHP's $_SERVER superglobal. The data IS NOT
     * REQUIRED to originate from $_SERVER.
     *
     * @return array
     */
    public function getServerParams(): array;

    /**
     * Retrieve a single derived server parameter.
     *
     * Retrieves a single derived server parameter as described in
     * getServerParams(). If the parameter has not been previously set, returns
     * the default value as provided.
     *
     * This method obviates the need for a hasServerParam() method, as it allows
     * specifying a default value to return if the parameter is not found.
     *
     * @see getServerParams()
     *
     * @param string $name    The server param name.
     * @param mixed  $default Default value to return if the param does not exist.
     *
     * @return mixed
     */
    public function getServerParam(string $name, $default = null);

    /**
     * Retrieve environment variables.
     *
     * @return array Environment variables.
     */
    public function getEnvironmentParams(): array;

    /**
     * Retrieve a single derived environment variable.
     *
     * Retrieves a single derived environment variable as described in
     * getAttributes(). If the variable has not been previously set, returns
     * the default value as provided.
     *
     * This method obviates the need for a hasEnvironmentParam() method, as it allows
     * specifying a default value to return if the variable is not found.
     *
     * @see getEnvironmentParams()
     *
     * @param string $name    The variable name.
     * @param mixed  $default Default value to return if the variable does not exist.
     *
     * @return mixed
     */
    public function getEnvironmentParam(string $name, $default = null);

    /**
     * Retrieve attributes derived from the request.
     *
     * The request "attributes" may be used to allow injection of any
     * parameters derived from the request: e.g., the results of path
     * match operations; the results of decrypting cookies; the results of
     * deserializing non-form-encoded message bodies; etc. Attributes
     * will be application and request specific, and CAN be mutable.
     *
     * @return array Attributes derived from the request.
     */
    public function getAttributes(): array;

    /**
     * Retrieve a single derived request attribute.
     *
     * Retrieves a single derived request attribute as described in
     * getAttributes(). If the attribute has not been previously set, returns
     * the default value as provided.
     *
     * This method obviates the need for a hasAttribute() method, as it allows
     * specifying a default value to return if the attribute is not found.
     *
     * @see getAttributes()
     *
     * @param string $name    The attribute name.
     * @param mixed  $default Default value to return if the attribute does not exist.
     *
     * @return mixed
     */
    public function getAttribute(string $name, $default = null);

    /**
     * Return an instance with the specified derived request attribute.
     *
     * This method allows setting a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated attribute.
     *
     * @see getAttributes()
     *
     * @param string $name  The attribute name.
     * @param mixed  $value The value of the attribute.
     *
     * @return PayloadInterface
     */
    public function withAttribute(string $name, $value): PayloadInterface;

    /**
     * Return an instance that removes the specified derived request attribute.
     *
     * This method allows removing a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the attribute.
     *
     * @see getAttributes()
     *
     * @param string $name The attribute name.
     *
     * @return PayloadInterface
     */
    public function withoutAttribute(string $name): PayloadInterface;

}