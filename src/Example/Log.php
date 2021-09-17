<?php

declare(strict_types=1);
/**
 * This file is part of Ink.
 */

namespace WxPay\Example;

class Log
{
    private $handler;

    private $level = 15;

    private static $instance;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    private function __setHandle($handler)
    {
        $this->handler = $handler;
    }

    private function __setLevel($level)
    {
        $this->level = $level;
    }

    public static function Init($handler = null, $level = 15)
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
            self::$instance->__setHandle($handler);
            self::$instance->__setLevel($level);
        }
        return self::$instance;
    }

    public static function DEBUG($msg)
    {
        self::$instance->write(1, $msg);
    }

    public static function WARN($msg)
    {
        self::$instance->write(4, $msg);
    }

    public static function ERROR($msg)
    {
        $debugInfo = debug_backtrace();
        $stack = '[';
        foreach ($debugInfo as $key => $val) {
            if (array_key_exists('file', $val)) {
                $stack .= ',file:' . $val['file'];
            }
            if (array_key_exists('line', $val)) {
                $stack .= ',line:' . $val['line'];
            }
            if (array_key_exists('function', $val)) {
                $stack .= ',function:' . $val['function'];
            }
        }
        $stack .= ']';
        self::$instance->write(8, $stack . $msg);
    }

    public static function INFO($msg)
    {
        self::$instance->write(2, $msg);
    }

    protected function write($level, $msg)
    {
        if (($level & $this->level) == $level) {
            $msg = '[' . date('Y-m-d H:i:s') . '][' . $this->getLevelStr($level) . '] ' . $msg . "\n";
            $this->handler->write($msg);
        }
    }

    private function getLevelStr($level): string
    {
        switch ($level) {
            case 1:
                return 'debug';
            case 2:
                return 'info';
            case 4:
                return 'warn';
            case 8:
                return 'error';
            default:
                return '';
        }
    }
}
