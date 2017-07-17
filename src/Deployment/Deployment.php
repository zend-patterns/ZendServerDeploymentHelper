<?php
/**
 * DepH - Zend Server Deployment Helper
 */

namespace ZendServer\DepH\Deployment;

use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;

class Deployment implements EventManagerAwareInterface
{

    const PRE_STAGE = 1;
    const POST_STAGE = 2;
    const PRE_ACTIVATE = 3;
    const POST_ACTIVATE = 4;
    const PRE_ROLLBACK = 5;
    const POST_ROLLBACK = 6;
    const PRE_DEACTIVATE = 7;
    const POST_DEACTIVATE = 8;

    /**
     * @var EventManagerInterface
     */
    private $events;

    /**
     * Filenames and corresponding keys of all valid action scripts
     *
     * @var array
     */
    private $actionScriptNames = array(
        self::PRE_STAGE       => 'pre_stage.php',
        self::POST_STAGE      => 'post_stage.php',
        self::PRE_ACTIVATE    => 'pre_activate.php',
        self::POST_ACTIVATE   => 'post_activate.php',
        self::PRE_ROLLBACK    => 'pre_rollback.php',
        self::POST_ROLLBACK   => 'post_rollback.php',
        self::PRE_DEACTIVATE  => 'pre_deactivate.php',
        self::POST_DEACTIVATE => 'post_deactivate.php',
    );

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getEventManager()
    {
        if (! $this->events instanceof EventManagerInterface) {
            $this->setEventManager(new EventManager());
        }
        return $this->events;
    }

    /**
     * Retrieves the value of the constant of the currently used action
     *
     * @throws Exception\RuntimeException
     * @return int
     */
    public function getCurrentAction()
    {
        $stack = array_reverse(debug_backtrace(), true);

        foreach ($stack as $item) {
            if (!isset($item['file'])) {
                continue;
            }
            $filename = basename($item['file']);
            if (false !== $key = array_search($filename, $this->actionScriptNames)) {
                return $key;
            }
        }

        throw new Exception\RuntimeException('Method can only be called from within a Zend Server Deployment Hook script');
    }

    /**
     * Retrieves the currently used action filename
     *
     * @return string
     */
    public function getCurrentActionScript()
    {
        return $this->actionScriptNames[$this->getCurrentAction()];
    }

    /**
     * @return boolean
     */
    public function isPreStageAction()
    {
        return (self::PRE_STAGE == $this->getCurrentAction());
    }

    /**
     * @return boolean
     */
    public function isPostStageAction()
    {
        return (self::POST_STAGE == $this->getCurrentAction());
    }

    /**
     * @return boolean
     */
    public function isPreActivateAction()
    {
        return (self::PRE_ACTIVATE == $this->getCurrentAction());
    }

    /**
     * @return boolean
     */
    public function isPostActivateAction()
    {
        return (self::POST_ACTIVATE == $this->getCurrentAction());
    }

    /**
     * @return boolean
     */
    public function isUpdate()
    {
        return (bool)getenv('ZS_PREVIOUS_APP_VERSION');
    }

    /**
     * Terminates the deployment process
     *
     * @param $msg
     */
    public function terminate($msg)
    {
        $this->getEventManager()->trigger(__FUNCTION__, $this, array('msg' => $msg));

        exit(1);
    }

}