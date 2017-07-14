<?php

namespace ZendServerTest\DepH;

use PHPUnit_Framework_TestSuite as TestSuite;

/**
 * Static test suite.
 */
class DeploymentSuite extends TestSuite
{

    /**
     * Constructs the test suite handler.
     */
    public function __construct()
    {
        $this->setName('DeploymentSuite');

        $this->addTestSuite('ZendServerTest\DepH\Deployment\DeploymentTest');
    }

    /**
     * Creates the suite.
     */
    public static function suite()
    {
        return new self();
    }
}

