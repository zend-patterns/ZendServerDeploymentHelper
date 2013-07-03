<?php
require_once 'PHPUnit/Framework/TestSuite.php';

require_once 'Deployment/DeploymentTest.php';

/**
 * Static test suite.
 */
class DeploymentSuite extends PHPUnit_Framework_TestSuite
{

    /**
     * Constructs the test suite handler.
     */
    public function __construct()
    {
        $this->setName('DeploymentSuite');
        
        $this->addTestSuite('DeploymentTest');
    }

    /**
     * Creates the suite.
     */
    public static function suite()
    {
        return new self();
    }
}

