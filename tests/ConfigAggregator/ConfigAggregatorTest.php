<?php
/**
 * @see       https://github.com/zendframework/zend-config-aggregator for the canonical source repository
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @copyright Copyright (c) 2015-2016 Mateusz Tymek (http://mateusztymek.pl)
 * @license   https://github.com/zendframework/zend-config-aggregator/blob/master/LICENSE.md New BSD License
 */

namespace ZendServerTest\DepH\ConfigAggregator;

use org\bovigo\vfs\vfsStream;
use PHPUnit_Framework_TestCase as TestCase;
use ZendServer\DepH\ConfigAggregator\ConfigAggregator;

class ConfigAggregatorTest extends TestCase
{

    public function testConfigAggregatorRisesExceptionIfProviderClassDoesNotExist()
    {
        $this->setExpectedException('ZendServer\DepH\ConfigAggregator\Exception\InvalidConfigProviderException');
        new ConfigAggregator(array('NonExistentConfigProvider'));
    }

    public function testConfigAggregatorRisesExceptionIfProviderIsNotCallable()
    {
        $this->setExpectedException('ZendServer\DepH\ConfigAggregator\Exception\InvalidConfigProviderException');
        new ConfigAggregator(array('StdClass'));
    }

    public function testConfigAggregatorMergesConfigFromProviders()
    {
        $aggregator = new ConfigAggregator(array(
            'ZendServerTest\DepH\ConfigAggregator\Resources\FooConfigProvider',
            'ZendServerTest\DepH\ConfigAggregator\Resources\BarConfigProvider',
        ));
        $config = $aggregator->getMergedConfig();
        $this->assertEquals(array('foo' => 'bar', 'bar' => 'bat'), $config);
    }

    public function testProviderCanBeClosure()
    {
        $aggregator = new ConfigAggregator(array(
            function () {
                return array('foo' => 'bar');
            },
        ));
        $config = $aggregator->getMergedConfig();
        $this->assertEquals(array('foo' => 'bar'), $config);
    }

    public function testConfigAggregatorCanCacheConfig()
    {
        vfsStream::setup(__FUNCTION__);
        $cacheFile = vfsStream::url(__FUNCTION__) . '/expressive_config_loader';
        new ConfigAggregator(array(
            function () {
                return array('foo' => 'bar', ConfigAggregator::ENABLE_CACHE => true);
            },
        ), $cacheFile);
        $this->assertTrue(file_exists($cacheFile));
        $cachedConfig = include $cacheFile;
        $this->assertInternalType('array', $cachedConfig);
        $this->assertEquals(array('foo' => 'bar', ConfigAggregator::ENABLE_CACHE => true), $cachedConfig);
    }

    public function testConfigAggregatorCanLoadConfigFromCache()
    {
        $expected = array(
            'foo'                          => 'bar',
            ConfigAggregator::ENABLE_CACHE => true,
        );
        $root = vfsStream::setup(__FUNCTION__);
        vfsStream::newFile('expressive_config_loader')
            ->at($root)
            ->setContent('<' . '?php return ' . var_export($expected, true) . ';');
        $cacheFile = vfsStream::url(__FUNCTION__ . '/expressive_config_loader');
        $aggregator = new ConfigAggregator(array(), $cacheFile);
        $mergedConfig = $aggregator->getMergedConfig();
        $this->assertInternalType('array', $mergedConfig);
        $this->assertEquals($expected, $mergedConfig);
    }
}