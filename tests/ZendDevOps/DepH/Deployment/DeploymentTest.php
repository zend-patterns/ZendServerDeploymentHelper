<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/bootstrap.php';

use \ZendDevOps\DepH\Deployment\Deployment;

/**
 * Deployment test case.
 */
class DeploymentTest extends PHPUnit_Framework_TestCase
{

    /**
     *
     * @var Deployment
     */
    private $Deployment;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        
        $this->Deployment = new Deployment();
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->Deployment = null;
        
        parent::tearDown();
    }

    /**
     * Constructs the test case.
     */
    public function __construct()
    {
    }

    /**
     * Tests Deployment->getCurrentAction()
     * 
     * @expectedException ZendDevOps\DepH\Deployment\Exception\RuntimeException
     */
    public function testGetCurrentActionException()
    {
        $this->Deployment->getCurrentAction();
    }
    
    /**
     * Tests Deployment->getCurrentAction()
     */
    public function testGetCurrentAction()
    {
        $methodToCall = 'getCurrentAction';
        $actual = require '_files/pre_activate.php';
        
        $this->assertEquals(\ZendDevOps\DepH\Deployment\Deployment::PRE_ACTIVATE, $actual);
    }

    /**
     * Tests Deployment->getCurrentActionScript()
     */
    public function testGetCurrentActionScript()
    {
        $methodToCall = 'getCurrentActionScript';
        $actual = require '_files/pre_activate.php';
        
        $this->assertEquals('pre_activate.php', $actual);
    }
    
    /**
     * Tests Deployment->getCurrentActionScript()
     * 
     * @expectedException ZendDevOps\DepH\Deployment\Exception\RuntimeException
     */
    public function testGetCurrentActionScriptException()
    {
        $this->Deployment->getCurrentActionScript();
    }

    /**
     * Tests Deployment->isPreStageAction()
     */
    public function testIsPreStageAction()
    {
        $methodToCall = 'isPreStageAction';
        $actual = require '_files/pre_activate.php';
        
        $this->assertFalse($actual);
        
        $actual = require '_files/pre_stage.php';
        
        $this->assertTrue($actual);
    }
    
    /**
     * Tests Deployment->isPreStageAction()
     * 
     * @expectedException ZendDevOps\DepH\Deployment\Exception\RuntimeException
     */
    public function testIsPreStageActionException()
    {
        $this->Deployment->isPreStageAction();
    }

    /**
     * Tests Deployment->isPostStageAction()
     */
    public function testIsPostStageAction()
    {
        $methodToCall = 'isPostStageAction';
        $actual = require '_files/pre_activate.php';
        
        $this->assertFalse($actual);
        
        $actual = require '_files/post_stage.php';
        
        $this->assertTrue($actual);
    }
    
    /**
     * Tests Deployment->isPostStageAction()
     * 
     * @expectedException ZendDevOps\DepH\Deployment\Exception\RuntimeException
     */
    public function testIsPostStageActionException()
    {
        $this->Deployment->isPostStageAction();
    }

    /**
     * Tests Deployment->isPreActivateAction()
     */
    public function testIsPreActivateAction()
    {
        $methodToCall = 'isPreActivateAction';
        $actual = require '_files/pre_activate.php';
        
        $this->assertTrue($actual);
        
        $actual = require '_files/post_stage.php';
        
        $this->assertFalse($actual);
    }
    
    /**
     * Tests Deployment->isPreActivateAction()
     * 
     * @expectedException ZendDevOps\DepH\Deployment\Exception\RuntimeException
     */
    public function testIsPreActivateActionException()
    {
        $this->Deployment->isPreActivateAction();
    }

    /**
     * Tests Deployment->isPostActivateAction()
     */
    public function testIsPostActivateAction()
    {
        $methodToCall = 'isPostActivateAction';
        $actual = require '_files/pre_activate.php';
        
        $this->assertFalse($actual);
        
        $actual = require '_files/post_activate.php';
        
        $this->assertTrue($actual);
    }
    
    /**
     * Tests Deployment->isPostActivateAction()
     * 
     * @expectedException ZendDevOps\DepH\Deployment\Exception\RuntimeException
     */
    public function testIsPostActivateActionException()
    {
        $this->Deployment->isPostActivateAction();
    }

    /**
     * Tests Deployment->isUpdate()
     */
    public function testIsUpdate()
    {
        $actual = $this->Deployment->isUpdate();
        $this->assertFalse($actual);
        
        putenv('ZS_PREVIOUS_APP_VERSION=1');
        
        $actual = $this->Deployment->isUpdate();
        $this->assertTrue($actual);
    }
}

