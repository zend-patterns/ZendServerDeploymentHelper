<?php
/**
 * @see       https://github.com/zendframework/zend-config-aggregator for the canonical source repository
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @copyright Copyright (c) 2015-2016 Mateusz Tymek (http://mateusztymek.pl)
 * @license   https://github.com/zendframework/zend-config-aggregator/blob/master/LICENSE.md New BSD License
 */
namespace ZendServer\DepH\ConfigAggregator;

use Zend\Config\Factory as ConfigFactory;
use Zend\Stdlib\Glob;

/**
 * Glob a set of any configuration files supported by Zend\Config\Factory as
 * configuration providers.
 */
class ZendConfigProvider
{
    /** @var string */
    private $pattern;
    /**
     * @param string $pattern Glob pattern.
     */
    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }
    /**
     * Provide configuration.
     *
     * Globs the given files, and passes the result to ConfigFactory::fromFiles
     * for purposes of returning merged configuration.
     *
     * @return array
     */
    public function __invoke()
    {
        $files = $this->glob($this->pattern);
        return ConfigFactory::fromFiles($files);
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