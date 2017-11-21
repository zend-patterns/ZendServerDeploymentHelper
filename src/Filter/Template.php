<?php
declare(strict_types=1);
/**
 * Zend Server Deployment Helper (https://github.com/zend-patterns/ZendServerDeploymentHelper)
 *
 * @link      https://github.com/zend-patterns/ZendServerDeploymentHelper for the canonical source repository
 * @copyright https://github.com/zend-patterns/ZendServerDeploymentHelper/blob/master/COPYRIGHT.md Copyright
 * @license   https://github.com/zend-patterns/ZendServerDeploymentHelper/blob/master/LICENSE.md New BSD License
 */

namespace ZendServer\DepH\Filter;

use Zend\Filter\FilterInterface;
use function preg_quote;
use function preg_replace_callback;

/**
 * Class Simple
 * @package ZendServer\DepH\Filter
 */
class Template implements FilterInterface
{

    /**
     * @var string
     */
    protected $filename;

    /**
     * @var string
     */
    protected $startTag = '{{';

    /**
     *
     * @var string
     */
    protected $endTag = '}}';

    /**
     * @var array
     */
    protected $data = [];

    /**
     * Simple constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Set tags
     *
     * @param string $start
     * @param string $end
     *
     * @return $this
     */
    public function setTags($start, $end)
    {
        $this->startTag = $start;
        $this->endTag = $end;

        return $this;
    }

    /**
     * Defined by \Zend\Filter\Filter
     *
     * Replaces template vars in string $value with the defined data
     *
     * @param  string $value
     *
     * @return string
     */
    public function filter($value)
    {
        $callback = function ($matches) {
            if (!isset($this->data[$matches[1]])) {
                return $matches[0];
            }

            return $this->data[$matches[1]];
        };
        $expression = '#' . preg_quote($this->startTag, '#') . '(.*?)' . preg_quote($this->endTag, '#') . '#';

        return preg_replace_callback($expression, $callback, $value);
    }
}