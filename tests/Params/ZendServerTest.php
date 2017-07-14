<?php

namespace ZendServerTest\DepH\Params;

use \ZendServer\DepH\Params\ZendServer;
use Zend\EventManager\EventManager;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * ZendServer test case.
 */
class ZendServerTest extends TestCase
{

    /**
     *
     * @var ZendServer
     */
    private $ZendServer;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp ()
    {
        parent::setUp();
        
        $this->ZendServer = new ZendServer();
        $em = new EventManager();
        $this->ZendServer->setEventManager($em);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown ()
    {
        $this->ZendServer = null;
        putenv('ZS_PREVIOUS_APP_VERSION');
        putenv('ZS_BASE_URL');
        
        parent::tearDown();
    }

    /**
     * Tests ZendServer->getApplicationBaseDir()
     */
    public function testGetApplicationBaseDir ()
    {
        $this->assertFalse($this->ZendServer->getApplicationBaseDir());
        
        $triggerIsCalled = false;
        
        $listener = \Mockery::mock('FooListener');
        $listener->shouldReceive('onFoo')
             ->once()
             ->andSet('foo', 'first')->andReturnUsing(function () use (&$triggerIsCalled) { $triggerIsCalled = true; });

        $this->ZendServer->getEventManager()->attach('getApplicationBaseDir', array($listener, 'onFoo'));
        
        $dir = '/usr/local/zend/var/apps/myapp';
        putenv('ZS_APPLICATION_BASE_DIR='.$dir);
        $actual = $this->ZendServer->getApplicationBaseDir();
        $this->assertEquals($dir, $actual);
        
        $this->assertTrue($triggerIsCalled);
    }

    /**
     * Tests ZendServer->getCurrentAppVersion()
     */
    public function testGetCurrentAppVersion ()
    {
        $this->assertFalse($this->ZendServer->getCurrentAppVersion());
        
        $triggerIsCalled = false;
        
        $listener = \Mockery::mock('FooListener');
        $listener->shouldReceive('onFoo')
             ->once()
             ->andSet('foo', 'first')->andReturnUsing(function () use (&$triggerIsCalled) { $triggerIsCalled = true; });

        $this->ZendServer->getEventManager()->attach('getCurrentAppVersion', array($listener, 'onFoo'));
        
        $version = '1.0.0';
        putenv('ZS_CURRENT_APP_VERSION='.$version);
        $actual = $this->ZendServer->getCurrentAppVersion();
        $this->assertEquals($version, $actual);
        
        $this->assertTrue($triggerIsCalled);
    }

    /**
     * Tests ZendServer->getPhpVersion()
     */
    public function testGetPhpVersion ()
    {
        // TODO Auto-generated ZendServerTest->testGetPhpVersion()
        $this->markTestIncomplete("getPhpVersion test not implemented");
        
        $this->ZendServer->getPhpVersion(/* parameters */);
    }

    /**
     * Tests ZendServer->getPreviousAppVersion()
     */
    public function testGetPreviousAppVersion ()
    {
        #$this->assertFalse($this->ZendServer->getPreviousAppVersion());
        
        $triggerIsCalled = false;
        
        $listener = \Mockery::mock('FooListener');
        $listener->shouldReceive('onFoo')
             ->once()
             ->andSet('foo', 'first')->andReturnUsing(function () use (&$triggerIsCalled) { $triggerIsCalled = true; });

        $this->ZendServer->getEventManager()->attach('getPreviousAppVersion', array($listener, 'onFoo'));
        
        $version = '0.9.0';
        putenv('ZS_PREVIOUS_APP_VERSION='.$version);
        $actual = $this->ZendServer->getPreviousAppVersion();
        $this->assertEquals($version, $actual);
        
        $this->assertTrue($triggerIsCalled);
    }

    /**
     * Tests ZendServer->getPreviousApplicationBaseDir()
     */
    public function testGetPreviousApplicationBaseDir ()
    {
        $this->assertFalse($this->ZendServer->getPreviousApplicationBaseDir());
        
        $triggerIsCalled = false;
        
        $listener = \Mockery::mock('FooListener');
        $listener->shouldReceive('onFoo')
             ->once()
             ->andSet('foo', 'first')->andReturnUsing(function () use (&$triggerIsCalled) { $triggerIsCalled = true; });

        $this->ZendServer->getEventManager()->attach('getPreviousApplicationBaseDir', array($listener, 'onFoo'));
        
        $dir = '/usr/local/zend/var/apps/myapp';
        putenv('ZS_PREVIOUS_APPLICATION_BASE_DIR='.$dir);
        $actual = $this->ZendServer->getPreviousApplicationBaseDir();
        $this->assertEquals($dir, $actual);
        
        $this->assertTrue($triggerIsCalled);
    }

    /**
     * Tests ZendServer->getRunOnceNode()
     */
    public function testGetRunOnceNode ()
    {
        $this->assertFalse($this->ZendServer->getRunOnceNode());
        
        $triggerIsCalled = false;
        
        $listener = \Mockery::mock('FooListener');
        $listener->shouldReceive('onFoo')
             ->once()
             ->andSet('foo', 'first')->andReturnUsing(function () use (&$triggerIsCalled) { $triggerIsCalled = true; });

        $this->ZendServer->getEventManager()->attach('getRunOnceNode', array($listener, 'onFoo'));
        
        putenv('ZS_RUN_ONCE_NODE=true');
        $this->assertTrue($this->ZendServer->getRunOnceNode());
        
        $this->assertTrue($triggerIsCalled);
    }

    /**
     * Tests ZendServer->getWebserverGid()
     */
    public function testGetWebserverGid ()
    {
        // TODO Auto-generated ZendServerTest->testGetWebserverGid()
        $this->markTestIncomplete("getWebserverGid test not implemented");
        
        $this->ZendServer->getWebserverGid(/* parameters */);
    }

    /**
     * Tests ZendServer->getWebserverType()
     */
    public function testGetWebserverType ()
    {
        // TODO Auto-generated ZendServerTest->testGetWebserverType()
        $this->markTestIncomplete("getWebserverType test not implemented");
        
        $this->ZendServer->getWebserverType(/* parameters */);
    }

    /**
     * Tests ZendServer->getWebserverUid()
     */
    public function testGetWebserverUid ()
    {
        // TODO Auto-generated ZendServerTest->testGetWebserverUid()
        $this->markTestIncomplete("getWebserverUid test not implemented");
        
        $this->ZendServer->getWebserverUid(/* parameters */);
    }

    /**
     * Tests ZendServer->getWebserverVersion()
     */
    public function testGetWebserverVersion ()
    {
        // TODO Auto-generated ZendServerTest->testGetWebserverVersion()
        $this->markTestIncomplete("getWebserverVersion test not implemented");
        
        $this->ZendServer->getWebserverVersion(/* parameters */);
    }

    /**
     * Tests ZendServer->getBaseUrl()
     */
    public function testGetBaseUrl ()
    {
        $this->assertFalse($this->ZendServer->getBaseUrl());
        
        $triggerIsCalled = false;
        
        $listener = \Mockery::mock('FooListener');
        $listener->shouldReceive('onFoo')
             ->once()
             ->andSet('foo', 'first')->andReturnUsing(function () use (&$triggerIsCalled) { $triggerIsCalled = true; });

        $this->ZendServer->getEventManager()->attach('getBaseUrl', array($listener, 'onFoo'));
        
        $url = 'http://my.great.app';
        putenv('ZS_BASE_URL=' . $url);
        $this->assertEquals($url, $this->ZendServer->getBaseUrl());
        
        $this->assertTrue($triggerIsCalled);
    }

    /**
     * Tests ZendServer->getApplicationId()
     */
    public function testGetApplicationId ()
    {
        $this->assertFalse($this->ZendServer->getApplicationId());
        
        $triggerIsCalled = false;
        
        $listener = \Mockery::mock('FooListener');
        $listener->shouldReceive('onFoo')
             ->once()
             ->andSet('foo', 'first')->andReturnUsing(function () use (&$triggerIsCalled) { $triggerIsCalled = true; });

        $this->ZendServer->getEventManager()->attach('getApplicationId', array($listener, 'onFoo'));
        
        $appId = '42';
        putenv('ZS_APPLICATION_ID=' . $appId);
        $this->assertEquals($appId, $this->ZendServer->getApplicationId());
        
        $this->assertTrue($triggerIsCalled);
    }
}

