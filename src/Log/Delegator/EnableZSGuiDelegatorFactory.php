<?php
declare(strict_types=1);
/**
 * Zend Server Deployment Helper (https://github.com/zend-patterns/ZendServerDeploymentHelper)
 *
 * @link      https://github.com/zend-patterns/ZendServerDeploymentHelper for the canonical source repository
 * @copyright https://github.com/zend-patterns/ZendServerDeploymentHelper/blob/master/COPYRIGHT.md Copyright
 * @license   https://github.com/zend-patterns/ZendServerDeploymentHelper/blob/master/LICENSE.md New BSD License
 */

namespace ZendServer\DepH\Log\Delegator;

use Interop\Container\ContainerInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog;
use Zend\Log as Zend;
use Zend\ServiceManager\Factory\DelegatorFactoryInterface;

/**
 * Class EnableZSGuiDelegatorFactory
 * @package ZendServer\DepH\Log\Delegator
 */
class EnableZSGuiDelegatorFactory implements DelegatorFactoryInterface
{

    /**
     * @inheritdoc
     */
    public function __invoke(ContainerInterface $container, $name, callable $callback, array $options = null)
    {
        $instance = $callback();
        if ($instance instanceof Monolog\Logger) {
            $existingHandlers = $instance->getHandlers();

            $handler = new StreamHandler('php://output', Monolog\Logger::DEBUG);
            $instance->setHandlers([$handler]);

            $formatter = new LineFormatter('', 'c');
            $handler->setFormatter($formatter);
            $instance->info('');

            $formatter = new LineFormatter('%datetime% %level_name% (%level%): %message% %context% %extra%', 'c');
            $handler->setFormatter($formatter);

            $instance->setHandlers($existingHandlers);
            $instance->pushHandler($handler);

        } elseif ($instance instanceof Zend\PsrLoggerAdapter && $instance->getLogger() instanceof Zend\Logger) {
            /** @var Zend\Logger $logger */
            $logger = $instance->getLogger();

            $existingWriters = $logger->getWriters();
            $logger->setWriters(new \Zend\Stdlib\SplPriorityQueue());

            $writer = new \Zend\Log\Writer\Stream('php://output');
            $logger->addWriter($writer);

            $formatter = new \Zend\Log\Formatter\Simple('');
            $writer->setFormatter($formatter);
            $instance->info('');

            $format = '<code>%timestamp% %priorityName% (%priority%): %message% </code>';
            $formatter = new \Zend\Log\Formatter\Simple($format);
            $writer->setFormatter($formatter);

            $existingWriters->insert($writer, 1);
            $logger->setWriters($existingWriters);
        }

        return $instance;
    }
}