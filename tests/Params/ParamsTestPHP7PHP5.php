<?php

namespace ZendServerTest\DepH\Params;

use Mockery;
use \ZendServer\DepH\Params\Params;
use Zend\EventManager\SharedEventManager;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Params test case.
 */
class ParamsTestPHP5PHP7 extends ParamsTestPHP5
{
    /**
     * Tests Params->setSharedManager()
     * 
     * @expectedException \TypeError
     */
    public function testSetSharedManagerException ()
    {
        $sharedManager = \Mockery::mock('NoSharedEventManager');
        
        $this->Params->setSharedManager($sharedManager);
    }
}

