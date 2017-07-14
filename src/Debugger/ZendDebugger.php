<?php
/**
 * DepH - Zend Server Deployment Helper
 */

namespace ZendServer\DepH\Debugger;

use ZendServer\DepH\Log\LogAwareInterface;

class ZendDebugger implements LogAwareInterface
{
    /**
     * @var \ZendServer\DepH\Deployment\Deployment
     */
    private $deployment;
    
    /**
     * @var \ZendServer\DepH\Log\Log
     */
    private $log;
    
    /**
     * Starts a Zend debug session on client with given IP.
     * Please note, that the original call will terminate at the end of this script,
     * but before the same script is called automatically a second time in order to 
     * start the debug session. Could result in strange effects in log file and the
     * wrong order in starting the hook scripts. 
     *  
     * @param string $clientIP
     */
    public function start ($clientIP, $port = 10137)
    {
        if (getenv('QUERY_STRING') != ''){
            $this->log->info('Please note: As the execution time of a Debugging sesion can take longer than the timout for the lighthttp (responsible for deployment execution), it can happen that the following stage is executed before the current stage has finished');
            // debug already started. 
            return;
        }
        
        $trace = debug_backtrace();
        
        $params = array(
            'start_debug' => 1,
            'debug_host' => $clientIP,
            'use_remote' => 1,
            'debug_port' => $port,
            'debug_session_id' => rand(100000, 999999),
            'debug_fastfile' => 1,
            'debug_stop' => 1,
        );
        $queryString = 'QUERY_STRING="' . http_build_query($params) . '"';
        $cmd = $queryString . ' /usr/local/zend/bin/php ' . $_SERVER["PHP_SELF"];
        exec($cmd);
        
        exit();
    }
    
    /**
     * @param \ZendServer\DepH\Deployment\Deployment $deployment
     */
    public function setDeployment(\ZendServer\DepH\Deployment\Deployment $deployment) {
        $this->deployment = $deployment;
    }

    /**
     * @see \ZendServer\DepH\Log\LogAwareInterface::setLog()
     */
    public function setLog(\ZendServer\DepH\Log\Log $log) {
        $this->log = $log;
    }

    /**
     * Terminates Deployment process in any case, as it should not be deployed in
     * Debugging mode. See comment in ZendDebugger::start()
     */
    public function __destruct() {
        if (!getenv('QUERY_STRING')){
            // debug not started. 
            return;
        }
        $em = $this->deployment->getEventManager();
        $em->attach('terminate', array($this->log, 'termination'));
        $this->deployment->terminate('Deployment cannot be finished because of active Debugging mode.');
    }
}