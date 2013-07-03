<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/bootstrap.php';

use \ZendDevOps\DepH\Path\Path;

/**
 * Path test case.
 */
class PathTest extends PHPUnit_Framework_TestCase
{

    /**
     *
     * @var Path
     */
    private $Path;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        
        $this->Path = new Path();
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->Path = null;
        
        parent::tearDown();
    }

    /**
     * Constructs the test case.
     */
    public function __construct()
    {
    }

    /**
     * Tests Path->getVirtualHostFile()
     */
    public function testGetVirtualHostFileDefault()
    {
        $vhostFileDefault = '/usr/local/zend/etc/sites.d/zend-default-vhost-80.conf';
        
        $params = \Mockery::mock('\ZendDevOps\DepH\Params\Params');
        $params->shouldReceive(array(
            'getApplicationBaseDir' => '/usr/local/zend/var/apps/__default__/0/1.0.0',
            'getCurrentAppVersion' => '1.0.0'
        ));
        $this->Path->setParams($params);
        
        $actual = $this->Path->getVirtualHostFile();
        $this->assertEquals($vhostFileDefault, $actual);
    }
    
    /**
     * Tests Path->getVirtualHostFile()
     */
    public function testGetVirtualHostFileCustom()
    {
        $vhostFileCustom = '/usr/local/zend/etc/sites.d/vhost_myapp_0.conf';
        
        $params = \Mockery::mock('\ZendDevOps\DepH\Params\Params');
        $params->shouldReceive(array(
            'getApplicationBaseDir' => '/usr/local/zend/var/apps/myapp/0/1.0.0',
            'getCurrentAppVersion' => '1.0.0'
        ));
        $this->Path->setParams($params);
    
        $actual = $this->Path->getVirtualHostFile();
        $this->assertEquals($vhostFileCustom, $actual);
    }

    /**
     * Tests Path->isDefaultSite()
     */
    public function testIsDefaultSiteYes()
    {
        $params = \Mockery::mock('\ZendDevOps\DepH\Params\Params');
        $params->shouldReceive(array(
            'getApplicationBaseDir' => '/usr/local/zend/var/apps/__default__/0/1.0.0',
            'getCurrentAppVersion' => '1.0.0'
        ));
        $this->Path->setParams($params);
        
        $this->assertTrue(
            $this->Path->isDefaultSite()
        );
    }
    
    /**
     * Tests Path->isDefaultSite()
     */
    public function testIsDefaultSiteNo()
    {
        $params = \Mockery::mock('\ZendDevOps\DepH\Params\Params');
        $params->shouldReceive(array(
            'getApplicationBaseDir' => '/usr/local/zend/var/apps/myapp/0/1.0.0',
            'getCurrentAppVersion' => '1.0.0'
        ));
        $this->Path->setParams($params);
    
        $this->assertFalse(
            $this->Path->isDefaultSite()
        );
    }
    
    /**
     * Tests Path->getUrlPath()
     */
    public function testGetUrlPath() {
        $params = \Mockery::mock('\ZendDevOps\DepH\Params\Params');
        $params->shouldReceive(array('getBaseUrl' => 'http:///'));
        $this->Path->setParams($params);
        $actual = $this->Path->getUrlPath();
        $this->assertEquals('/', $actual);
        
        $params = \Mockery::mock('\ZendDevOps\DepH\Params\Params');
        $params->shouldReceive(array('getBaseUrl' => 'http:///mypath'));
        $this->Path->setParams($params);
        $actual = $this->Path->getUrlPath();
        $this->assertEquals('/mypath', $actual);
        
        $params = \Mockery::mock('\ZendDevOps\DepH\Params\Params');
        $params->shouldReceive(array('getBaseUrl' => 'http://myvhost/'));
        $this->Path->setParams($params);
        $actual = $this->Path->getUrlPath();
        $this->assertEquals('/', $actual);
        
        $params = \Mockery::mock('\ZendDevOps\DepH\Params\Params');
        $params->shouldReceive(array('getBaseUrl' => 'http://myvhost'));
        $this->Path->setParams($params);
        $actual = $this->Path->getUrlPath();
        $this->assertEquals('/', $actual);
        
        $params = \Mockery::mock('\ZendDevOps\DepH\Params\Params');
        $params->shouldReceive(array('getBaseUrl' => 'http://myvhost/mypath'));
        $this->Path->setParams($params);
        $actual = $this->Path->getUrlPath();
        $this->assertEquals('/mypath', $actual);
    }
}

