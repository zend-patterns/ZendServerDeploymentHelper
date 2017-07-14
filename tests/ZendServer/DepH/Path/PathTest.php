<?php

namespace ZendServerTest\DepH\Path;

use \ZendServer\DepH\Path\Path;
use RecursiveDirectoryIterator;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Path test case.
 */
class PathTest extends TestCase
{

    /**
     *
     * @var Path
     */
    private $Path;

    private $tmpTestDir;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->Path = new Path();

        $shell = new \ZendServer\DepH\SystemCall\Shell();
        $log = \Mockery::mock('\ZendServer\DepH\Log\Log');
        $log->shouldReceive('info');
        $shell->setLog($log);

        $this->Path->setShell($shell);

        $this->tmpTestDir = __DIR__ . '/_files/tmp';

        if (is_dir($this->tmpTestDir)) {
            $this->deleteDir($this->tmpTestDir);
        }
        mkdir($this->tmpTestDir, 0777, true);
    }

    private function deleteDir($dir) {
        $rdIt = new RecursiveDirectoryIterator($dir);
        while($rdIt->valid()) {

            if (!$rdIt->isDot()) {
                $filename = (string) $rdIt->key();
                if (is_dir($filename)) $this->deleteDir($filename);
                else unlink($filename);
            }

            $rdIt->next();
        }
        rmdir($dir);
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
        
        $params = \Mockery::mock('\ZendServer\DepH\Params\Params');
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
        
        $params = \Mockery::mock('\ZendServer\DepH\Params\Params');
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
        $params = \Mockery::mock('\ZendServer\DepH\Params\Params');
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
        $params = \Mockery::mock('\ZendServer\DepH\Params\Params');
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
        $params = \Mockery::mock('\ZendServer\DepH\Params\Params');
        $params->shouldReceive(array('getBaseUrl' => 'http:///'));
        $this->Path->setParams($params);
        $actual = $this->Path->getUrlPath();
        $this->assertEquals('/', $actual);
        
        $params = \Mockery::mock('\ZendServer\DepH\Params\Params');
        $params->shouldReceive(array('getBaseUrl' => 'http:///mypath'));
        $this->Path->setParams($params);
        $actual = $this->Path->getUrlPath();
        $this->assertEquals('/mypath', $actual);
        
        $params = \Mockery::mock('\ZendServer\DepH\Params\Params');
        $params->shouldReceive(array('getBaseUrl' => 'http://myvhost/'));
        $this->Path->setParams($params);
        $actual = $this->Path->getUrlPath();
        $this->assertEquals('/', $actual);
        
        $params = \Mockery::mock('\ZendServer\DepH\Params\Params');
        $params->shouldReceive(array('getBaseUrl' => 'http://myvhost'));
        $this->Path->setParams($params);
        $actual = $this->Path->getUrlPath();
        $this->assertEquals('/', $actual);
        
        $params = \Mockery::mock('\ZendServer\DepH\Params\Params');
        $params->shouldReceive(array('getBaseUrl' => 'http://myvhost/mypath'));
        $this->Path->setParams($params);
        $actual = $this->Path->getUrlPath();
        $this->assertEquals('/mypath', $actual);
    }
    
    public function testGetAppsDir() {
        $actual = $this->Path->getAppsDir();
        $this->assertEquals('/usr/local/zend/var/apps', $actual);
    }
    
    public function testMakeWritableDir() {
        $params = \Mockery::mock('\ZendServer\DepH\Params\Params');
        $params->shouldReceive(array(
            'getApplicationBaseDir' => $this->tmpTestDir,
            'getWebserverUid' => getmyuid(),
            'getWebserverGid' => getmygid()
        ));
        $this->Path->setParams($params);
        
        $absolutePath = $this->Path->makeWritableDir('myWritableDir');
        
        $this->assertEquals($this->tmpTestDir . '/myWritableDir', $absolutePath);
        
        $myFile = $this->tmpTestDir . '/myWritableDir/myFile';
        touch($myFile);
        
        $this->assertTrue(is_file($myFile));
    }
    
    public function testMakePersitentWritableDir() {
        $params = \Mockery::mock('\ZendServer\DepH\Params\Params');
        $params->shouldReceive(array(
            'getApplicationBaseDir' => $this->tmpTestDir,
            'getWebserverGid' => getmygid()
        ));
        $this->Path->setParams($params);
        
        $appName = 'myapp';
        
        $absolutePaths = $this->Path->makePersitentWritableDir($appName, 'myWritableDir', $this->tmpTestDir . '/persitentDir');
        
        $absolutePersitentPath= $absolutePaths['persitentDir'];
        $absoluteLinkedPath = $absolutePaths['linkedDir'];
        
        $this->assertEquals($this->tmpTestDir . "/persitentDir/$appName/myWritableDir", $absolutePersitentPath);
        $this->assertEquals($this->tmpTestDir . '/myWritableDir', $absoluteLinkedPath);
        
        $myFile = $this->tmpTestDir . '/myWritableDir/myFile';
        $myPersitentFile = $this->tmpTestDir . "/persitentDir/$appName/myWritableDir/myFile";
        touch($myFile);
        
        $this->assertTrue(is_file($myFile));
        $this->assertTrue(is_file($myPersitentFile));
    }
}

