<?php

namespace ZendServerTest\DepH\Params;

use \ZendServer\DepH\Params\Custom;
use Zend\EventManager\EventManager;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Custom test case.
 */
class CustomTest extends TestCase
{

    /**
     *
     * @var Custom
     */
    private $Custom;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp ()
    {
        parent::setUp();

        $this->Custom = new Custom();
        $em = new EventManager();
        $this->Custom->setEventManager($em);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown ()
    {
        $this->Custom = null;

        parent::tearDown();
    }

    /**
     * Tests Custom->getApplicationName()
     */
    public function testGetApplicationName ()
    {
        putenv('ZS_BASE_URL=http://my.great.app');
        $appName = $this->Custom->getApplicationName();
        $this->assertEquals('my.great.app', $appName);

        putenv('ZS_BASE_URL=http://my.great.app/');
        $appName = $this->Custom->getApplicationName();
        $this->assertEquals('my.great.app', $appName);

        putenv('ZS_BASE_URL=http://my.great.app/with/path');
        $appName = $this->Custom->getApplicationName();
        $this->assertEquals('my.great.app_with_path', $appName);
    }

    /**
     * Tests Custom->get()
     */
    public function testGet ()
    {
        $triggerIsCalled = false;

        $listener = \Mockery::mock('FooListener');
        $listener->shouldReceive('onFoo')
             ->once()
             ->andSet('foo', 'first')
             ->andReturnUsing(function () use (&$triggerIsCalled) {
                 $triggerIsCalled = true;
             });

        $this->Custom->getEventManager()->attach('get', array($listener, 'onFoo'));

        putenv('ZS_MYENV=test');

        $triggerIsCalled = false;
        $validEnv = $this->Custom->get('myenv');
        $this->assertEquals('test', $validEnv);
        $this->assertTrue($triggerIsCalled);

        $triggerIsCalled = false;
        $validEnv = $this->Custom->get('zs_myenv');
        $this->assertEquals('test', $validEnv);
        $this->assertTrue($triggerIsCalled);

        $triggerIsCalled = false;
        $validEnv = $this->Custom->get(array('myenv'));
        $this->assertEquals('test', $validEnv);
        $this->assertTrue($triggerIsCalled);

        $triggerIsCalled = false;
        $invalidEnv = $this->Custom->get('invalidenv');
        $this->assertFalse($invalidEnv);
        $this->assertTrue($triggerIsCalled);
    }

    /**
     * Tests Custom->get()
     *
     * @expectedException \InvalidArgumentException
     */
    public function testGetException ()
    {
        $invalidArg = $this->Custom->get(new \stdClass());
    }


    /**
     * Tests Custom->getServerName()
     */
    public function testGetServerName() {
        putenv('ZS_BASE_URL=http://my.great.app');
        $serverName = $this->Custom->getServerName();
        $this->assertEquals('my.great.app', $serverName);

        putenv('ZS_BASE_URL=http://my.great.app/');
        $serverName = $this->Custom->getServerName();
        $this->assertEquals('my.great.app', $serverName);

        putenv('ZS_BASE_URL=http://my.great.app/with/path');
        $serverName = $this->Custom->getServerName();
        $this->assertEquals('my.great.app', $serverName);

        $url = 'http://my.great.app';
        $serverName = $this->Custom->getServerName($url);
        $this->assertEquals('my.great.app', $serverName);

        $url = 'http://my.great.app/';
        $serverName = $this->Custom->getServerName($url);
        $this->assertEquals('my.great.app', $serverName);

        $url = 'http://my.great.app/with/path';
        $serverName = $this->Custom->getServerName($url);
        $this->assertEquals('my.great.app', $serverName);

        $url = 'http://my.great.app';
        $serverName = $this->Custom->getServerName(array($url));
        $this->assertEquals('my.great.app', $serverName);

        $url = 'http://my.great.app/';
        $serverName = $this->Custom->getServerName(array($url));
        $this->assertEquals('my.great.app', $serverName);

        $url = 'http://my.great.app/with/path';
        $serverName = $this->Custom->getServerName(array($url));
        $this->assertEquals('my.great.app', $serverName);
    }

    /**
     * Tests Custom->getServerName()
     *
     * @expectedException \InvalidArgumentException
     */
    public function testGetServerNameException() {
        $this->Custom->getServerName(new \stdClass());
    }
    
    /**
     * Test $this->Custom->getUrlWithTrailingSlash()
     */
    public function testGetUrlWithTrailingSlash() {
        putenv('ZS_URL=http://my.great.app');
        $url = $this->Custom->getUrlWithTrailingSlash('url');
        $this->assertEquals('http://my.great.app/', $url);
        
        putenv('ZS_URL=http://my.great.app/');
        $url = $this->Custom->getUrlWithTrailingSlash('url');
        $this->assertEquals('http://my.great.app/', $url);
        
        putenv('ZS_URL=http://my.great.app/with/path');
        $url = $this->Custom->getUrlWithTrailingSlash('url');
        $this->assertEquals('http://my.great.app/with/path/', $url);
        
        putenv('ZS_URL=http://my.great.app/with/path/');
        $url = $this->Custom->getUrlWithTrailingSlash('url');
        $this->assertEquals('http://my.great.app/with/path/', $url);
    }
}

