<?php

namespace ZendServerTest\DepH\Params;

use Mockery;
use \ZendServer\DepH\Params\Params;
use Zend\EventManager\SharedEventManager;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Params test case.
 */
class ParamsTest extends TestCase
{

    /**
     *
     * @var Params
     */
    protected $Params;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp ()
    {
        parent::setUp();
        
        $sharedManager = new SharedEventManager();
        $this->Params = new Params($sharedManager);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown ()
    {
        // TODO Auto-generated ParamsTest::tearDown()
        $this->Params = null;
        
        parent::tearDown();
    }

    /**
     * Tests Params->setSharedManager()
     */
    public function testSetSharedManager ()
    {
        $sharedManager = new SharedEventManager();
        
        $this->Params->setSharedManager($sharedManager);

        $this->assertSame($sharedManager, $this->Params->getSharedManager());
    }
    
    /**
     * Tests Params->setSharedManager()
     *
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testSetSharedManagerException ()
    {

        $sharedManager = \Mockery::mock('NoSharedEventManager');

        $this->Params->setSharedManager($sharedManager);
    }

    /**
     * Tests Params->getSharedManager()
     */
    public function testGetSharedManager ()
    {
        $sharedEventManager = $this->Params->getSharedManager();
        $this->assertInstanceOf('\Zend\EventManager\SharedEventManagerInterface', $sharedEventManager);
    }

    /**
     * Tests Params->unsetSharedManager()
     * 
     * @expectedException \ZendServer\DepH\Params\Exception\MissingPropertyException
     */
    public function testUnsetSharedManager ()
    {
        $this->Params->unsetSharedManager();
        $this->Params->getSharedManager();
    }

    /**
     * Tests Params->addParamContainer()
     * 
     * @expectedException \ZendServer\DepH\Params\Exception\InvalidArgumentException
     */
    public function testAddParamContainer ()
    {
        $container = \Mockery::mock('\ZendServer\DepH\Params\ParamsContainerInterface');
        $this->Params->addParamContainer($container);
        $this->Params->addParamContainer($container);
    }

    /**
     * Tests Params->checkEnvVariable()
     */
    public function testCheckEnvVariable ()
    {
        putenv('MYENVVAR=1'); 
        $event = Mockery::mock('\Zend\EventManager\Event');
        $event->shouldReceive('getParam')
            ->with('envname')
            ->once()
            ->andReturn('MYENVVAR');    
        $this->assertTrue($this->Params->checkEnvVariable($event));
    }
    
    /**
     * Tests Params->checkEnvVariable()
     * 
     * @expectedException \ZendServer\DepH\Params\Exception\RuntimeException
     */
    public function testCheckEnvVariableException ()
    {
        $event = Mockery::mock('\Zend\EventManager\Event');
        $event->shouldReceive('getParam')
            ->with('envname')
            ->once()
            ->andReturn('IDONETEXIST');    
        $this->Params->checkEnvVariable($event);
    }

    /**
     * Tests Params->stopPropagationIfActionPreStage()
     */
    public function testStopPropagationIfActionPreStage ()
    {
        $event = Mockery::mock('\Zend\EventManager\Event');
        $deployment = Mockery::mock('\ZendServer\DepH\Deployment\Deployment');
        $deployment->shouldReceive('isPreStageAction')
            ->once()
            ->andReturn(false);
        
        $this->Params->setDeployment($deployment);
        $actual = $this->Params->stopPropagationIfActionPreStage($event);
        $this->assertEmpty($actual);
    }
    
    /**
     * Tests Params->stopPropagationIfActionPreStage()
     * 
     * @expectedException \ZendServer\DepH\Params\Exception\RuntimeException
     */
    public function testStopPropagationIfActionPreStageException ()
    {
        $event = Mockery::mock('\Zend\EventManager\Event');
        $event->shouldReceive('getParam')
            ->with('envname')
            ->once()
            ->andReturn('myMockEvent');
        $deployment = Mockery::mock('\ZendServer\DepH\Deployment\Deployment');
        $deployment->shouldReceive('isPreStageAction')
            ->once()
            ->andReturn(true);
        
        $this->Params->setDeployment($deployment);
        $actual = $this->Params->stopPropagationIfActionPreStage($event);
    }

    /**
     * Tests Params->stopPropagationIfNotUpdate()
     */
    public function testStopPropagationIfNotUpdate ()
    {
        $event = Mockery::mock('\Zend\EventManager\Event');
        $deployment = Mockery::mock('\ZendServer\DepH\Deployment\Deployment');
        $deployment->shouldReceive('isUpdate')
            ->once()
            ->andReturn(true);
        
        $this->Params->setDeployment($deployment);
        $actual = $this->Params->stopPropagationIfNotUpdate($event);
        $this->assertEmpty($actual);
    }
    
    /**
     * Tests Params->stopPropagationIfNotUpdate()
     * 
     * @expectedException \ZendServer\DepH\Params\Exception\RuntimeException
     */
    public function testStopPropagationIfNotUpdateException ()
    {
        $event = Mockery::mock('\Zend\EventManager\Event');
        $event->shouldReceive('getParam')
            ->with('envname')
            ->once()
            ->andReturn('myMockEvent');
        $deployment = Mockery::mock('\ZendServer\DepH\Deployment\Deployment');
        $deployment->shouldReceive('isUpdate')
            ->once()
            ->andReturn(false);
        
        $this->Params->setDeployment($deployment);
        $actual = $this->Params->stopPropagationIfNotUpdate($event);
    }

    /**
     * Tests Params->__call()
     */
    public function test__call ()
    {
        $container1 = \Mockery::mock('\ZendServer\DepH\Params\ParamsContainerInterface');
        $container1->shouldReceive('getEventManager')
            ->once()
            ->andReturn('resultOfMyMethodContainer1');
        $this->Params->addParamContainer($container1);
        
        $actual = $this->Params->__call('getEventManager', array());
        $this->assertEquals('resultOfMyMethodContainer1', $actual);
    }
    
    /**
     * Tests Params->__call()
     * 
     * @expectedException \ZendServer\DepH\Params\Exception\RuntimeException
     */
    public function test__callException ()
    {
        $container = \Mockery::mock('\ZendServer\DepH\Params\ParamsContainerInterface');
        $this->Params->addParamContainer($container);
        $this->Params->methodIsNotRegistered(array());
    }
}

