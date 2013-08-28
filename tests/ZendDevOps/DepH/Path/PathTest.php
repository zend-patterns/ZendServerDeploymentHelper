<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/bootstrap.php';

use \ZendDevOps\DepH\Path\Path;

function deleteDir($dir) {
    $rdIt = new RecursiveDirectoryIterator($dir);
    while($rdIt->valid()) {
    
        if (!$rdIt->isDot()) {
            $filename = (string) $rdIt->key();
            if (is_dir($filename)) deleteDir($filename);
            else unlink($filename);
        }
    
        $rdIt->next();
    }
    rmdir($dir);
}

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
    
    private $tmpTestDir;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        
        $this->Path = new Path();
        
        $shell = new ZendDevOps\DepH\SystemCall\Shell();
        $log = \Mockery::mock('\ZendDevOps\DepH\Log\Log');
        $log->shouldReceive('info');
        $shell->setLog($log);
        
        $this->Path->setShell($shell);
        
        $this->tmpTestDir = __DIR__ . '/_files/tmp';
        
        if (is_dir($this->tmpTestDir)) {
            deleteDir($this->tmpTestDir);
        }
        mkdir($this->tmpTestDir);
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
    
    public function testGetAppsDir() {
        $actual = $this->Path->getAppsDir();
        $this->assertEquals('/usr/local/zend/var/apps', $actual);
    }
    
    public function testMakeWritableDir() {
        $params = \Mockery::mock('\ZendDevOps\DepH\Params\Params');
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
        $params = \Mockery::mock('\ZendDevOps\DepH\Params\Params');
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

