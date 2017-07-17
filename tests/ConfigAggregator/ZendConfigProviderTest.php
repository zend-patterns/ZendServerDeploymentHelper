<?php
/**
 * @see       https://github.com/zendframework/zend-config-aggregator for the canonical source repository
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @copyright Copyright (c) 2015-2016 Mateusz Tymek (http://mateusztymek.pl)
 * @license   https://github.com/zendframework/zend-config-aggregator/blob/master/LICENSE.md New BSD License
 */
namespace ZendServerTest\DepH\ConfigAggregator;

use PHPUnit_Framework_TestCase as TestCase;
use ZendServer\DepH\ConfigAggregator\ZendConfigProvider;

class ZendConfigProviderTest extends TestCase
{
    public function testProviderLoadsConfigFromFiles()
    {
        $provider = new ZendConfigProvider(__DIR__ . '/Resources/zend-config/config.*');
        $config = $provider();
        $this->assertEquals(
            array(
                'database' => array(
                    'adapter' => 'pdo',
                    'host' => 'db.example.com',
                    'database' => 'dbproduction',
                    'user' => 'dbuser',
                    'password' => 'secret',
                )
            ),
            $config
        );
    }
}