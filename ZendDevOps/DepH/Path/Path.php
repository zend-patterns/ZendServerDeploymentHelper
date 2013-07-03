<?php
/**
 * DepH - Zend Server Deployment Helper
 */
namespace ZendDevOps\DepH\Path;

use \ZendDevOps\DepH\Params\Params;

class Path {
    const DIR_PREFIX = '/usr/local/zend';
    const DIR_APPS = '/usr/local/zend/var/apps';
    const SITES_D_DIR = '/usr/local/zend/etc/sites.d';
    const DEFAULT_VHOST_FILENAME = 'zend-default-vhost-80.conf'; 
    
    /**
     * @var \ZendDevOps\DepH\Params\Params
     */
    private $params;
    
    /**
     * @param Params $params
     */
    public function setParams (Params $params) {
        $this->params = $params;            
    }

    /**
     * Returns filename of original Zend Server vhost file
     * @return string
     */
    public function getVirtualHostFile() {
        $applicationBaseDir = $this->params->getApplicationBaseDir();
       
        if (strpos($applicationBaseDir, '__default__') > 0) {
            return self::SITES_D_DIR. '/' . self::DEFAULT_VHOST_FILENAME;
        }
        
    	$virtualHostFile = str_replace(self::DIR_APPS, '', $applicationBaseDir);
    	$virtualHostFile = str_replace($this->params->getCurrentAppVersion(), '', $virtualHostFile);
    	$virtualHostFile = trim(rtrim($virtualHostFile, '/'));
    	$virtualHostFile = str_replace('/', '_', $virtualHostFile) . '.conf';
    	$virtualHostFile = self::SITES_D_DIR. '/vhost' . $virtualHostFile;
    	
    	return $virtualHostFile;
    }
    
    /**
     * Checks whether application is being deployed on the default site or on
     * a custom vhost
     * 
     * @return boolean
     */
    public function isDefaultSite() {
        $applicationBaseDir = $this->params->getApplicationBaseDir();
        return (bool) strpos($applicationBaseDir, '__default__') > 0;
    }
    
    /**
     * Returns a writable Temp folder (which will be available only 
     * during script lifetime)
     * 
     * @return string
     */
    public function getDeployTempFolder() {
        return dirname($_SERVER['SCRIPT_FILENAME']);        
    }
    
    /**
     * returns the path in which the application will be deployed
     * 
     * @return string
     */
    public function getUrlPath() {
        $baseUrl = $this->params->getBaseUrl();
        
        if (strpos($baseUrl, 'http:///') === 0) {
            $baseUrl = str_replace('http:///', 'http://xyz/', $baseUrl);
        }
        
        if (($path = parse_url($baseUrl, PHP_URL_PATH)) == null) {
            $path = '/';
        }
        
        return $path;
    }
}