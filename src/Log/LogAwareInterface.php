<?php
/**
 * DepH - Zend Server Deployment Helper
 */

namespace ZendServer\DepH\Log;

interface LogAwareInterface {
    /**
     * @param Log $log
     */
    public function setLog(Log $log);
} 