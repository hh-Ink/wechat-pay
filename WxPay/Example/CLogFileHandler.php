<?php

declare(strict_types=1);
/**
 * This file is part of Ink.
 */

namespace WxPay\Example;

class CLogFileHandler implements ILogHandler
{
    private $handle;

    public function __construct($file = '')
    {
        $this->handle = fopen($file, 'a');
    }

    public function __destruct()
    {
        fclose($this->handle);
    }

    public function write($msg)
    {
        fwrite($this->handle, $msg, 4096);
    }
}
