<?php

namespace ZendServerTest\DepH\SystemCall;

use RecursiveDirectoryIterator;
use \ZendServer\DepH\SystemCall\Shell;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Shell test case.
 */
class ShellTest extends TestCase
{

    /**
     *
     * @var Shell
     */
    private $Shell;

    /**
     * 
     */
    private $tmpTestDir;
    
    /**
     * Prepares the environment before running a test.
     */
    protected function setUp ()
    {
        parent::setUp();
        
        $this->tmpTestDir = __DIR__ . '/_files/tmp/shelltest';
        $this->Shell = new Shell();

        $log = $this->getMockBuilder('\ZendServer\DepH\Log\Log')->getMock();
        $log->expects($this->atLeastOnce())->method('info')->with($this->anything());
        $this->Shell->setLog($log);
        
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
        mkdir($this->tmpTestDir, 0777, true);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown ()
    {
        $cmd = 'rm -r ' . $this->tmpTestDir;
        $this->Shell->exec($cmd);
        $this->Shell = null;
        
        parent::tearDown();
    }

    /**
     * Tests Shell->exec()
     */
    public function testExec ()
    {
        $cmd = "touch {$this->tmpTestDir}/abc";
        $this->Shell->exec($cmd);
        
        $cmd = "touch {$this->tmpTestDir}/xyz";
        $this->Shell->exec($cmd);
        
        $cmd = 'ls -m ' . $this->tmpTestDir;
        $res = $this->Shell->exec($cmd, true);
        
        $this->assertEquals('abc, xyz' . "\n", $res);
        
        $res = $this->Shell->exec($cmd);
        $this->assertEmpty($res);
        
        $res = $this->Shell->exec($cmd, false);
        $this->assertEmpty($res);
    }
}