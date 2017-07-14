<?php

namespace ZendServerTest\DepH;

use PHPUnit_Framework_TestSuite as TestSuite;

/**
 * Static test suite.
 */
class SystemCallSuite extends TestSuite {

	/**
	 * Constructs the test suite handler.
	 */
	public function __construct() {
        $this->setName ( 'SystemCallSuite' );

		$this->addTestSuite ( 'ZendServerTest\DepH\SystemCall\ShellTest' );
	}
	
	/**
	 * Creates the suite.
	 */
	public static function suite() {
		return new self ();
	}
}

