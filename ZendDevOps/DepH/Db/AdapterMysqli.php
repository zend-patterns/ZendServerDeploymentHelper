<?php
/**
 * DepH - Zend Server Deployment Helper
 */

namespace ZendDevOps\DepH\Db;

use \ZendDevOps\DepH\Log\LogAwareInterface;
use \Zend\Db\Adapter\Adapter;

class AdapterMysqli extends Adapter implements LogAwareInterface {
    /**
     * Log
     * 
     * @var \ZendDevOps\DepH\Log\Log
     */
    private $log;
    
    /**
     * Params
     * 
     * @var \ZendDevOps\DepH\Params\Params
     */
    private $params;
    
    /**
     * Template
     * 
     * @var \ZendDevOps\DepH\File\Template
     */
    private $template;
    
    /**
     * Path
     *
     * @var \ZendDevOps\DepH\Path\Path
     */
    private $path;
    
    /**
     * Shell
     *
     * @var \ZendDevOps\DepH\Shell\Shell
     */
    private $shell;
    
    /**
     * Accepts driver specification for MySQL connection
     * 
     * @param array $driver
     */
    public function __construct($driver = array()) {
        $driver['driver'] = 'mysqli';
        parent::__construct($driver);
    }
    
    /**
     * @see \ZendDevOps\DepH\Log\LogAwareInterface::setLog()
     */
    public function setLog(\ZendDevOps\DepH\Log\Log $log) {
        $this->log = $log;    
    }
    
    /**
     * @param \ZendDevOps\DepH\Params\Params $params
     */
    public function setParams(\ZendDevOps\DepH\Params\Params $params) {
        $this->params = $params;
    }
    
    /**
     * @param \ZendDevOps\DepH\File\Template $template
     */
    public function setTemplate(\ZendDevOps\DepH\File\Template $template) {
        $this->template = $template;
    } 
    
    /**
     * @param \ZendDevOps\DepH\File\Path $path
     */
    public function setPath(\ZendDevOps\DepH\Path\Path $path) {
        $this->path = $path;
    }
    
    /**
     * @param \ZendDevOps\DepH\SystemCall\Shell $shell
     */
    public function setShell(\ZendDevOps\DepH\SystemCall\Shell $shell) {
        $this->shell = $shell;
    }

    /**
     * Imports content from given file into DB. Tested with files generated
     * by mysqldump. Parameters $search and $replace substitute custom values
     * before importing
     * 
     * @param string $sqlFile filename (relative path)
     * @param array $search
     * @param array $replace
     * @throws \Exception
     */
    public function import($sqlFile, $search = array(), $replace = array()) {
        $sql = $this->template->dryRun($sqlFile, $search, $replace, false);
        $resource = $this->getDriver()->getConnection()->getResource();
        
        // @todo error handling...
        if (!$resource->multi_query($sql)) {
            throw new \Exception('Something went wrong with the query...');
        }     

        $resource->close();
    }
    
    /**
     * Imports content from given file into DB. Tested with files generated
     * by mysqldump. Parameters $search and $replace substitute custom values
     * before importing.
     * This method is using mysql client on CL.
     *
     * @param string $sqlFile filename (relative path)
     * @param array $search
     * @param array $replace
     * @throws \Exception
     */
    public function importCl($sqlFile, $search = array(), $replace = array()) {
        $filename = $this->path->getDeployTempFolder() . '/' . basename($sqlFile);
        $this->template->write($sqlFile, $filename, $search, $replace, false);
    
        $params = $this->getDriver()->getConnection()->getConnectionParameters();
        $cmd = "mysql -u {$params['username']} -p{$params['password']} -h  {$params['hostname']} {$params['database']} < $filename";
        $result = $this->shell->exec($cmd);
    }
}