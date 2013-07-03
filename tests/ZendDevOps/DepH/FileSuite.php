<?php
require_once 'PHPUnit/Framework/TestSuite.php';

require_once 'File/TemplateTest.php';

/**
 * Static test suite.
 */
class FileSuite extends PHPUnit_Framework_TestSuite
{

    /**
     * Constructs the test suite handler.
     */
    public function __construct()
    {
        $this->setName('FileSuite');
        
        $this->addTestSuite('TemplateTest');
    }

    /**
     * Creates the suite.
     */
    public static function suite()
    {
        return new self();
    }
}

