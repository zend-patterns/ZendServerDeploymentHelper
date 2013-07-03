<?php
/**
 * DepH - Zend Server Deployment Helper
 */

namespace ZendDevOps\DepH\Params;

class ZendServer extends AbstractParamsContainer {
    
    /**
     * returns the directory to which the application is deployed 
     * 
     * @return string
     */
    public function getApplicationBaseDir() {
        $this->getEventManager()->trigger(__FUNCTION__, $this, array('envname' => 'ZS_APPLICATION_BASE_DIR'));
        return getenv('ZS_APPLICATION_BASE_DIR');
    }
    
    /**
     * In case an upgrade was performed, returns the version number 
     * of the current application
     * 
     * @return string
     */
    public function getCurrentAppVersion() {
        $this->getEventManager()->trigger(__FUNCTION__, $this, array('envname' => 'ZS_CURRENT_APP_VERSION'));
        return getenv('ZS_CURRENT_APP_VERSION');
    }
    
    /**
     * returns the PHP version that Zend Server uses
     * 
     * @return string
     */
    public function getPhpVersion() {
        $this->getEventManager()->trigger(__FUNCTION__, $this, array('envname' => 'ZS_PHP_VERSION'));
        return getenv('ZS_PHP_VERSION');
    }
    /**
     * In case a rollback was performed, 
     * returns the previous version of the application
     * 
     * @return string
     */
    public function getPreviousAppVersion() {
        $this->getEventManager()->trigger(__FUNCTION__, $this, array('envname' => 'ZS_PREVIOUS_APP_VERSION'));
        return getenv('ZS_PREVIOUS_APP_VERSION');
    }
    
    /*
     * In case a rollback was performed, 
     * returns the directory to which the application was deployed
     * 
     * @return string
     */
    public function getPreviousApplicationBaseDir() {
        $this->getEventManager()->trigger(__FUNCTION__, $this, array('envname' => 'ZS_PREVIOUS_APPLICATION_BASE_DIR'));
        return getenv('ZS_PREVIOUS_APPLICATION_BASE_DIR');
    }
    
    /*
     * When deploying in a Cluster Environment, 
     * a single node ID is chosen to perform actions 
     * that only need to be done once. If the value 
     * of this constant is set  to ‘1’ during deployment, 
     * the node is defined as the ‘run once node’.
     * The value of the env variable is casted to boolean value.
     * 
     * @return bool
     */
    public function getRunOnceNode() {
        $this->getEventManager()->trigger(__FUNCTION__, $this, array('envname' => 'ZS_RUN_ONCE_NODE'));
        return (bool) getenv('ZS_RUN_ONCE_NODE');
    }
    
    /*
     * returns the web server user group ID (UNIX only)
     * 
     * @return string
     */
    public function getWebserverGid() {
        $this->getEventManager()->trigger(__FUNCTION__, $this, array('envname' => 'ZS_WEBSERVER_GID'));
        return getenv('ZS_WEBSERVER_GID');
    }
    
    /*
     * Returns a code representing the web server type (APACHE)
     * 
     * @return string
     */
    public function getWebserverType() {
        $this->getEventManager()->trigger(__FUNCTION__, $this, array('envname' => 'ZS_WEBSERVER_TYPE'));
        return getenv('ZS_WEBSERVER_TYPE');
    }
    
    /*
     * Returns the web server user ID (UNIX only)
     * 
     * @return string
     */
    public function getWebserverUid() {
        $this->getEventManager()->trigger(__FUNCTION__, $this, array('envname' => 'ZS_WEBSERVER_UID'));
        return getenv('ZS_WEBSERVER_UID');
    }
    
    /*
     * Returns the web server version (2.2)
     * 
     * @return string
     */
    public function getWebserverVersion() {
        $this->getEventManager()->trigger(__FUNCTION__, $this, array('envname' => 'ZS_WEBSERVER_VERSION'));
        return getenv('ZS_WEBSERVER_VERSION');
    }
    
    /**
     * Returns the base URL set for deployment
     * 
     * @return string
     */
    public function getBaseUrl() {
        $this->getEventManager()->trigger(__FUNCTION__, $this, array('envname' => 'ZS_BASE_URL'));
        return getenv('ZS_BASE_URL');
    }
    
    /**
     * Returns the Application ID
     * 
     * @return string
     */
    public function getApplicationId() {
        $this->getEventManager()->trigger(__FUNCTION__, $this, array('envname' => 'ZS_APPLICATION_ID'));
        return getenv('ZS_APPLICATION_ID');
    }
}