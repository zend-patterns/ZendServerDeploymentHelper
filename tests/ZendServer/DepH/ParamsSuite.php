<?php

namespace ZendServerTest\DepH;

use PHPUnit_Framework_TestSuite as TestSuite;

/**
 * Static test suite.
 */
class ParamsSuite extends TestSuite
{

    /**
     * Constructs the test suite handler.
     */
    public function __construct ()
    {
        $this->setName('ParamsSuite');

        $this->addTestSuite('ZendServerTest\DepH\Params\CustomTest');

        $this->addTestSuite('ZendServerTest\DepH\Params\ParamsTest');

        $this->addTestSuite('ZendServerTest\DepH\Params\ZendServerTest');
    }

    /**
     * Creates the suite.
     */
    public static function suite ()
    {
        return new self();
    }
}

