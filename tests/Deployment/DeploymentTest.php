<?php
declare(strict_types=1);
/**
 * Zend Server Deployment Helper (https://github.com/zend-patterns/ZendServerDeploymentHelper)
 *
 * @link      https://github.com/zend-patterns/ZendServerDeploymentHelper for the canonical source repository
 * @copyright https://github.com/zend-patterns/ZendServerDeploymentHelper/blob/master/COPYRIGHT.md Copyright
 * @license   https://github.com/zend-patterns/ZendServerDeploymentHelper/blob/master/LICENSE.md New BSD License
 */

namespace ZendServerTest\DepH\Deployment;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use ZendServer\DepH\Deployment\Deployment;

/**
 * Deployment test case.
 */
class DeploymentTest extends TestCase
{

    /**
     *
     * @var Deployment|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deployment;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fs;

    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $rootFs;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        $structure = [
            'local' => [
                'zend' => [
                    'etc' => [
                        'sites.d' => [
                            'zend-default-vhost-80.conf' => '',
                            'vhost_myapp_0.conf'         => '',
                        ],
                    ],
                    'var' => [
                        'apps' => [
                            '__default__' => [
                                '0' => [
                                    '1.0.0' => [
                                        'index.php' => '',
                                    ],
                                ],
                            ],
                            'myapp'       => [
                                '0' => [
                                    '1.0.0' => [
                                        'index.php' => '',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->rootFs = vfsStream::setup('usr', null, $structure);

        $this->fs = $this->getMockBuilder(Filesystem::class)->setMethods(['symlink'])->getMock();

        $this->deployment = $this->getMockBuilder(Deployment::class)
            ->setConstructorArgs([$this->fs])
            ->setMethods([
                'getZendServerBaseDir',
                'getApplicationBaseDir',
                'getCurrentAppVersion',
                'getApplicationName',
                'getBaseUrl',
                'getWebserverUid',
                'getWebserverGid',
            ])
            ->getMock();
        $this->deployment->method('getZendServerBaseDir')->willReturn(vfsStream::url('usr/local/zend'));
    }

    /**
     * Tests Deployment->getCurrentAction()
     *
     * @expectedException \ZendServer\DepH\Deployment\Exception\RuntimeException
     */
    public function testGetCurrentActionException()
    {
        $this->deployment->getCurrentAction();
    }

    /**
     * @return array
     */
    public function currentActionDataProvider()
    {
        $result = [];
        foreach (Deployment::$actionScriptNames as $action => $file) {
            $result[] = [$action, $file];
        }

        return $result;
    }

    /**
     * @dataProvider currentActionDataProvider
     * @runInSeparateProcess
     *
     * @param int    $action
     * @param string $file
     */
    public function testCurrentAction(int $action, string $file)
    {
        $methodToCall = 'getCurrentAction';
        $deployment = new Deployment();
        $actual = require "_files/${file}";

        $this->assertEquals($action, $actual);
    }

    /**
     * @return array
     */
    public function isActionDataProvider()
    {
        $result = [];
        $result[] = [
            'isPreStageAction',
            Deployment::$actionScriptNames[Deployment::PRE_STAGE],
            true,
        ];
        $result[] = [
            'isPostStageAction',
            Deployment::$actionScriptNames[Deployment::POST_STAGE],
            true,
        ];
        $result[] = [
            'isPreUnstageAction',
            Deployment::$actionScriptNames[Deployment::PRE_UNSTAGE],
            true,
        ];
        $result[] = [
            'isPostUnstageAction',
            Deployment::$actionScriptNames[Deployment::POST_UNSTAGE],
            true,
        ];
        $result[] = [
            'isPreActivateAction',
            Deployment::$actionScriptNames[Deployment::PRE_ACTIVATE],
            true,
        ];
        $result[] = [
            'isPostActivateAction',
            Deployment::$actionScriptNames[Deployment::POST_ACTIVATE],
            true,
        ];
        $result[] = [
            'isPreRollbackAction',
            Deployment::$actionScriptNames[Deployment::PRE_ROLLBACK],
            true,
        ];
        $result[] = [
            'isPostRollbackAction',
            Deployment::$actionScriptNames[Deployment::POST_ROLLBACK],
            true,
        ];
        $result[] = [
            'isPreDeactivateAction',
            Deployment::$actionScriptNames[Deployment::PRE_DEACTIVATE],
            true,
        ];
        $result[] = [
            'isPostDeactivateAction',
            Deployment::$actionScriptNames[Deployment::POST_DEACTIVATE],
            true,
        ];

        $result[] = [
            'isPreStageAction',
            Deployment::$actionScriptNames[Deployment::POST_STAGE],
            false,
        ];
        $result[] = [
            'isPostStageAction',
            Deployment::$actionScriptNames[Deployment::PRE_STAGE],
            false,
        ];
        $result[] = [
            'isPreUnstageAction',
            Deployment::$actionScriptNames[Deployment::POST_UNSTAGE],
            false,
        ];
        $result[] = [
            'isPostUnstageAction',
            Deployment::$actionScriptNames[Deployment::PRE_UNSTAGE],
            false,
        ];
        $result[] = [
            'isPreActivateAction',
            Deployment::$actionScriptNames[Deployment::POST_ACTIVATE],
            false,
        ];
        $result[] = [
            'isPostActivateAction',
            Deployment::$actionScriptNames[Deployment::PRE_ACTIVATE],
            false,
        ];
        $result[] = [
            'isPreRollbackAction',
            Deployment::$actionScriptNames[Deployment::POST_ROLLBACK],
            false,
        ];
        $result[] = [
            'isPostRollbackAction',
            Deployment::$actionScriptNames[Deployment::PRE_ROLLBACK],
            false,
        ];
        $result[] = [
            'isPreDeactivateAction',
            Deployment::$actionScriptNames[Deployment::POST_DEACTIVATE],
            false,
        ];
        $result[] = [
            'isPostDeactivateAction',
            Deployment::$actionScriptNames[Deployment::PRE_DEACTIVATE],
            false,
        ];

        return $result;
    }

    /**
     * @dataProvider isActionDataProvider
     * @runInSeparateProcess
     *
     * @param string $methodToCall
     * @param string $file
     * @param bool   $expectedResult
     */
    public function testIsAction(string $methodToCall, string $file, bool $expectedResult)
    {
        $deployment = $this->deployment;
        $actual = require "_files/${file}";

        $this->assertEquals($expectedResult, $actual);
    }

    /**
     * @return array
     */
    public function currentActionScriptDataProvider()
    {
        $result = [];
        foreach (Deployment::$actionScriptNames as $file) {
            $result[] = [$file];
        }

        return $result;
    }

    /**
     * @dataProvider currentActionScriptDataProvider
     * @runInSeparateProcess
     *
     * @param string $file
     */
    public function testGetCurrentActionScript(string $file)
    {
        $methodToCall = 'getCurrentActionScript';
        $deployment = $this->deployment;
        $actual = require "_files/${file}";

        $this->assertEquals($file, $actual);
    }

    /**
     * Tests Deployment->getCurrentActionScript()
     *
     * @expectedException \ZendServer\DepH\Deployment\Exception\RuntimeException
     */
    public function testGetCurrentActionScriptException()
    {
        $this->deployment->getCurrentActionScript();
    }

    /**
     * @dataProvider isActionDataProvider
     *
     * @expectedException \ZendServer\DepH\Deployment\Exception\RuntimeException
     */
    public function testIsActionThrowsException(string $method)
    {
        $this->deployment->$method();
    }

    /**
     * Tests Path->getVirtualHostFile()
     */
    public function testGetVirtualHostFileDefault()
    {

        $vhostFileDefault = vfsStream::url('usr/local/zend/etc/sites.d/zend-default-vhost-80.conf');

        $this->deployment->method('getApplicationBaseDir')->willReturn(vfsStream::url('usr/local/zend/var/apps/__default__/0/1.0.0'));
        $this->deployment->method('getCurrentAppVersion')->willReturn('1.0.0');

        $actual = $this->deployment->getVirtualHostFile();
        $this->assertEquals($vhostFileDefault, $actual);
    }

    /**
     * Tests Path->getVirtualHostFile()
     */
    public function testGetVirtualHostFileCustom()
    {
        $vhostFileCustom = vfsStream::url('usr/local/zend/etc/sites.d/vhost_myapp_0.conf');

        $this->deployment->method('getApplicationBaseDir')->willReturn(vfsStream::url('usr/local/zend/var/apps/myapp/0/1.0.0'));
        $this->deployment->method('getCurrentAppVersion')->willReturn('1.0.0');

        $actual = $this->deployment->getVirtualHostFile();
        $this->assertEquals($vhostFileCustom, $actual);
    }

    /**
     * Tests Path->isDefaultSite()
     */
    public function testIsDefaultSiteYes()
    {

        $this->deployment->method('getApplicationBaseDir')->willReturn(vfsStream::url('usr/local/zend/var/apps/__default__/0/1.0.0'));
        $this->deployment->method('getCurrentAppVersion')->willReturn('1.0.0');

        $this->assertTrue($this->deployment->isDefaultSite());
    }

    /**
     * Tests Path->isDefaultSite()
     */
    public function testIsDefaultSiteNo()
    {
        $this->deployment->method('getApplicationBaseDir')->willReturn('/usr/local/zend/var/apps/myapp/0/1.0.0');
        $this->deployment->method('getCurrentAppVersion')->willReturn('1.0.0');

        $this->assertFalse($this->deployment->isDefaultSite());
    }

    public function urlPathDataProvider()
    {
        return [
            ['http:///', '/'],
            ['http:///mypath', '/mypath'],
            ['http://myvhost/', '/'],
            ['http://myvhost', '/'],
            ['http://myvhost/mypath', '/mypath'],
        ];
    }

    /**
     * @dataProvider urlPathDataProvider
     * Tests Path->getUrlPath()
     */
    public function testGetUrlPath($path, $expectedResult)
    {
        $this->deployment->method('getBaseUrl')->willReturn($path);
        $actual = $this->deployment->getUrlPath();
        $this->assertEquals($expectedResult, $actual);
    }

    public function testGetAppsDir()
    {
        $actual = $this->deployment->getAppsDir();
        $this->assertEquals(vfsStream::url('usr/local/zend/var/apps'), $actual);
    }

    public function testMakeWritableDir()
    {
        $this->deployment->method('getApplicationBaseDir')->willReturn(vfsStream::url('usr/local/zend/var/apps/__default__/0/1.0.0'));
        $this->deployment->method('getWebserverUid')->willReturn(getmyuid());
        $this->deployment->method('getWebserverGid')->willReturn(getmygid());

        $absolutePath = $this->deployment->createWriteableApplicationDir('myWritableDir');

        $this->assertEquals(vfsStream::url('usr/local/zend/var/apps/__default__/0/1.0.0') . '/myWritableDir',
            $absolutePath);

        $myFile = vfsStream::url('usr/local/zend/var/apps/__default__/0/1.0.0') . '/myWritableDir/myFile';
        touch($myFile);

        $this->assertTrue(is_file($myFile));
    }

    public function testMakePersitentWritableDir()
    {
        $tmpTestDir = vfsStream::url('usr/local/zend/var/apps/__default__/0/1.0.0');

        $appName = 'myapp';
        $this->deployment->method('getApplicationBaseDir')->willReturn($tmpTestDir);
        $this->deployment->method('getWebserverGid')->willReturn(getmygid());
        $this->deployment->method('getApplicationName')->willReturn($appName);

        $this->fs->expects($this->once())->method('symlink')->with("$tmpTestDir/persitentDir/$appName/myWritableDir", "$tmpTestDir/myWritableDir");

        $absolutePaths = $this->deployment->createPersitentApplicationDir(
            'myWritableDir',
            $tmpTestDir . '/persitentDir'
        );

        $absolutePersitentPath = $absolutePaths['persitentDir'];
        $absoluteLinkedPath = $absolutePaths['linkedDir'];

        $this->assertEquals($tmpTestDir . "/persitentDir/$appName/myWritableDir", $absolutePersitentPath);
        $this->assertEquals($tmpTestDir . '/myWritableDir', $absoluteLinkedPath);
    }

}

