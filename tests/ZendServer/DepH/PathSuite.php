<?php

namespace ZendServerTest\DepH;

use PHPUnit_Framework_TestSuite as TestSuite;

/**
 * Static test suite.
 */
class PathSuite extends TestSuite
{

    /**
     * Constructs the test suite handler.
     */
    public function __construct()
    {
        $this->setName('PathSuite');

        $this->addTestSuite('ZendServerTest\DepH\Path\PathTest');
    }

    /**
     * Creates the suite.
     */
    public static function suite()
    {
        return new self();
    }
}

