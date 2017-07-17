<?php
/**
 * DepH - Zend Server Deployment Helper
 */

namespace ZendServer\DepH\File;

use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use ZendServer\DepH\Log\LogAwareInterface;

class Template implements EventManagerAwareInterface, LogAwareInterface
{

    /**
     * @var \ZendServer\DepH\Log\Log
     */
    private $log;

    /**
     * @var EventManagerInterface
     */
    private $events;

    /**
     * @see \Zend\EventManager\EventManagerAwareInterface::setEventManager()
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers(array(
            __CLASS__,
            get_called_class(),
        ));
        $this->events = $events;

        $this->events->attach('*', array($this, 'crit'));

        return $this;
    }

    /**
     * @see \Zend\EventManager\EventsCapableInterface::getEventManager()
     */
    public function getEventManager()
    {
        return $this->events;
    }

    /**
     * @see \ZendServer\DepH\Log\LogAwareInterface::setLog()
     */
    public function setLog(\ZendServer\DepH\Log\Log $log)
    {
        $this->log = $log;
    }

    /**
     * Should be called in case of a critical event. Thanks to the
     * Exception, Deployment process will terminate
     *
     * @param string $event
     *
     * @throws Exception\RuntimeException
     */
    public function crit($event)
    {
        $this->log->failure($event);
        throw new Exception\RuntimeException($event->getParam('msg'));
    }

    /**
     * Checks whether $origFilename is a valid path. Adds absolute path
     * if relative path is given. Returns absolute path.
     *
     * @param string $origFilename
     *
     * @return string|boolean
     */
    private function isFile($origFilename)
    {
        $filenameOptions[] = $origFilename;
        if ($origFilename[0] != '/') {
            $filenameOptions[] = SCRIPT_ROOT . '/' . $origFilename;
        }

        foreach ($filenameOptions as $filename) {
            if (is_file($filename)) {
                return $filename;
            }
        }

        return false;
    }

    /**
     * Returns content of a given file/template. False if file doesn't exists
     *
     * @param string $filename
     *
     * @return boolean|string
     */
    private function getTemplateContent($filename)
    {

        if (!$filename = $this->isFile($filename)) {
            $this->getEventManager()->trigger(__FUNCTION__, $this,
                array('msg' => "Template-File [$filename] doesn't exist."));

            return false;
        }

        return file_get_contents($filename);
    }

    /**
     * Writes data to file $filename
     *
     * @param string $filename
     * @param string $data
     */
    private function writeToTarget($filename, $data)
    {
        if (!@file_put_contents($filename, $data)) {
            $this->getEventManager()->trigger(__FUNCTION__, $this,
                array('msg' => "Target-File [$filename] is not writable."));

            return;
        }
    }

    /**
     * Takes the content of a file, substitutes values, and writes the result
     *
     * @param string $from
     * @param string $to
     * @param array  $search
     * @param array  $replace
     */
    public function write($from, $to, $search = array(), $replace = array())
    {
        $content = $this->getTemplateContent($from);
        $content = str_replace($search, $replace, $content);
        $this->writeToTarget($to, $content);
    }

    /**
     * Takes the content of a file, substitutes values and returns the new content
     *
     * @param string  $from
     * @param array   $search
     * @param array   $replace
     * @param boolean $writeToLog
     *
     * @return string
     */
    public function dryRun($from, $search = null, $replace = null, $writeToLog = true)
    {
        $content = $this->getTemplateContent($from);
        $content = str_replace($search, $replace, $content);
        if ($writeToLog) {
            $this->log->info('Content from template file after substitution: ' . $content);
        }

        return $content;
    }
}