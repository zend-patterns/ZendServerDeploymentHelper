<?php
/**
 * DepH - Zend Server Deployment Helper
 */

namespace ZendDevOps\DepH\Log;

class Log extends \Zend\Log\Logger{
    /**
     * Default path for logging
     * 
     * @var string
     */
    public static $logFilePath = '/usr/local/zend/var/log/app_deployment.log';
    
    /**
     * List of files which are excluded/not read in fullVerbose mode
     * @var array
     */
    private $fullVerboseExcludeList;
    
    /**
     * List of files which have already been read in fullVerbose mode 
     * @var array
     */
    private $fullVerboseFileList;
    
    /**
     * @var string
     */
    private $currentActionScriptName;
    
    /**
     * Helper variable for checking the setup for fullVerbose
     * @var boolean
     */
    private $firstTick = true;
    
    /**
     * retrieves path of app specific log file
     * 
     * @param string $appName
     * @param string $appVersion
     * @return string
     */
    public static function getLogFilePath($appName, $appVersion) {
        $filter = new \Zend\I18n\Filter\Alnum();
        $appName = $filter->filter($appName);
        $replace = 'app_' . strtolower($appName) . '_' . strtolower($appVersion) . '_';
        
        return str_replace('app_', $replace, self::$logFilePath);        
    }
    
    /**
     * @param array $fullVerboseExcludeList
     */
    public function setFullVerboseExcludeList(array $fullVerboseExcludeList) {
        $this->fullVerboseExcludeList = $fullVerboseExcludeList;
    }

    /**
     * In case of a severe issue during deployment this should be called (at the
     * beginning of the deployment trigger script) in order to log every step which
     * have been executed. So to speak a poor Code Tracing feature
     *  
     * @throws \Exception
     */
    public function fullVerbose() {
        /**
         * inspired by
         * netcoder
         * http://stackoverflow.com/questions/6294122/log-all-executed-lines
         */
        $excludeList = $this->fullVerboseExcludeList;
        $fileList = $this->fullVerboseFileList;
        $logger = $this;
        $functionName = __FUNCTION__;
        $ticksAreDeclared = false;
        $firstTick =& $this->firstTick;
        register_tick_function(function() use ($excludeList, &$fileList, $logger, &$firstTick){
            $firstTick = false;
            $backtrace = debug_backtrace();
            $line = $backtrace[0]['line'];
            $file = $backtrace[0]['file'];
            
            foreach ($excludeList as $pathPart) {
                // File is part of a framework - don't want to log these
                if (str_replace($pathPart, '', $file) != $file) return;
            }
            
            if ($file == __FILE__) return;
                
            if (!isset($fileList[$file])) {
                $fileList[$file] = file($file);
            }
            
            $code = $fileList[$file][$line-1];
            $code = str_replace("\n", '', $code);
            
            $logger->debug("$code // called in $file on line $line");
        });
        // @todo a little hack for checking whether ticks were declared
        if ($this->firstTick) {
            throw new \Exception("In order to use full verbose output, you have to set declare(ticks=1); at the beginning of your calling depoyment hook script!");
        }
    }
    
    /**
     * After this method has been called, all messages sent to log are
     * also displayed in case of an error/termination in the GUI
     */
    public function addGuiOutput() {
        $existingWriters = $this->getWriters();
        $this->setWriters(new \Zend\Stdlib\SplPriorityQueue());
        $writer = new \Zend\Log\Writer\Stream('php://output');
        
        $this->addWriter($writer);
        
        $formatter = new \Zend\Log\Formatter\Simple('');
        $writer->setFormatter($formatter);
        $this->info('');
        
        $format = '<code>%timestamp% %priorityName% (%priority%): %message% </code>' ;
        $formatter = new \Zend\Log\Formatter\Simple($format);
        $writer->setFormatter($formatter);
        
        $this->info('*** Log of script ' . $this->currentActionScriptName . ' has been started in GUI mode ***');
        $existingWriters->insert($writer, 1);
        $this->setWriters($existingWriters);
    }
    
    /**
     * @param string $currentActionScriptName
     */
    public function setCurrentActionScriptName($currentActionScriptName) {
        $this->currentActionScriptName = $currentActionScriptName;
    }
    
    /**
     * writes phpinfo() output to logfile (and to GUI if enabled by self::addGuiOutput)
     */
    public function phpInfo() {
        ob_start();
        phpinfo();
        $this->info(ob_get_contents());
        ob_end_clean();
    }
    
    /**
     * Warning message is logged
     * @param string $event
     */
    public function failure($event) {
        $this->warn('Failure: ' . $event->getParam('msg'));
    }
    
    /**
     * Emerg message is logged when Exception was thrown
     * @param string $event
     */
    public function exception($event) {
        $this->emerg('Deployment was terminated unexpectedly. Reason: ' . $event->getParam('msg'));
    }
    
    /**
     * Waring message is logged when Deployment was terminated manually
     * @param string $event
     */
    public function termination($event) {
        $this->warn('Deployment was terminated. Reason: ' . $event->getParam('msg'));
    }
    
    /**
     * @see \Zend\Log\Logger::__destruct()
     */
    public function __destruct() {
        $this->info('*** Script ' . $this->currentActionScriptName . ' ended ***');
        parent::__destruct();
    }
}