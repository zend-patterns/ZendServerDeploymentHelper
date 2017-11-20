<?php
declare(strict_types=1);
/**
 * Zend Server Deployment Helper (https://github.com/zend-patterns/ZendServerDeploymentHelper)
 *
 * @link      https://github.com/zend-patterns/ZendServerDeploymentHelper for the canonical source repository
 * @copyright https://github.com/zend-patterns/ZendServerDeploymentHelper/blob/master/COPYRIGHT.md Copyright
 * @license   https://github.com/zend-patterns/ZendServerDeploymentHelper/blob/master/LICENSE.md New BSD License
 */

namespace ZendServer\DepH\File;

use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;
use ZendServer\DepH\Filter;
use function dirname;
use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function is_file;
use function is_readable;
use function is_writable;
use function touch;

/**
 * Class Template
 * @package ZendServer\DepH\File
 */
class Template implements EventManagerAwareInterface
{

    use EventManagerAwareTrait;

    /**
     * @param $filename
     *
     * @return bool|string The method returns the read data or false on failure.
     * @throws Exception\InvalidArgumentException if file $filename does not exist
     * @throws Exception\RuntimeException if file $value is not readable or an error while reading occurred
     */
    private function readFile($filename)
    {
        if (!is_file($filename)) {
            throw new Exception\InvalidArgumentException(sprintf('File "%s" not found or not a regular file.',
                $filename));
        }
        if (!is_readable($filename)) {
            throw new Exception\RuntimeException(sprintf('Unable to read file "%s".', $filename));
        }
        $content = file_get_contents($filename);
        if (!$content) {
            throw new Exception\RuntimeException("Problem while reading file '$filename'");
        }

        return $content;
    }

    /**
     * @param $filename
     * @param $content
     *
     * @return int The function returns the number of bytes that were written to the file.
     * @throws Exception\InvalidArgumentException if file $filename does not exist
     * @throws Exception\RuntimeException if file $filename is not writable or an error while writing occurred
     */
    private function writeFile($filename, $content): int
    {
        if (!is_dir(dirname($filename)) || !is_writable(dirname($filename))) {
            throw new Exception\InvalidArgumentException(sprintf('Unable to write file "%s".', $filename));
        }
        if (!is_writable($filename) && !touch($filename)) {
            throw new Exception\RuntimeException(sprintf('Unable to write to file "%s".', $filename));
        }
        if (!is_file($filename)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'File "%s" not found or not a regular file.',
                $filename
            ));
        }

        $result = file_put_contents($filename, $content);
        if (!$result) {
            throw new Exception\RuntimeException("Problem while writing file '$filename'");
        }

        return $result;
    }

    /**
     * Takes the content of a file, substitutes values, and writes the result
     *
     * @param string $source
     * @param string $target
     * @param array  $data
     *
     * @throws Exception\RuntimeException
     */
    public function write($source, $target, $data = [])
    {
        $content = $this->readFile($source);
        $filter = new Filter\Template($data);
        $this->writeFile($target, $filter->filter($content));
    }
}