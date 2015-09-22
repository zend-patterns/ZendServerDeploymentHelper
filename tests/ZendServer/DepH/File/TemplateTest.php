<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/bootstrap.php';

use \ZendServer\DepH\File\Template;
use Zend\EventManager\EventManager;

/**
 * Template test case.
 */
class TemplateTest extends PHPUnit_Framework_TestCase
{

    /**
     *
     * @var Template
     */
    private $Template;
    
    private $tmpTestDir;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        
        $this->Template = new Template();
        $em = new EventManager();
        $this->Template->setEventManager($em);
        
        $log = new \ZendServer\DepH\Log\Log;
        $log->addWriter(new \Zend\Log\Writer\Mock());
        $this->Template->setLog($log);
        
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
        $this->Template = null;
        
        parent::tearDown();
    }

    /**
     * Constructs the test case.
     */
    public function __construct()
    {
    }

    /**
     * Tests Template->crit()
     * 
     * @expectedException ZendServer\DepH\File\Exception\RuntimeException
     */
    public function testCrit()
    {
        $event = Mockery::mock('\Zend\EventManager\Event');
        $event->shouldReceive('getParam')
            ->with('msg')
            ->andReturn('crit');
        
        $this->Template->crit($event);
    }

    /**
     * Tests Template->write()
     */
    public function testWrite()
    {
        $tplFilename = dirname($this->tmpTestDir) . '/my.tpl';
        $filename = $this->tmpTestDir . '/my.txt';
        $this->Template->write(
            $tplFilename,
            $filename,
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
    }
    
    /**
     * Tests Template->write()
     * 
     * @expectedException ZendServer\DepH\File\Exception\RuntimeException
     */
    public function testWrite_invalidTplFile()
    {
        $tplFilename = 'idonotexist.tpl';
        $filename = $this->tmpTestDir . '/my.txt';
        $this->Template->write(
            $tplFilename,
            $filename
        );
    }
    
    /**
     * Tests Template->write()
     *
     * @expectedException ZendServer\DepH\File\Exception\RuntimeException
     */
    public function testWrite_invalidTargetFile()
    {
        $tplFilename = dirname($this->tmpTestDir) . '/my.tpl';
        $filename = $this->tmpTestDir. 'i/do/not/exist.txt';
        PHPUnit_Framework_Error_Warning::$enabled = false;
        $this->Template->write(
            $tplFilename,
            $filename
        );
        PHPUnit_Framework_Error_Warning::$enabled = true;
    }
    
    /**
     * Tests Template->dryRun()
     */
    public function testDryRun()
    {      
        $tplFilename = dirname($this->tmpTestDir) . '/my.tpl';
        $content = $this->Template->dryRun(
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
        
        foreach (explode("\n", trim($content)) as $key => $line) {
            $this->assertEquals($res[$key], trim($line));
        }
    }
    
    /**
     * Tests Template->dryRun()
     * 
     * @expectedException ZendServer\DepH\File\Exception\RuntimeException
     */
    public function testDryRun_invalidTplFile()
    {
        $tplFilename = $tplFilename = 'idonotexist.tpl';
        $this->Template->dryRun(
            $tplFilename
        );
    }
}

