<?php
/**
 * DepH - Zend Server Deployment Helper
 */
namespace ZendServer\DepH\Path;

use \ZendServer\DepH\Params\Params;
use \ZendServer\DepH\SystemCall\Shell;

class Path {
    const DIR_PREFIX = '/usr/local/zend';
    const DIR_APPS = '/usr/local/zend/var/apps';
    const SITES_D_DIR = '/usr/local/zend/etc/sites.d';
    
    const DIR_PREFIX_I5 = '/usr/local/zendsvr6';
    const DIR_APPS_I5 = '/usr/local/zendsvr6/var/apps';
    const SITES_D_DIR_I5 = '/usr/local/zendsvr6/etc/sites.d';
    
    const DEFAULT_VHOST_FILENAME = 'zend-default-vhost-80.conf'; 
    
    /**
     * @var \ZendServer\DepH\Params\Params
     */
    private $params;
    
    /**
     * @var \ZendServer\DepH\SystemCall\Shell
     */
    private $shell;
    
    /**
     * @param Params $params
     */
    public function setParams (Params $params) {
        $this->params = $params;            
    }
    
    /**
     * @param Shell $shell
     */
    public function setShell (Shell $shell) {
        $this->shell = $shell;
    }
    
    public function getSitesDDir() {
        if (strpos(php_uname(), "OS400") !== false) {
            return self::SITES_D_DIR_I5;
        }
        
        return self::SITES_D_DIR;
    }

    /**
     * Returns filename of original Zend Server vhost file
     * @return string
     */
    public function getVirtualHostFile() {
        $applicationBaseDir = $this->params->getApplicationBaseDir();
       
        if (strpos($applicationBaseDir, '__default__') > 0) {
            return $this->getSitesDDir() . '/' . self::DEFAULT_VHOST_FILENAME;
        }
        
    	$virtualHostFile = str_replace(self::DIR_APPS, '', $applicationBaseDir);
    	$virtualHostFile = str_replace($this->params->getCurrentAppVersion(), '', $virtualHostFile);
    	$virtualHostFile = trim(rtrim($virtualHostFile, '/'));
    	$virtualHostFile = str_replace('/', '_', $virtualHostFile) . '.conf';
    	$virtualHostFile = $this->getSitesDDir() . '/vhost' . $virtualHostFile;
    	
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
    
    /**
     * returns the root path in which all of the application are being deployed.
     * This dir is writable by user zend, used for persitent storage for all
     * app versions.
     *
     * @return string
     */
    public function getAppsDir() {
        if (strpos(php_uname(), "OS400") !== false) {
            return self::DIR_APPS_I5;
        }
        
        return self::DIR_APPS;
    }
    
    /**
     * creates a dir with access rights for the web server user. $dir is
     * set relative to document root.
     * Be careful with app removement: deployment user cannot remove dirs
     * that contain files written by the webserver user
     * 
     * @return string returns the absolute path of the dir
     */
    public function makeWritableDir($dir) {
        $dir = $this->params->getApplicationBaseDir() . '/' . $dir;
        $dir = str_replace('//', '/', $dir);
        
        $this->createWriteableDir($dir);
        
        return $dir;
    }
    
    /**
     * creates a dir with access rights for the web server user. $dir is
     * set relative to document root as a link! Persistent dir is stoed 
     * outside the app dir structure, so that it will not removed on app removal,
     * but the data is also available for all versions.
     * 
     * @return array of absolute path of persistent dir and linked dir in app folder
     */
    public function makePersitentWritableDir($appName, $dir, $persitentDir = null) {
        if (!$persitentDir) $persitentDir = $this->getAppsDir();
        
        $persitentDir .= "/$appName/$dir";
        $persitentDir = str_replace('//', '/', $persitentDir);
        
        $dir = $this->params->getApplicationBaseDir() . '/' . $dir;
        $dir = str_replace('//', '/', $dir);

        $this->createWriteableDir($persitentDir);
        
        $this->shell->exec("ln -s $persitentDir $dir");
        
        return array(
        	'persitentDir' => $persitentDir,
            'linkedDir' => $dir
        );
    }
    
    /**
     * actually executes the mkdir and permission actions.
     * 
     * @see \ZendServer\DepH\Path\Path::makeWritableDir
     * @see \ZendServer\DepH\Path\Path::makePersitentWritableDir
     * 
     * @param string $dir
     */
    public function createWriteableDir($dir) {
        $this->shell->exec("mkdir -p $dir");
        
        $gid = $this->params->getWebserverGid();
        
        $this->shell->exec("chgrp -R $gid $dir");
        $this->shell->exec("chmod -R 0775 $dir");
    }
}