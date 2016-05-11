<?php

namespace ByJG\AnyDataset;

use ByJG\DesignPattern\Singleton;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

/**
 * Description of LogHandler
 *
 * @author jg
 */
class LogHandler implements LoggerInterface
{

    use Singleton;

    /**
     *
     * @var Logger
     */
    private $logger;

    protected function __construct()
    {
        // Nothing here
    }

    public function pushLogHandler(HandlerInterface $handler)
    {
        if (!isset($this->logger)) {
            $this->logger = new Logger('byjg-anydataset');
        }
        $this->logger->pushHandler($handler);
    }

    public function alert($message, array $context = array())
    {
        if (isset($this->logger)) {
            $this->logger->addAlert($message, $context);
        }
    }

    public function critical($message, array $context = array())
    {
        if (isset($this->logger)) {
            $this->logger->addCritical($message, $context);
        }
    }

    public function debug($message, array $context = array())
    {
        if (isset($this->logger)) {
            $this->logger->addDebug($message, $context);
        }
    }

    public function emergency($message, array $context = array())
    {
        if (isset($this->logger)) {
            $this->logger->addEmergency($message, $context);
        }
    }

    public function error($message, array $context = array())
    {
        if (isset($this->logger)) {
            $this->logger->addError($message, $context);
        }
    }

    public function info($message, array $context = array())
    {
        if (isset($this->logger)) {
            $this->logger->addInfo($message, $context);
        }
    }

    public function log($level, $message, array $context = array())
    {
        if (isset($this->logger)) {
            $this->logger->log($level, $message, $context);
        }
    }

    public function notice($message, array $context = array())
    {
        if (isset($this->logger)) {
            $this->logger->addNotice($message, $context);
        }
    }

    public function warning($message, array $context = array())
    {
        if (isset($this->logger)) {
            $this->logger->addWarning($message, $context);
        }
    }
}
