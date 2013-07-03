<?php
require_once 'PHPUnit/Framework/TestSuite.php';

require_once 'Vhost/VhostTest.php';

/**
 * Static test suite.
 */
class VhostSuite extends PHPUnit_Framework_TestSuite
{

    /**
     * Constructs the test suite handler.
     */
    public function __construct()
    {
        $this->setName('VhostSuite');
        
        $this->addTestSuite('VhostTest');
    }

    /**
     * Creates the suite.
     */
    public static function suite()
    {
        return new self();
    }
}

