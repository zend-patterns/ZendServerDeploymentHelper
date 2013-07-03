<?php
require_once 'PHPUnit/Framework/TestSuite.php';

require_once 'Path/PathTest.php';

/**
 * Static test suite.
 */
class PathSuite extends PHPUnit_Framework_TestSuite
{

    /**
     * Constructs the test suite handler.
     */
    public function __construct()
    {
        $this->setName('PathSuite');
        
        $this->addTestSuite('PathTest');
    }

    /**
     * Creates the suite.
     */
    public static function suite()
    {
        return new self();
    }
}

