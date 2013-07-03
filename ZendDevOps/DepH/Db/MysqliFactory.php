<?php
/**
 * DepH - Zend Server Deployment Helper
 */

namespace ZendDevOps\DepH\Db;

use \Zend\ServiceManager\FactoryInterface;
use \Zend\ServiceManager\ServiceLocatorInterface;
use ZendDevOps\DepH\Log\LogAwareInterface;

class MysqliFactory implements FactoryInterface, LogAwareInterface {
    /**
     * @var ServiceLocatorInterface
     */
    private $serviceLocator;

    /**
     * @var \ZendDevOps\DepH\Log\Log
     */
    private $log;
    
    /**
     * Mandatory keys for connecting to MySQL DB
     * 
     * @var array
     */
    private $driverConfig = array(
        'username' => '',
        'password' => '',
        'database' => '',
        'hostname' => ''
    );
    
    /**
     * Searches for custom DbDriverConfig Service (array) with which connection
     * to MySQL can be established. If service not available, it tries to get
     * values from standard User Params defined in deployment.xml, which then have to be
     * DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_HOSTNAME
     * 
     * Inject Log and Template Service into instance
     *
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     */
    public function createService(ServiceLocatorInterface $serviceLocator) {
        $this->serviceLocator = $serviceLocator;
        
        try {
            $driverConfig = $serviceLocator->get('DbDriverConfig');
        }
        catch (\Exception $e) {
            $this->log->info('Driver config not found. Trying to get config from standard user params [DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_HOSTNAME]');
        }
        
        if (!isset($driverConfig)) {
            $driverConfig = $this->driverConfig;
            $driverConfigMissing = array();
            foreach (array_keys($driverConfig) as $key) {
                $method = 'get' . ucfirst($key);
                if (!$value = $this->$method()) {
                    $driverConfigMissing[] = $key;
                }
                else {
                    $driverConfig[$key] = $value;
                }
            }
            if (count($driverConfigMissing) > 0) {
                throw new \Exception('Incomplete DB credentials: ' . join(', ', $driverConfigMissing));
            }
        }

        $db = new \ZendDevOps\DepH\Db\AdapterMysqli($driverConfig);
        
        $db->setLog($this->serviceLocator->get('Log'));
        $db->setTemplate($this->serviceLocator->get('Template'));
        $db->setPath($this->serviceLocator->get('Path'));
        $db->setShell($this->serviceLocator->get('Shell'));
        
        return $db;
    }
    
    /**
     * @see \ZendDevOps\DepH\Log\LogAwareInterface::setLog()
     */
    public function setLog(\ZendDevOps\DepH\Log\Log $log) {
        $this->log = $log;
    }

    /**
     * Retrieves DB Username defined in deployment.xml. If nothing is found false is returned
     * 
     * @return string | boolean
     */
    public function getUsername() {
        $params = $this->serviceLocator->get('Params');
        try {
            return $params->get('db_username');
        }
        catch (\Exception $e) {
            $this->log->info('Cannot find username in env variables / user parameters from deployment.xml (searched for DB_USERNAME');
            return false;
        }
    }
    
    /**
     * Retrieves DB Password defined in deployment.xml. If nothing is found false is returned
     * 
     * @return string | boolean
     */
    public function getPassword() {
        $params = $this->serviceLocator->get('Params');
        try {
            return $params->get('db_password');
        }
        catch (\Exception $e) {
            $this->log->info('Cannot find password in env variables / user parameters from deployment.xml (searched for DB_PASSWORD');
            return false;
        }
    }
    
    /**
     * Retrieves DB Database defined in deployment.xml. If nothing is found false is returned
     * 
     * @return string | boolean
     */
    public function getDatabase() {
        $params = $this->serviceLocator->get('Params');
        try {
            return $params->get('db_database');
        }
        catch (\Exception $e) {
            $this->log->info('Cannot find password in env variables / user parameters from deployment.xml (searched for DB_DATABASE');
            return false;
        }
    }
    
    /**
     * Retrieves DB Hostname defined in deployment.xml. If nothing is found false is returned
     * 
     * @return string | boolean
     */
    public function getHostname() {
        $params = $this->serviceLocator->get('Params');
        try {
            return $params->get('db_hostname');
        }
        catch (\Exception $e) {
            $this->log->info('Cannot find password in env variables / user parameters from deployment.xml (searched for DB_HOSTNAME');
            return false;
        }
    }
}