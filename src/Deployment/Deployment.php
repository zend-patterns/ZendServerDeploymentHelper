<?php
declare(strict_types=1);
/**
 * Zend Server Deployment Helper (https://github.com/zend-patterns/ZendServerDeploymentHelper)
 *
 * @link      https://github.com/zend-patterns/ZendServerDeploymentHelper for the canonical source repository
 * @copyright https://github.com/zend-patterns/ZendServerDeploymentHelper/blob/master/COPYRIGHT.md Copyright
 * @license   https://github.com/zend-patterns/ZendServerDeploymentHelper/blob/master/LICENSE.md New BSD License
 */

namespace ZendServer\DepH\Deployment;

use function file_exists;
use function is_writable;
use Symfony\Component\Filesystem\Filesystem;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;
use const PHP_OS;
use function array_reverse;
use function array_search;
use function basename;
use function debug_backtrace;
use function dirname;
use function explode;
use function getenv;
use function is_array;
use function is_string;
use function parse_url;
use function rtrim;
use function str_replace;
use function stripos;
use function strlen;
use function strpos;
use function strtolower;
use function strtoupper;
use function trim;

/**
 * Class Deployment
 * @package ZendServer\DepH\Deployment
 */
class Deployment implements EventManagerAwareInterface
{

    use EventManagerAwareTrait;

    const PRE_STAGE = 1;
    const POST_STAGE = 2;
    const PRE_UNSTAGE = 3;
    const POST_UNSTAGE = 4;
    const PRE_ACTIVATE = 5;
    const POST_ACTIVATE = 6;
    const PRE_ROLLBACK = 7;
    const POST_ROLLBACK = 8;
    const PRE_DEACTIVATE = 9;
    const POST_DEACTIVATE = 10;

    const DIR_PREFIX = '/usr/local/zend';
    const DIR_PREFIX_I5 = '/usr/local/zendsvr6';
    const DEFAULT_VHOST_FILENAME = 'zend-default-vhost-80.conf';

    /**
     * Filenames and corresponding keys of all valid action scripts
     *
     * @var array
     */
    public static $actionScriptNames = [
        self::PRE_STAGE       => 'pre_stage.php',
        self::POST_STAGE      => 'post_stage.php',
        self::PRE_ACTIVATE    => 'pre_activate.php',
        self::POST_ACTIVATE   => 'post_activate.php',
        self::PRE_ROLLBACK    => 'pre_rollback.php',
        self::POST_ROLLBACK   => 'post_rollback.php',
        self::PRE_DEACTIVATE  => 'pre_deactivate.php',
        self::POST_DEACTIVATE => 'post_deactivate.php',
        self::PRE_UNSTAGE     => 'pre_unstage.php',
        self::POST_UNSTAGE    => 'post_unstage.php',
    ];

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * Deployment constructor.
     *
     * @param $fs
     */
    public function __construct(Filesystem $fs = null)
    {
        if (null === $fs) {
            $fs = new Filesystem();
        }
        $this->fs = $fs;
    }

    /**
     * Retrieves the value of the constant of the currently used action
     *
     * @return int
     * @throws Exception\RuntimeException if no action script name was found in backtrace
     */
    public function getCurrentAction(): int
    {
        static $stack = [];
        if (empty($stack)) {
            $stack = array_reverse(debug_backtrace(), true);
        }

        foreach ($stack as $item) {
            if (!isset($item['file'])) {
                continue;
            }
            $filename = basename($item['file']);
            if (false !== $key = array_search($filename, static::$actionScriptNames, true)) {
                return (int)$key;
            }
        }

        throw new Exception\RuntimeException('Method can only be called from within a Zend Server Deployment Hook script');
    }

    /**
     * Retrieves the currently used action filename
     *
     * @return string
     */
    public function getCurrentActionScript(): string
    {
        return static::$actionScriptNames[$this->getCurrentAction()];
    }

    /**
     * @return boolean
     */
    public function isPreStageAction(): bool
    {
        return (self::PRE_STAGE === $this->getCurrentAction());
    }

    /**
     * @return boolean
     */
    public function isPostStageAction(): bool
    {
        return (self::POST_STAGE === $this->getCurrentAction());
    }

    /**
     * @return boolean
     */
    public function isPreUnstageAction(): bool
    {
        return (self::PRE_UNSTAGE === $this->getCurrentAction());
    }

    /**
     * @return boolean
     */
    public function isPostUnstageAction(): bool
    {
        return (self::POST_UNSTAGE === $this->getCurrentAction());
    }

    /**
     * @return boolean
     */
    public function isPreActivateAction(): bool
    {
        return (self::PRE_ACTIVATE === $this->getCurrentAction());
    }

    /**
     * @return boolean
     */
    public function isPostActivateAction(): bool
    {
        return (self::POST_ACTIVATE === $this->getCurrentAction());
    }

    /**
     * @return boolean
     */
    public function isPreDeactivateAction(): bool
    {
        return (self::PRE_DEACTIVATE === $this->getCurrentAction());
    }

    /**
     * @return boolean
     */
    public function isPostDeactivateAction(): bool
    {
        return (self::POST_DEACTIVATE === $this->getCurrentAction());
    }

    /**
     * @return boolean
     */
    public function isPreRollbackAction(): bool
    {
        return (self::PRE_ROLLBACK === $this->getCurrentAction());
    }

    /**
     * @return boolean
     */
    public function isPostRollbackAction(): bool
    {
        return (self::POST_ROLLBACK === $this->getCurrentAction());
    }

    /**
     * @return boolean
     */
    public function isUpdate(): bool
    {
        return (bool)$this->getParam('ZS_PREVIOUS_APP_VERSION', false);
    }

    /**
     * Returns the Application ID
     *
     * @return string
     */
    public function getApplicationId(): string
    {
        $this->getEventManager()->trigger(__FUNCTION__, $this, ['envname' => 'ZS_APPLICATION_ID']);

        return $this->getParam('ZS_APPLICATION_ID');
    }

    /**
     * Returns the application name. Unfortunately this is determined
     * by the base url, there is no known other way to get this information.
     *
     * @return string
     */
    public function getApplicationName(): string
    {
        $url = $this->getParam('ZS_BASE_URL');
        $urlParts = explode(':', $url);

        return str_replace('/', '_', trim($urlParts[1], '/'));
    }

    /**
     * In case an upgrade was performed, returns the version number
     * of the current application
     *
     * @return string
     */
    public function getCurrentAppVersion(): string
    {
        $this->getEventManager()->trigger(__FUNCTION__, $this, ['envname' => 'ZS_CURRENT_APP_VERSION']);

        return $this->getParam('ZS_CURRENT_APP_VERSION');
    }

    /**
     * Returns previous application version.
     *
     * In case a rollback was performed, returns the previous version of the application.
     * This will be NULL if no previous version exists (freshly installed app).
     *
     * @return string|null
     */
    public function getPreviousAppVersion()
    {
        $this->getEventManager()->trigger(__FUNCTION__, $this, ['envname' => 'ZS_PREVIOUS_APP_VERSION']);

        return $this->getParam('ZS_PREVIOUS_APP_VERSION');
    }

    /**
     * Returns the directory to which the application is deployed.
     *
     * The directory will be NULL in PRE_STAGE phase.
     *
     * @return string|null
     */
    public function getApplicationBaseDir()
    {
        $this->getEventManager()->trigger(__FUNCTION__, $this, ['envname' => 'ZS_APPLICATION_BASE_DIR']);

        return $this->getParam('ZS_APPLICATION_BASE_DIR');
    }

    /**
     * Returns previous application base directory.
     *
     * In case a rollback was performed,returns the directory to which the application was deployed.
     * This will be NULL if no previous version exists (freshly installed app).
     *
     * @return string|null
     */
    public function getPreviousApplicationBaseDir()
    {
        $this->getEventManager()->trigger(__FUNCTION__, $this, ['envname' => 'ZS_PREVIOUS_APPLICATION_BASE_DIR']);

        return $this->getParam('ZS_PREVIOUS_APPLICATION_BASE_DIR');
    }

    /**
     * returns the PHP version that Zend Server uses
     *
     * @return string
     */
    public function getPhpVersion(): string
    {
        $this->getEventManager()->trigger(__FUNCTION__, $this, ['envname' => 'ZS_PHP_VERSION']);

        return $this->getParam('ZS_PHP_VERSION');
    }

    /**
     * When deploying in a Cluster Environment,
     * a single node ID is chosen to perform actions
     * that only need to be done once. If the value
     * of this constant is set  to ‘1’ during deployment,
     * the node is defined as the ‘run once node’.
     * The value of the env variable is casted to boolean value.
     *
     * @return bool
     */
    public function getRunOnceNode(): bool
    {
        $this->getEventManager()->trigger(__FUNCTION__, $this, ['envname' => 'ZS_RUN_ONCE_NODE']);

        return (bool)$this->getParam('ZS_RUN_ONCE_NODE', false);
    }

    /**
     * Returns the web server user group ID (UNIX only)
     *
     * @return int
     */
    public function getWebserverGid(): int
    {
        $this->getEventManager()->trigger(__FUNCTION__, $this, ['envname' => 'ZS_WEBSERVER_GID']);

        return (int)$this->getParam('ZS_WEBSERVER_GID');
    }

    /**
     * Returns the web server user ID (UNIX only)
     *
     * @return int
     */
    public function getWebserverUid(): int
    {
        $this->getEventManager()->trigger(__FUNCTION__, $this, ['envname' => 'ZS_WEBSERVER_UID']);

        return (int)$this->getParam('ZS_WEBSERVER_UID');
    }

    /**
     * Returns a code representing the web server type (APACHE)
     *
     * @return string
     */
    public function getWebserverType(): string
    {
        $this->getEventManager()->trigger(__FUNCTION__, $this, ['envname' => 'ZS_WEBSERVER_TYPE']);

        return $this->getParam('ZS_WEBSERVER_TYPE');
    }

    /**
     * Returns the web server version (2.2)
     *
     * @return string
     */
    public function getWebserverVersion(): string
    {
        $this->getEventManager()->trigger(__FUNCTION__, $this, ['envname' => 'ZS_WEBSERVER_VERSION']);

        return $this->getParam('ZS_WEBSERVER_VERSION');
    }

    /**
     * @return string
     */
    public function getZendServerBaseDir(): string
    {
        if (strpos(PHP_OS, "OS400") !== false) {
            return self::DIR_PREFIX_I5;
        }

        return self::DIR_PREFIX;
    }

    /**
     * Returns the base dir where the deployment scripts are executed in.
     *
     * The directory returned exists only during deployment script lifetime.
     *
     * @return string
     */
    public function getDeploymentBaseDir(): string
    {
        return dirname($_SERVER['SCRIPT_FILENAME']);
    }

    /**
     * @return string
     */
    public function getSitesDDir(): string
    {
        return $this->getZendServerBaseDir() . '/etc/sites.d';
    }

    /**
     * returns the root path in which all of the application are being deployed.
     * This dir is writable by user zend, used for persitent storage for all
     * app versions.
     *
     * @return string
     */
    public function getAppsDir(): string
    {
        return $this->getZendServerBaseDir() . '/var/apps';
    }

    /**
     * @return string
     */
    public function getZendServerLogDir(): string
    {
        return $this->getZendServerBaseDir() . '/var/log';
    }

    /**
     * Returns a writable Temp folder (which will be available only during script lifetime)
     *
     * @return string
     */
    public function getDeploymentTempDir()
    {
        $dir = $this->getDeploymentBaseDir() . "/tmp";
        if (!file_exists($dir) && is_writable($this->getDeploymentBaseDir())) {
            $this->fs->mkdir($dir);
        }

        return $dir;
    }

    /**
     * retrieves path of default log file path depending on OS
     *
     * @return string
     */
    public function getDefaultLogFile(): string
    {
        return $this->getZendServerLogDir() . '/app_deployment.log';
    }

    /**
     * retrieves path of app specific log file
     *
     * @return string
     */
    public function getApplicationLogFile(): string
    {
        $appName = preg_replace('/[^a-zA-Z0-9]/', '', $this->getApplicationName());
        $logfile = 'app_' . strtolower($appName) . '_' . strtolower($this->getCurrentAppVersion()) . '_deployment.log';

        return $this->getZendServerLogDir() . "/" . $logfile;
    }

    /**
     * Returns filename of original Zend Server vhost file
     *
     * @return string
     * @throws Exception\FileNotFoundException
     */
    public function getVirtualHostFile(): string
    {
        $applicationBaseDir = $this->getApplicationBaseDir();

        if (strpos($applicationBaseDir, '__default__') > 0) {
            $file = $this->getSitesDDir() . '/' . self::DEFAULT_VHOST_FILENAME;
            if (!$this->fs->exists($file)) {
                throw new Exception\FileNotFoundException('VirtualHost configuration file "' . $file . '" not found.');
            }

            return $file;
        }

        $virtualHostFile = str_replace([$this->getAppsDir(), $this->getCurrentAppVersion()], '', $applicationBaseDir);
        $virtualHostFile = trim(rtrim($virtualHostFile, '/'));
        $virtualHostFile = str_replace('/', '_', $virtualHostFile) . '.conf';
        $virtualHostFile = $this->getSitesDDir() . '/vhost' . $virtualHostFile;

        return $virtualHostFile;
    }

    /**
     * Creates a writeable folder relative to the deployment base directory.
     *
     * The directory returned is only valid during script lifetime.
     *
     * @param string $dir Relative directory path to be created
     *
     * @return string The absolute path of the created directory
     */
    public function createWriteableDeploymentTempDir(string $dir): string
    {
        $dir = $this->getDeploymentBaseDir() . "/tmp/$dir";
        $this->createWritableDir($dir);

        return $dir;
    }

    /**
     * Creates a writeable folder relative to the application base directory.
     *
     * The created folder will be accessable by the web server user.
     * Be careful with app removement: deployment user cannot remove dirs that contain files written by the webserver
     * user.
     *
     * @param string $dir Relative directory path to be created
     *
     * @return string The absolute path of the created directory
     */
    public function createWriteableApplicationDir(string $dir): string
    {
        $this->createWritableDir($this->getApplicationBaseDir() . "/$dir");

        return $this->getApplicationBaseDir() . "/$dir";
    }

    /**
     * Creates persistent directory.
     *
     * Creates a folder $dir relative to Zend Server application dir or
     * $persistentDir with access rights for the web server user.
     * $dir will also be created as a symlink relative to the application base dir.
     * creates a dir with access rights for the web server user. $dir is
     *
     * As the persistent directory is created outside the app base dir it is not affected by app removals.
     * The data is also available for all other app versions.
     *
     * @param string      $dir Relative directory path
     * @param string|null $persitentDir
     *
     * @return array of absolute path of persistent dir and linked dir in app folder
     */
    public function createPersitentApplicationDir(string $dir, $persitentDir = null): array
    {

        if (!$persitentDir) {
            $persitentDir = $this->getAppsDir();
        }

        $persitentDir = rtrim($persitentDir, "/");
        $persitentDir = rtrim($persitentDir, "\\");

        $appName = $this->getApplicationName();
        $persitentDir .= "/$appName/$dir";

        $dir = $this->getApplicationBaseDir() . "/$dir";

        $this->createWritableDir($persitentDir);

        $this->fs->symlink($persitentDir, $dir);

        return [
            'persitentDir' => $persitentDir,
            'linkedDir'    => $dir,
        ];
    }

    /**
     * @param string $dir
     */
    private function createWritableDir(string $dir)
    {
        $this->fs->mkdir($dir);

        $gid = $this->getWebserverGid();
        $this->fs->chgrp($dir, $gid, true);
        $this->fs->chmod($dir, 0775, 0000, true);
    }

    /**
     * Checks whether application is being deployed on the default site or on
     * a custom vhost
     *
     * @return boolean
     */
    public function isDefaultSite(): bool
    {
        $applicationBaseDir = $this->getApplicationBaseDir();

        return (bool)strpos($applicationBaseDir, '__default__') > 0;
    }

    /**
     * Returns the base URL set for deployment
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        $this->getEventManager()->trigger(__FUNCTION__, $this, ['envname' => 'ZS_BASE_URL']);

        return $this->getParam('ZS_BASE_URL');
    }

    /**
     * Returns the server name. if no url is passed, it is taken from
     * the base url.
     *
     * @param string|array|null $arguments
     *
     * @return string
     * @throws Exception\InvalidArgumentException
     */
    public function getServerName($arguments = null): string
    {
        if (!$arguments) {
            $arguments = $this->getParam('ZS_BASE_URL');
            // @todo doesn't work properly when installing from command line, produces sth. like http\\://hostname
            $arguments = str_replace(['http\\:', 'https\\:'], ['http:', 'https:'], $arguments);
        }

        if (is_string($arguments)) {
            $url = $arguments;
        } elseif (is_array($arguments)) {
            $url = $arguments[0];
        } else {
            throw new Exception\InvalidArgumentException('Either pass a string or an array to the Deployment::getServerName() method.');
        }

        return parse_url($url, PHP_URL_HOST);
    }

    /**
     * returns the path in which the application will be deployed
     *
     * @return string
     */
    public function getUrlPath(): string
    {
        $baseUrl = $this->getBaseUrl();

        if (strpos($baseUrl, 'http:///') === 0) {
            $baseUrl = str_replace('http:///', 'http://xyz/', $baseUrl);
        }

        if (($path = parse_url($baseUrl, PHP_URL_PATH)) === null) {
            $path = '/';
        }

        return $path;
    }

    /**
     * @param $urlArgument
     *
     * @return string
     */
    public function getUrlWithTrailingSlash(string $urlArgument): string
    {
        $url = $this->getParam($urlArgument);

        return (strrpos($url, '/') + 1 === strlen($url)) ? $url : $url . '/';
    }

    /**
     * Returns value custom argument defined in deployment.xml (stored as env variable).
     *
     * Zend Server prefix 'ZS_' will be automatically prepended to $name if $name not already starts with 'ZS_'
     *
     * @param string            $name
     * @param array|string|null $default
     *
     * @return array|string|null
     */
    public function getParam(string $name, $default = null)
    {

        $name = (stripos($name, 'ZS_') === 0) ? strtoupper($name) : 'ZS_' . strtoupper($name);

        $this->getEventManager()->trigger(__FUNCTION__, $this, ['envname' => $name]);
        $res = getenv($name);
        if (!$res) {
            return $default;
        }

        // @todo doesn't work properly when installing from command line, produces sth. like http\\://hostname
        $res = str_replace(['http\\:', 'https\\:'], ['http:', 'https:'], $res);

        return $res;
    }

    /**
     * Terminates the deployment process
     *
     * @param string|null $msg
     */
    public function terminate(string $msg = null)
    {
        $this->getEventManager()->trigger(__FUNCTION__, $this, ['msg' => $msg]);

        exit(1);
    }
}