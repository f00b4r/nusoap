<?php

namespace NuSOAP;

trait LoggerTrait {
    /**
     * the debug level for this instance.
     *
     * @var int
     */
    private $debugLevel;

    /**
     * Current debug string (manipulated by debug/appendDebug/clearDebug/getDebug/getDebugAsXMLComment).
     *
     * @var string
     */
    public $debug_str = '';

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
     * @param string $string debug data
     * @param mixed  $time
     * @param mixed  $class
     */
    public function debug($string, $time = true, $class = true) {
        if ($this->getDebugLevel() > 0) {
            $this->debug_str .= ($time ? $this->getmicrotime() : '').($class ? get_class($this) : '').$string."\n";
        }

        return true;
    }

    /**
     * adds debug data to the instance debug string without formatting.
     *
     * @param string $string debug data
     */
    public function appendDebug($string) {
        return $this->debug($string, false, false);
    }

    /**
     * clears the current debug data for this instance.
     */
    public function clearDebug() {
        // it would be nice to use a memory stream here to use
        // memory more efficiently
        $this->debug_str = '';
    }

    /**
     * gets the current debug data for this instance.
     *
     * @return debug data
     */
    public function &getDebug() {
        // it would be nice to use a memory stream here to use
        // memory more efficiently
        return $this->debug_str;
    }

    /**
     * gets the current debug data for this instance as an XML comment
     * this may change the contents of the debug data.
     *
     * @return debug data as an XML comment
     */
    public function &getDebugAsXMLComment() {
        // it would be nice to use a memory stream here to use
        // memory more efficiently
        while (strpos($this->debug_str, '--')) {
            $this->debug_str = str_replace('--', '- -', $this->debug_str);
        }
        $ret = "<!--\n".$this->debug_str."\n-->";

        return $ret;
    }

    /**
     * returns the time in ODBC canonical form with microseconds.
     *
     * @return string The time in ODBC canonical form with microseconds
     */
    public function getmicrotime() {
        if (function_exists('gettimeofday')) {
            $tod  = gettimeofday();
            $sec  = $tod['sec'];
            $usec = $tod['usec'];
        } else {
            $sec  = time();
            $usec = 0;
        }

        return strftime('%Y-%m-%d %H:%M:%S', $sec).'.'.sprintf('%06d', $usec);
    }
}