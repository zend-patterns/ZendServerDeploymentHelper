<?php
namespace ZendServer\DepH;

class ConfigProvider
{

    /**
     * @return array
     */
    public function getDependencyConfig()
    {
        return array(
            'factories'    => array(
                'ZendServer\DepH\Params\Params'         => 'ZendServer\DepH\Params\Params\ParamsFactory',
                'ZendServer\DepH\Log\Log'               => 'ZendServer\DepH\Log\LogFactory',
                'ZendServer\DepH\Path\Path'             => 'ZendServer\DepH\Path\PathFactory',
                'ZendServer\DepH\Debugger\ZendDebugger' => 'ZendServer\DepH\Debugger\ZendDebuggerFactory',
                'DB'                                    => 'ZendServer\DepH\Db\MysqliFactory',
            ),
            'invokables'   => array(
                'Zend\EventManager\EventManager',
                'Zend\EventManager\SharedEventManager',
                'ZendServer\DepH\Deployment\Deployment',
                'ZendServer\DepH\File\Template',
                'ZendServer\DepH\SystemCall\Shell',
                'ZendServer\DepH\Params\ZendServer',
                'ZendServer\DepH\Params\Custom',
                'ZendServer\DepH\Db\MysqliFactory',
            ),
            'aliases'      => array(
                'EventManager'       => 'Zend\EventManager\EventManager',
                'SharedEventManager' => 'Zend\EventManager\SharedEventManager',
                'Deployment'         => 'ZendServer\DepH\Deployment\Deployment',
                'Template'           => 'ZendServer\DepH\File\Template',
                'Shell'              => 'ZendServer\DepH\SystemCall\Shell',
                'ZSParams'           => 'ZendServer\DepH\Params\ZendServer',
                'CustomParams'       => 'ZendServer\DepH\Params\Custom',
                'MysqliFactory'      => 'ZendServer\DepH\Db\MysqliFactory',
                'Params'             => 'ZendServer\DepH\Params\Params',
                'Log'                => 'ZendServer\DepH\Log\Log',
                'Path'               => 'ZendServer\DepH\Path\Path',
                'ZendDebugger'       => 'ZendServer\DepH\Debugger\ZendDebugger',
            ),
            'initializers' => array(
                'ZendServer\DepH\ServiceManager\Initializer\EventManagerInitializer',
            ),
            'shared'       => array(
                'Zend\EventManager\EventManager' => false,
                'ZendServer\DepH\File\Template'  => false,
            ),
        );
    }

    /**
     * @return array
     */
    public function __invoke()
    {
        return array(
            'dependencies' => $this->getDependencyConfig(),
        );
    }

}