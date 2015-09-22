<?php
require_once 'PHPUnit/Framework/TestSuite.php';

require_once 'Params/CustomTest.php';

require_once 'Params/ParamsTest.php';

require_once 'Params/ZendServerTest.php';

/**
 * Static test suite.
 */
class ParamsSuite extends PHPUnit_Framework_TestSuite
{

    /**
     * Constructs the test suite handler.
     */
    public function __construct ()
    {
        $this->setName('ParamsSuite');
        
        $this->addTestSuite('CustomTest');
        
        $this->addTestSuite('ParamsTest');
        
        $this->addTestSuite('ZendServerTest');
    }

    /**
     * Creates the suite.
     */
    public static function suite ()
    {
        return new self();
    }
}

