<?php

declare(strict_types=1);
/**
 * This file is part of Ink.
 */

namespace WxPay;

/**
 * 微信支付API异常类.
 */
class WxPayException extends \Exception
{
    public function errorMessage()
    {
        return $this->getMessage();
    }
}
