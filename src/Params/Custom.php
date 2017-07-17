<?php
/**
 * DepH - Zend Server Deployment Helper
 */

namespace ZendServer\DepH\Params;

class Custom extends AbstractParamsContainer {
    
    /**
     * Returns the application name. Unfortunately this is determined
     * by the base url, there is no known other way to get this information. 
     * 
     * @return string
     */
    public function getApplicationName() {
        // @todo do we need event manager in this class?
        #$this->getEventManager()->trigger(__FUNCTION__, $this, array('name' => 'ZS_CUSTOM_APPLICATION_NAME'));
        $url = getenv('ZS_BASE_URL');
        $urlParts = explode(':', $url);
	    return str_replace('/', '_', trim($urlParts[1], '/'));
    }
    
    /**
     * Returns the server name. if no url is passed, it is taken from 
     * the base url.
     *
     * @return string
     */
    public function getServerName($arguments = null) {
        if (!$arguments) {
            $arguments = getenv('ZS_BASE_URL');
            // @todo doesn't work properly when installing from command line, produces sth. like http\\://hostname
            $arguments = str_replace(array('http\\:', 'https\\:'), array('http:', 'https:'), $arguments);
        }
        
        if (is_string($arguments)) $url = $arguments;
        elseif (is_array($arguments)) $url = $arguments[0];
        else throw new Exception\InvalidArgumentException('Either pass a string or an array to the Custom::getServerName() method.');
        
        return parse_url($url, PHP_URL_HOST);
    }
    
    public function getUrlWithTrailingSlash($urlArgument) {
        $url = $this->get($urlArgument);
        
        return (strrpos($url, '/') + 1 === strlen($url)) ? $url : $url . '/';
    }
    
    /**
     * Returns value custom argument defined in deployment.xml (stored
     * as env variable)
     *  
     * @param array $arguments
     * @return string
     */
    public function get($arguments) {
        if (is_string($arguments)) $name = $arguments;
        elseif (is_array($arguments)) $name = $arguments[0];
        else throw new Exception\InvalidArgumentException('Either pass a string or an array to the Custom::get() method.');
        
        $name = (stripos($name, 'ZS_') === 0) ? strtoupper($name) : 'ZS_' . strtoupper($name);
        
        $this->getEventManager()->trigger(__FUNCTION__, $this, array('envname' => $name));
        $res = getenv($name);
        if (!$res) return false;
        
        // @todo doesn't work properly when installing from command line, produces sth. like http\\://hostname
        $res = str_replace(array('http\\:', 'https\\:'), array('http:', 'https:'), $res);
        
        return $res;
    }
}