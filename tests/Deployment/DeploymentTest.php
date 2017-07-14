<?php

namespace ZendServerTest\DepH\Deployment;

use \ZendServer\DepH\Deployment\Deployment;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Deployment test case.
 */
class DeploymentTest extends TestCase
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
     * Tests Deployment->getCurrentAction()
     *
     * @expectedException \ZendServer\DepH\Deployment\Exception\RuntimeException
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

        $this->assertEquals(\ZendServer\DepH\Deployment\Deployment::PRE_ACTIVATE, $actual);
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
     * @expectedException \ZendServer\DepH\Deployment\Exception\RuntimeException
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
     * @expectedException \ZendServer\DepH\Deployment\Exception\RuntimeException
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
     * @expectedException \ZendServer\DepH\Deployment\Exception\RuntimeException
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
     * @expectedException \ZendServer\DepH\Deployment\Exception\RuntimeException
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
     * @expectedException \ZendServer\DepH\Deployment\Exception\RuntimeException
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

