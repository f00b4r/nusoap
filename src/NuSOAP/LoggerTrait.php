<?php

namespace NuSOAP;

use \Monolog\Logger;

trait LoggerTrait {
    /**
     * the debug level for this instance.
     *
     * @var int
     */
    private $debugLevel;

    /**
     * Current debug string (manipulated by debug/debug/clearDebug/getDebug/getDebugAsXMLComment).
     *
     * @var string
     */
    private $logger;

    public function setLogger(Logger $logger) {
        if ($logger instanceof \Monolog\Logger) {
            $this->logger = $logger;

            return true;
        }

        return false;
    }

    public function getLogger() {
        return $this->logger;
    }

    /**
     * gets the debug level for this instance.
     *
     * @return int Debug level 0-9, where 0 turns off
     */
    public function getDebugLevel() {
        return $this->debugLevel;
    }

    /**
     * sets the debug level for this instance.
     *
     * @param int $level Debug level 0-9, where 0 turns off
     */
    public function setDebugLevel($level) {
        return $this->debugLevel = $level;
    }

    /**
     * adds debug data to the instance debug string with formatting.
     *
     * @param string $message
     * @param array  $context
     * @param string $log_level
     */
    public function debug($message = '', array $context = [], $log_level = 'debug') {
        if ($this->getDebugLevel() > 0 && $this->getLogger() instanceof \Monolog\Logger) {
            return $this->logger->log($log_level, $message);
        }

        return true;
    }

    /**
     * adds debug data to the instance debug string without formatting.
     *
     * @param string $message
     * @param array  $context
     * @param string $log_level
     */
    public function debug($message, $context = [], $log_level) {
        // DEPRECATED: PLEASE USE DIRECTLY DEBUG FUNCTION
        return $this->debug($message, $context, $log_level);
    }

    /**
     * clears the current debug data for this instance.
     */
    public function clearDebug() {
        // DEPRECATED IN FAVOR IF MONOLOG
        return true;
    }

    /**
     * gets the current debug data for this instance.
     *
     * @return debug data
     */
    public function getDebug() {
        // DEPRECATED IN FAVOR IF MONOLOG
        return '';
    }
}
