<?php

namespace ZendServerTest\DepH;

use PHPUnit_Framework_TestSuite as TestSuite;

/**
 * Static test suite.
 */
class FileSuite extends TestSuite
{

    /**
     * Constructs the test suite handler.
     */
    public function __construct()
    {
        $this->setName('FileSuite');

        $this->addTestSuite('ZendServerTest\DepH\File\TemplateTest');
    }

    /**
     * Creates the suite.
     */
    public static function suite()
    {
        return new self();
    }
}

