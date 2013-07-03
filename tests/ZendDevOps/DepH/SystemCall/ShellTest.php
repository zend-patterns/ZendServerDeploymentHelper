<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/bootstrap.php';

use \ZendDevOps\DepH\SystemCall\Shell;

/**
 * Shell test case.
 */
class ShellTest extends PHPUnit_Framework_TestCase
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
        $log = \Mockery::mock('\ZendDevOps\DepH\Log\Log');
        $log->shouldReceive('info');
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
        mkdir($this->tmpTestDir);
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
     * Constructs the test case.
     */
    public function __construct ()
    {
        // TODO Auto-generated constructor
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