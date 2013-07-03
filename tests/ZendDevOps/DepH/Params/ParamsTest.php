<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/bootstrap.php';

use \ZendDevOps\DepH\Params\Params;
use Zend\EventManager\SharedEventManager;

require_once 'PHPUnit/Framework/TestCase.php';

/**
 * Params test case.
 */
class ParamsTest extends PHPUnit_Framework_TestCase
{

    /**
     *
     * @var Params
     */
    private $Params;

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
     * Constructs the test case.
     */
    public function __construct ()
    {
    }

    /**
     * Tests Params->setSharedManager()
     */
    public function testSetSharedManager ()
    {
        $sharedManager = new SharedEventManager();
        
        $this->Params->setSharedManager($sharedManager);
    }
    
    /**
     * Tests Params->setSharedManager()
     * 
     * @expectedException PHPUnit_Framework_Error
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
     * @expectedException ZendDevOps\DepH\Params\Exception\MissingPropertyException
     */
    public function testUnsetSharedManager ()
    {
        $this->Params->unsetSharedManager();
        $this->Params->getSharedManager();
    }

    /**
     * Tests Params->addParamContainer()
     * 
     * @expectedException ZendDevOps\DepH\Params\Exception\InvalidArgumentException
     */
    public function testAddParamContainer ()
    {
        $container = \Mockery::mock('\ZendDevOps\DepH\Params\ParamsContainerInterface');
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
     * @expectedException ZendDevOps\DepH\Params\Exception\RuntimeException
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
        $deployment = Mockery::mock('\ZendDevOps\DepH\Deployment\Deployment');
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
     * @expectedException ZendDevOps\DepH\Params\Exception\RuntimeException
     */
    public function testStopPropagationIfActionPreStageException ()
    {
        $event = Mockery::mock('\Zend\EventManager\Event');
        $event->shouldReceive('getParam')
            ->with('envname')
            ->once()
            ->andReturn('myMockEvent');
        $deployment = Mockery::mock('\ZendDevOps\DepH\Deployment\Deployment');
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
        $deployment = Mockery::mock('\ZendDevOps\DepH\Deployment\Deployment');
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
     * @expectedException ZendDevOps\DepH\Params\Exception\RuntimeException
     */
    public function testStopPropagationIfNotUpdateException ()
    {
        $event = Mockery::mock('\Zend\EventManager\Event');
        $event->shouldReceive('getParam')
            ->with('envname')
            ->once()
            ->andReturn('myMockEvent');
        $deployment = Mockery::mock('\ZendDevOps\DepH\Deployment\Deployment');
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
        $container1 = \Mockery::mock('\ZendDevOps\DepH\Params\ParamsContainerInterface');
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
     * @expectedException ZendDevOps\DepH\Params\Exception\RuntimeException
     */
    public function test__callException ()
    {
        $container = \Mockery::mock('\ZendDevOps\DepH\Params\ParamsContainerInterface');
        $this->Params->addParamContainer($container);
        $this->Params->methodIsNotRegistered(array());
    }
}

