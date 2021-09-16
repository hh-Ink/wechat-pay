<?php

declare(strict_types=1);
/**
 * This file is part of Ink.
 */

namespace WxPay\Example;

interface ILogHandler
{
    public function write($msg);
}
