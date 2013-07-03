<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/bootstrap.php';

use \ZendDevOps\DepH\Vhost\Vhost;
use Zend\EventManager\EventManager;

/**
 * Vhost test case.
 */
class VhostTest extends PHPUnit_Framework_TestCase
{

    /**
     *
     * @var Vhost
     */
    private $Vhost;
    
    private $tmpTestDir;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        
        $this->Vhost = new Vhost();
        
        $em = new EventManager();
        $this->Vhost->setEventManager($em);
        
        $log = new \ZendDevOps\DepH\Log\Log;
        $log->addWriter(new \Zend\Log\Writer\Mock());
        $this->Vhost->setLog($log);
        
        $this->tmpTestDir = __DIR__ . '/_files/tmp';
        
        if (is_dir($this->tmpTestDir)) {
            $rdIt = new RecursiveDirectoryIterator($this->tmpTestDir);
            while($rdIt->valid()) {
        
                if (!$rdIt->isDot()) {
                    $filename = (string) $rdIt->key();
                    unlink($filename);
                }
        
                $rdIt->next();
            }
            rmdir($this->tmpTestDir);
        }
        mkdir($this->tmpTestDir);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->Vhost = null;
        
        parent::tearDown();
    }

    /**
     * Constructs the test case.
     */
    public function __construct()
    {
    }

    /**
     * Tests Vhost->getCustomVhostAwareFile()
     */
    public function testGetCustomVhostAwareFile()
    {
        $path = \Mockery::mock('\ZendDevOps\DepH\Path\Path');
        $path->shouldReceive('getDeployTempFolder')
            ->andReturn($this->tmpTestDir);
        
        $this->Vhost->setPath($path);
        
        $actual = $this->Vhost->getCustomVhostAwareFile();
        $awareFile = $this->tmpTestDir . '/deployment_custom_vhost';
        
        $this->assertEquals($awareFile, $actual);
    }

    /**
     * Tests Vhost->write()
     */
    public function testWrite()
    {
        $tplFilename = dirname($this->tmpTestDir) . '/my.tpl';
        $filename = $this->tmpTestDir . '/virtualhostfile';
        
        $log = new \ZendDevOps\DepH\Log\Log;
        $log->addWriter(new \Zend\Log\Writer\Mock());
        
        $path = \Mockery::mock('\ZendDevOps\DepH\Path\Path');
        $path->shouldReceive(array(
            'getVirtualHostFile' => $filename,
            'getDeployTempFolder' => $this->tmpTestDir
        ));
        $this->Vhost->setPath($path);
        
        $template = new \ZendDevOps\DepH\File\Template();
        $this->Vhost->setTemplate($template);
        
        $shell = new \ZendDevOps\DepH\SystemCall\Shell();
        $shell->setLog($log);
        $this->Vhost->setShell($shell);
        
        $triggerIsCalled = false;
        
        $listener = \Mockery::mock('FooListener');
        $listener->shouldReceive('onFoo')
            ->once()
            ->andSet('foo', 'first')
            ->andReturnUsing(function () use (&$triggerIsCalled) {
                $triggerIsCalled = true;
            });
        
        $this->Vhost->getEventManager()->attach('write', array($listener, 'onFoo'));
        
        $this->Vhost->write(
            $tplFilename, 
            array('xyz', 987),
            array('qrs', 666)
        );
        
        $res = array(
            'abc',
            'qrs',
            '123',
            '666'
        );
        
        foreach (file($filename) as $key => $line) {
            $this->assertEquals($res[$key], trim($line));
        }
        
        $perm = substr(sprintf('%o', fileperms($filename)), -4);
        $this->assertEquals('0444', $perm);
        
        $this->assertTrue(file_exists($this->tmpTestDir . '/deployment_custom_vhost'));
        
        $this->assertTrue($triggerIsCalled);
    }

    /**
     * Tests Vhost->finalize()
     */
    public function testFinalize()
    {
        $filename = $this->tmpTestDir . '/virtualhostfile';
        
        $log = new \ZendDevOps\DepH\Log\Log;
        $log->addWriter(new \Zend\Log\Writer\Mock());
        
        $path = \Mockery::mock('\ZendDevOps\DepH\Path\Path');
        $path->shouldReceive(array(
            'getVirtualHostFile' => $filename,
        ));
        $this->Vhost->setPath($path);
        
        $shell = new \ZendDevOps\DepH\SystemCall\Shell();
        $shell->setLog($log);
        $this->Vhost->setShell($shell);
        
        $triggerIsCalled = false;
        
        $listener = \Mockery::mock('FooListener');
        $listener->shouldReceive('onFoo')
            ->once()
            ->andSet('foo', 'first')
            ->andReturnUsing(function () use (&$triggerIsCalled) {
                $triggerIsCalled = true;
            });
        
        $this->Vhost->getEventManager()->attach('finalize', array($listener, 'onFoo'));
        
        touch($filename);
        $this->Vhost->finalize();
        
        $perm = substr(sprintf('%o', fileperms($filename)), -4);
        $this->assertEquals('0644', $perm);
        
        $this->assertTrue($triggerIsCalled);
    }

    /**
     * Tests Vhost->allowedToWrite()
     */
    public function testAllowedToWrite()
    {
        $deployment = \Mockery::mock('\ZendDevOps\DepH\Deployment\Deployment');
        $deployment->shouldReceive('isPreActivateAction')
            ->andReturn(true);
        $this->Vhost->setDeployment($deployment);
        $actual = $this->Vhost->allowedToWrite();
        
        $this->assertTrue($actual);
    }
    
    /**
     * Tests Vhost->allowedToWrite()
     * 
     * @expectedException ZendDevOps\DepH\Vhost\Exception\RuntimeException
     */
    public function testAllowedToWriteException()
    {
        $deployment = \Mockery::mock('\ZendDevOps\DepH\Deployment\Deployment');
        $deployment->shouldReceive('isPreActivateAction')
            ->andReturn(false);
        $this->Vhost->setDeployment($deployment);
        $actual = $this->Vhost->allowedToWrite();
    }
    
    /**
     * Tests Vhost->preparingFinalize()
     */
    public function testPreparingFinalize()
    {
        $path = \Mockery::mock('\ZendDevOps\DepH\Path\Path');
        $path->shouldReceive(array(
            'getDeployTempFolder' => $this->tmpTestDir
        ));
        $this->Vhost->setPath($path);
        
        $actual = $this->Vhost->preparingFinalize();
        $this->assertNull($actual);
        
        $awareFile = $this->tmpTestDir . '/deployment_custom_vhost';
        touch($awareFile);
        
        $deployment = \Mockery::mock('\ZendDevOps\DepH\Deployment\Deployment');
        $deployment->shouldReceive('isPostActivateAction')
            ->andReturn(true);
        $this->Vhost->setDeployment($deployment);
        
        $this->Vhost->preparingFinalize();
        
        $this->assertFalse(is_file($awareFile));
    }
    
    /**
     * Tests Vhost->preparingFinalize()
     * 
     * @expectedException ZendDevOps\DepH\Vhost\Exception\RuntimeException
     */
    public function testPreparingFinalizeException()
    {
        $path = \Mockery::mock('\ZendDevOps\DepH\Path\Path');
        $path->shouldReceive(array(
            'getDeployTempFolder' => $this->tmpTestDir
        ));
        $this->Vhost->setPath($path);
    
        $deployment = \Mockery::mock('\ZendDevOps\DepH\Deployment\Deployment');
        $deployment->shouldReceive('isPostActivateAction')
            ->andReturn(false);
        $this->Vhost->setDeployment($deployment);
    
        $actual = $this->Vhost->preparingFinalize();
        $this->assertNull($actual);
        
        $awareFile = $this->tmpTestDir . '/deployment_custom_vhost';
        touch($awareFile);
        
        $this->Vhost->preparingFinalize();
    }
}

