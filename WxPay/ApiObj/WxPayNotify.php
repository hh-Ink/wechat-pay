<?php

declare(strict_types=1);
/**
 * This file is part of Ink.
 */

namespace WxPay\ApiObj;

use WxPay\WxPayApi;

/**
 * 回调基础类.
 */
class WxPayNotify extends WxPayNotifyReply
{
    /**
     * 回调入口.
     * @param bool $needSign 是否需要签名输出时
     * @param string $apiKey 需要签名输出时的KEY
     * @throws \WxPay\WxPayException
     */
    final public function Handle($apiKey, $needSign = false)
    {
        $msg = 'OK';
        //当返回false的时候，表示notify中调用NotifyCallBack回调失败获取签名校验失败，此时直接回复失败
        $result = WxPayApi::notify([$this, 'NotifyCallBack'], $msg, $apiKey);
        if ($result == false) {
            $this->SetReturn_code('FAIL');
            $this->SetReturn_msg($msg);
            $this->ReplyNotify(false, $apiKey);
            return;
        }
        //该分支在成功回调到NotifyCallBack方法，处理完成之后流程
        $this->SetReturn_code('SUCCESS');
        $this->SetReturn_msg('OK');

        $this->ReplyNotify($needSign, $apiKey);
    }

    /**
     * 回调方法入口，子类可重写该方法
     * 注意：
     * 1、微信回调超时时间为2s，建议用户使用异步处理流程，确认成功之后立刻回复微信服务器
     * 2、微信服务器在调用失败或者接到回包为非确认包的时候，会发起重试，需确保你的回调是可以重入.
     * @param array $data 回调解释出的参数
     * @param string $msg 如果回调处理失败，可以将错误信息输出到该方法
     */
    public function NotifyProcess($data, &$msg)
    {
        return true;
    }

    /**
     * notify回调方法，该方法中需要赋值需要输出的参数,不可重写.
     * @param array $data
     */
    final public function NotifyCallBack($data)
    {
        $msg = 'OK';
        $result = $this->NotifyProcess($data, $msg);

        if ($result == true) {
            $this->SetReturn_code('SUCCESS');
            $this->SetReturn_msg('OK');
        } else {
            $this->SetReturn_code('FAIL');
            $this->SetReturn_msg($msg);
        }
        return $result;
    }

    /**
     * 回复通知.
     * @param bool $needSignKey
     * @param string $apiKey 需要签名输出时的KEY
     * @throws \WxPay\WxPayException
     */
    final private function ReplyNotify($needSignKey, $apiKey)
    {
        //如果需要签名
        if ($needSignKey && $this->GetReturn_code() == 'SUCCESS') {
            $this->SetSign($apiKey);
        }
        WxpayApi::replyNotify($this->ToXml());
    }
}
