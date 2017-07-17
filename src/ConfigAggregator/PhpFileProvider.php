<?php
/**
 * @see       https://github.com/zendframework/zend-config-aggregator for the canonical source repository
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @copyright Copyright (c) 2015-2016 Mateusz Tymek (http://mateusztymek.pl)
 * @license   https://github.com/zendframework/zend-config-aggregator/blob/master/LICENSE.md New BSD License
 */
namespace ZendServer\DepH\ConfigAggregator;

use Zend\Stdlib\Glob;

/**
 * Provide a collection of PHP files returning config arrays.
 */
class PhpFileProvider
{
    /** @var string */
    private $pattern;
    /**
     * @param string $pattern A glob pattern by which to look up config files.
     */
    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }
    /**
     * @return array
     */
    public function __invoke()
    {
        $result = array();
        foreach ($this->glob($this->pattern) as $file) {
            $result[] = include $file;
        }
        return $result;
    }

    /**
     * Return a set of filesystem items based on a glob pattern.
     *
     * Uses the zend-stdlib Glob class for cross-platform globbing, when
     * present, falling back to glob() otherwise.
     *
     * @param string $pattern
     * @return array
     */
    private function glob($pattern)
    {
        if (class_exists('Zend\Stdlib\Glob')) {
            return Glob::glob($pattern, Glob::GLOB_BRACE);
        }
        return glob($pattern, GLOB_BRACE);
    }
}