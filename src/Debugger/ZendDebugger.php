<?php
declare(strict_types=1);
/**
 * Zend Server Deployment Helper (https://github.com/zend-patterns/ZendServerDeploymentHelper)
 *
 * @link      https://github.com/zend-patterns/ZendServerDeploymentHelper for the canonical source repository
 * @copyright https://github.com/zend-patterns/ZendServerDeploymentHelper/blob/master/COPYRIGHT.md Copyright
 * @license   https://github.com/zend-patterns/ZendServerDeploymentHelper/blob/master/LICENSE.md New BSD License
 */

namespace ZendServer\DepH\Debugger;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Process\PhpProcess;
use function getenv;
use function http_build_query;
use function random_int;

/**
 * Class ZendDebugger
 * @package ZendServer\DepH\Debugger
 */
class ZendDebugger implements LoggerAwareInterface
{

    use LoggerAwareTrait;

    /**
     * @var \ZendServer\DepH\Deployment
     */
    private $deployment;

    /**
     * ZendDebugger constructor.
     *
     * @param \ZendServer\DepH\Deployment $deployment
     */
    public function __construct(Deployment $deployment)
    {
        $this->setDeployment($deployment);
    }

    /**
     * Starts a Zend debug session on client with given IP.
     * Please note, that the original call will terminate at the end of this script,
     * but before the same script is called automatically a second time in order to
     * start the debug session. Could result in strange effects in log file and the
     * wrong order in starting the hook scripts.
     *
     * @param string $clientIP
     * @param int    $port
     */
    public function start($clientIP, $port = 10137)
    {
        if (getenv('QUERY_STRING') !== '') {
            $this->logger->info('Please note: As the execution time of a Debugging session can take longer than the timout for the lighthttp (responsible for deployment execution), it can happen that the following stage is executed before the current stage has finished');

            // debug already started.
            return;
        }

        $params = [
            'start_debug'      => 1,
            'debug_host'       => $clientIP,
            'use_remote'       => 1,
            'debug_port'       => $port,
            'debug_session_id' => random_int(100000, 999999),
            'debug_fastfile'   => 1,
            'debug_stop'       => 1,
        ];
        $pro = new PhpProcess($_SERVER["PHP_SELF"], null, ['QUERY_STRING' => http_build_query($params)], null);
        $pro->setPhpBinary('/usr/local/zend/bin/php');
        $pro->start();

        exit();
    }

    /**
     * @param \ZendServer\DepH\Deployment $deployment
     */
    public function setDeployment(Deployment $deployment)
    {
        $this->deployment = $deployment;
    }

    /**
     * Terminates Deployment process in any case, as it should not be deployed in
     * Debugging mode. See comment in ZendDebugger::start()
     */
    public function __destruct()
    {
        if (!getenv('QUERY_STRING')) {
            // debug not started. 
            return;
        }
        $this->deployment->terminate('Deployment cannot be finished because of active Debugging mode.');
    }
}