<?php
/**
 * DepH - Zend Server Deployment Helper
 */

namespace ZendDevOps\DepH\SystemCall;

use ZendDevOps\DepH\Log\LogAwareInterface;

class Shell implements LogAwareInterface{
    
    /**
     * @var \ZendDevOps\DepH\Log\Log
     */
    private $log;
    
    /**
     * @see \ZendDevOps\DepH\Log\LogAwareInterface::setLog()
     */
    public function setLog(\ZendDevOps\DepH\Log\Log $log) {
        $this->log = $log;
    }

    /**
     * Executes command on shell
     * @param string $cmd
     * @param string $return
     * @return string
     */
    public function exec($cmd, $return = false) {
        $res = shell_exec($cmd . ' 2>&1');
        $this->log->info("Output of cmd [$cmd]");
        $this->log->info($res);
        
        if ($return) return $res;
    }
}