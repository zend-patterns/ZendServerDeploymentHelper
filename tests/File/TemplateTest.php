<?php
declare(strict_types=1);
/**
 * Zend Server Deployment Helper (https://github.com/zend-patterns/ZendServerDeploymentHelper)
 *
 * @link      https://github.com/zend-patterns/ZendServerDeploymentHelper for the canonical source repository
 * @copyright https://github.com/zend-patterns/ZendServerDeploymentHelper/blob/master/COPYRIGHT.md Copyright
 * @license   https://github.com/zend-patterns/ZendServerDeploymentHelper/blob/master/LICENSE.md New BSD License
 */

namespace ZendServerTest\DepH\File;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use ZendServer\DepH\File\Template;

/**
 * Template test case.
 * @coversDefaultClass \ZendServer\DepH\File\Template
 */
class TemplateTest extends TestCase
{

    /**
     *
     * @var Template
     */
    private $template;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        $this->template = new Template();
        $root = vfsStream::setup('tmp');
        $content = <<<EOF
{{abc}}
{{xyz}}
{{123}}
{{987}}
EOF;
        $root->addChild(vfsStream::newFile('my.tpl')->setContent($content));
    }

    /**
     * Tests Template->write()
     *
     * @covers ::write
     * @covers ::readFile
     * @covers ::writeFile
     */
    public function testWrite()
    {

        $tplFilename = vfsStream::url('tmp/my.tpl');
        $filename = vfsStream::url('tmp/my.txt');
        $this->template->write(
            $tplFilename,
            $filename,
            [
                'xyz' => 'qrs',
                '987' => '666',
            ]
        );

        $expectedContent = <<<EOF
{{abc}}
qrs
{{123}}
666
EOF;

        $this->assertSame($expectedContent, file_get_contents(vfsStream::url('tmp/my.txt')));
    }

    /**
     * Tests Template->write()
     *
     * @covers ::write
     * @expectedException \ZendServer\DepH\File\Exception\InvalidArgumentException
     */
    public function testWriteThrowsExceptionIfSourceDoesNotExist()
    {
        $tplFilename = vfsStream::url('tmp/idonotexist.tpl');
        $filename = vfsStream::url('tmp/my.txt');
        $this->template->write(
            $tplFilename,
            $filename
        );
    }

    /**
     * Tests Template->write()
     *
     * @covers ::write
     * @covers ::writeFile
     * @expectedException \ZendServer\DepH\File\Exception\InvalidArgumentException
     */
    public function testWriteThrowsExceptionIfDestinationFileCannotCreated()
    {
        $tplFilename = vfsStream::url('tmp/my.tpl');
        $filename = vfsStream::url('tmp/i/do/not/exist.txt');

        $this->template->write(
            $tplFilename,
            $filename
        );
    }
}

