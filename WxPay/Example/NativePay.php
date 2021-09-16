<?php

declare(strict_types=1);
/**
 * This file is part of Ink.
 */

namespace WxPay\Example;

use WxPay\ApiObj\WxPayBizPayUrl;
use WxPay\WxPayApi;

/**
 * 刷卡支付实现类.
 */
class NativePay extends WxPayApi
{
    public function __construct(array $config = [])
    {
        return parent::__construct($config);
    }

    /**
     * 生成扫描支付URL,模式一
     * @param mixed $productId
     */
    public function GetPrePayUrl($productId)
    {
        $biz = new WxPayBizPayUrl();
        $biz->SetProduct_id($productId);
        $values = parent::bizpayurl($biz);
        return 'weixin://wxpay/bizpayurl?' . $this->ToUrlParams($values);
    }

    /**
     * 生成直接支付url，支付url有效期为2小时,模式二.
     * @param mixed $input
     * @throws \WxPay\WxPayException
     */
    public function GetPayUrl($input)
    {
        if ($input->GetTrade_type() == 'NATIVE') {
            return parent::unifiedOrder($input);
        }
        return false;
    }

    /**
     * 参数数组转换为url参数.
     * @param array $urlObj
     */
    private function ToUrlParams($urlObj)
    {
        $buff = '';
        foreach ($urlObj as $k => $v) {
            $buff .= $k . '=' . $v . '&';
        }

        return trim($buff, '&');
    }
}
