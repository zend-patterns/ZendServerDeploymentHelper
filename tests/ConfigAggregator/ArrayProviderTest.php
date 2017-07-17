<?php
/**
 * @see       https://github.com/zendframework/zend-config-aggregator for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @copyright Copyright (c) 2015-2016 Mateusz Tymek (http://mateusztymek.pl)
 * @license   https://github.com/zendframework/zend-config-aggregator/blob/master/LICENSE.md New BSD License
 */
namespace ZendServerTest\DepH\ConfigAggregator;

use PHPUnit_Framework_TestCase as TestCase;

use ZendServer\DepH\ConfigAggregator\ArrayProvider;

class ArrayProviderTest extends TestCase
{
    public function testProviderIsCallable()
    {
        $provider = new ArrayProvider(array());
        $this->assertInternalType('callable', $provider);
    }
    public function testProviderReturnsArrayProvidedAtConstruction()
    {
        $expected = array('foo' => 'bar');
        $provider = new ArrayProvider($expected);
        $this->assertSame($expected, $provider());
    }
}