<?php

declare(strict_types=1);
/**
 * This file is part of Ink.
 */

namespace WxPay\Example;

use WxPay\ApiObj\WxPayOrderQuery;
use WxPay\ApiObj\WxPayReverse;
use WxPay\WxPayApi;

/**
 * 刷卡支付实现类
 * 该类实现了一个刷卡支付的流程，流程如下：
 * 1、提交刷卡支付
 * 2、根据返回结果决定是否需要查询订单，如果查询之后订单还未变则需要返回查询（一般反复查10次）
 * 3、如果反复查询10订单依然不变，则发起撤销订单
 * 4、撤销订单需要循环撤销，一直撤销成功为止（注意循环次数，建议10次）.
 *
 * 该类是微信支付提供的样例程序，商户可根据自己的需求修改，或者使用lib中的api自行开发，为了防止
 * 查询时hold住后台php进程，商户查询和撤销逻辑可在前端调用
 */
class MicroPay extends WxPayApi
{
    public function __construct(array $config = [])
    {
        return parent::__construct($config);
    }

    /**
     * 查询订单情况.
     * @param string $out_trade_no 商户订单号
     * @param int $succCode 查询订单结果
     * @return0 订单不成功，1表示订单成功，2表示继续等待
     */
    public function query($out_trade_no, &$succCode)
    {
        $queryOrderInput = new WxPayOrderQuery();
        $queryOrderInput->SetOut_trade_no($out_trade_no);
        $result = parent::orderQuery($queryOrderInput);

        if ($result['return_code'] == 'SUCCESS'
            && $result['result_code'] == 'SUCCESS') {
            //支付成功
            if ($result['trade_state'] == 'SUCCESS') {
                $succCode = 1;
                return $result;
            }
            //用户支付中
            if ($result['trade_state'] == 'USERPAYING') {
                $succCode = 2;
                return false;
            }
        }

        //如果返回错误码为“此交易订单号不存在”则直接认定失败
        if ($result['err_code'] == 'ORDERNOTEXIST') {
            $succCode = 0;
        } else {
            //如果是系统错误，则后续继续
            $succCode = 2;
        }
        return false;
    }

    /**
     * 撤销订单，如果失败会重复调用10次
     * @param string $out_trade_no
     * @param mixed $depth
     */
    public function cancel($out_trade_no, $depth = 0)
    {
        if ($depth > 10) {
            return false;
        }

        $clostOrder = new WxPayReverse();
        $clostOrder->SetOut_trade_no($out_trade_no);
        $result = parent::reverse($clostOrder);

        //接口调用失败
        if ($result['return_code'] != 'SUCCESS') {
            return false;
        }

        //如果结果为success且不需要重新调用撤销，则表示撤销成功
        if ($result['result_code'] != 'SUCCESS'
            && $result['recall'] == 'N') {
            return true;
        }
        if ($result['recall'] == 'Y') {
            return $this->cancel($out_trade_no, ++$depth);
        }
        return false;
    }
}
