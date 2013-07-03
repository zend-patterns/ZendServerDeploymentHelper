<?php
require_once 'PHPUnit/Framework/TestSuite.php';

require_once 'DepH/DeploymentSuite.php';

require_once 'DepH/FileSuite.php';

require_once 'DepH/ParamsSuite.php';

require_once 'DepH/PathSuite.php';

require_once 'DepH/SystemCallSuite.php';

require_once 'DepH/VhostSuite.php';

/**
 * Static test suite.
 */
class DepHSuite extends PHPUnit_Framework_TestSuite
{

    /**
     * Constructs the test suite handler.
     */
    public function __construct()
    {
        $this->setName('DepHSuite');
        
        $this->addTestSuite('DeploymentSuite');
        
        $this->addTestSuite('FileSuite');
        
        $this->addTestSuite('ParamsSuite');
        
        $this->addTestSuite('PathSuite');
        
        $this->addTestSuite('SystemCallSuite');
        
        $this->addTestSuite('VhostSuite');
    }

    /**
     * Creates the suite.
     */
    public static function suite()
    {
        return new self();
    }
}

