<?php
namespace ZendServerTest;

use PHPUnit_Framework_TestSuite as TestSuite;

/**
 * Static test suite.
 */
class DepHSuite extends TestSuite
{

    /**
     * Constructs the test suite handler.
     */
    public function __construct()
    {
        $this->setName('DepHSuite');

        $this->addTestSuite('ZendServerTest\DepH\DeploymentSuite');

        $this->addTestSuite('ZendServerTest\DepH\FileSuite');

        $this->addTestSuite('ZendServerTest\DepH\ParamsSuite');

        $this->addTestSuite('ZendServerTest\DepH\PathSuite');

        $this->addTestSuite('ZendServerTest\DepH\SystemCallSuite');
    }

    /**
     * Creates the suite.
     */
    public static function suite()
    {
        return new self();
    }
}

