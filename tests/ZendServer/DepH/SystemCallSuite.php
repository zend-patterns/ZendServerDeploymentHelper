<?php
require_once 'PHPUnit/Framework/TestSuite.php';

require_once 'SystemCall/ShellTest.php';

/**
 * Static test suite.
 */
class SystemCallSuite extends PHPUnit_Framework_TestSuite {
	
	/**
	 * Constructs the test suite handler.
	 */
	public function __construct() {
		$this->setName ( 'SystemCallSuite' );
		
		$this->addTestSuite ( 'ShellTest' );
	}
	
	/**
	 * Creates the suite.
	 */
	public static function suite() {
		return new self ();
	}
}

