<?php
/**
 * DepH - Zend Server Deployment Helper
 */

namespace ZendDevOps\DepH\Vhost;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManagerAwareInterface;
use ZendDevOps\DepH\Log\LogAwareInterface;

class Vhost implements EventManagerAwareInterface, LogAwareInterface {
    const CUSTOM_VHOST_AWARE_FILE = 'deployment_custom_vhost';
    
    /**
     * Path for the vhost template
     * 
     * @var string
     */
    private $templatePath;
    
    /**
     * 
     * @var \ZendDevOps\DepH\File\Template
     */
    private $template;
    
    /**
     * @var \ZendDevOps\DepH\SystemCall\Shell
     */
    private $shell;
    
    /**
     * @var \ZendDevOps\DepH\Path\Path
     */
    private $path;
    
    /**
     * @var \ZendDevOps\DepH\Deployment\Deployment
     */
    private $deployment;
    
    /**
     * @var \ZendDevOps\DepH\Log\Log
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
     * @see \ZendDevOps\DepH\Log\LogAwareInterface::setLog()
     */
    public function setLog(\ZendDevOps\DepH\Log\Log $log) {
        $this->log = $log;
    }

    /**
     * @param string $templatePath
     */
    public function setTemplatePath($templatePath) {
        $this->templatePath = $templatePath;
    }
    
    /**
     * @param \ZendDevOps\DepH\File\Template $template
     */
    public function setTemplate(\ZendDevOps\DepH\File\Template $template) {
        $this->template = $template;
    }

    /**
     * @param \ZendDevOps\DepH\SystemCall\Shell $shell
     */
    public function setShell(\ZendDevOps\DepH\SystemCall\Shell $shell) {
        $this->shell = $shell;
    }

    /**
     * @param \ZendDevOps\DepH\Path\Path $path
     */
    public function setPath(\ZendDevOps\DepH\Path\Path $path) {
        $this->path = $path;
    }

    /**
     * @param \ZendDevOps\DepH\Deployment\Deployment $deployment
     */
    public function setDeployment(\ZendDevOps\DepH\Deployment\Deployment $deployment) {
        $this->deployment = $deployment;
    }

    /**
     * Path to status file of custom vhost creation. See self::write() and 
     * self::finalize()
     * 
     * @return string
     */
    public function getCustomVhostAwareFile() {
        $deployTempFolder = $this->path->getDeployTempFolder();
        return $deployTempFolder . DIRECTORY_SEPARATOR . self::CUSTOM_VHOST_AWARE_FILE;
    }
    
    /**
     * Write the new vhost file to the appropriate path after substitutions were made.
     * Also changes the access rights so that Zend Server cannot overwrite the custom file
     * 
     * @param string $templatePath
     * @param array $search
     * @param array $replace
     */
    public function write($templatePath, $search = array(), $replace = array()) {
        $this->events->trigger('write', $this);
        $this->log->info("Read template file [$templatePath] in order to overwrite vhost file.");
        
        file_put_contents($this->getCustomVhostAwareFile(), 1);
        
        $vhostFile = $this->path->getVirtualHostFile();
        $this->template->write($templatePath, $vhostFile, $search, $replace);
        $this->log->info("New vhost file [$vhostFile] is written. Access rights set to 644, so Zend Server cannot overwrite.");
        $this->shell->exec('chmod 444 ' . $vhostFile); 
        $this->log->info("Please remember to initialize class DepH in post_activate.php (as you have done in this script). This is needed to clean up the virtual host setup. any necessarry actions will be executed automatically after initializing.");
    }
    
    /**
     * in post_activate.php the access rigthts of custom vhost are reset. See comment on self::write()
     */
    public function finalize() {
        $this->events->trigger('finalize', $this);
        $vhostFile = $this->path->getVirtualHostFile();
        $this->log->info("Reset custom vhost file [$vhostFile] access rights to 644.");
        $this->shell->exec('chmod 644 ' . $vhostFile); 
    }
    
    /**
     * Check whether custom vhost can be written (only in pre_activate.php)
     * @throws \ZendDevOps\DepH\File\Exception\RuntimeException
     */
    public function allowedToWrite() {
        if (!$this->deployment->isPreActivateAction()) {
            throw new Exception\RuntimeException('Write action on custom vhosts is only allowed in pre activate action.');
        }
        
        return true;
    }
    
    /**
     * Checks whether self::finalize can be called and prepare necessary steps 
     * @throws \ZendDevOps\DepH\File\Exception\RuntimeException
     */
    public function preparingFinalize() {
        if (!is_file($this->getCustomVhostAwareFile())) return;

        if (!$this->deployment->isPostActivateAction()) {
            throw new Exception\RuntimeException('Finalize action on custom vhosts is only allowed in post activate action.');;
        }
        unlink($this->getCustomVhostAwareFile());
    }
}