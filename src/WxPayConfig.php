<?php

declare(strict_types=1);
/**
 * This file is part of Ink.
 */

namespace WxPay;

/**
 *    配置账号信息.
 */
class WxPayConfig
{
    //=======【基本信息设置】=====================================
    //
    public static $NOTIFY_URL = '';

    /**
     * 微信公众号信息配置.
     *
     * APPID：绑定支付的APPID（必须配置，开户邮件中可查看）
     *
     * MCHID：商户号（必须配置，开户邮件中可查看）
     *
     * KEY：商户支付密钥，参考开户邮件设置（必须配置，登录商户平台自行设置）
     * 设置地址：https://pay.weixin.qq.com/index.php/account/api_cert
     *
     * APPSECRET：公众帐号secert（仅JSAPI支付的时候需要配置， 登录公众平台，进入开发者中心可设置），
     * 获取地址：https://mp.weixin.qq.com/advanced/advanced?action=dev&t=advanced/dev&token=2005451881&lang=zh_CN
     * @var string
     */
    public static $KEY = '';

    public static $MCHID = '';

    public static $APPID = '';

    public static $APPSECRET = '';

    //=======【证书路径设置】=====================================
    /*
     * 证书路径,注意应该填写绝对路径（仅退款、撤销订单时需要，可登录商户平台下载，
     * API证书下载地址：https://pay.weixin.qq.com/index.php/account/api_cert，下载之前需要安装商户操作证书）
     * @var path
     */
    public static $SSLCERT_PATH = '';

    public static $SSLKEY_PATH = '';

    //=======【curl代理设置】===================================

    /**
     * 本例程通过curl使用HTTP POST方法，此处可修改代理服务器，
     * 默认CURL_PROXY_HOST=0.0.0.0和CURL_PROXY_PORT=0，此时不开启代理（如有需要才设置）.
     */
    public static $CURL_PROXY_HOST = '0.0.0.0'; //"10.152.18.220";

    public static $CURL_PROXY_PORT = 0; //8080;

    //=======【上报信息配置】===================================

    /**
     * 不会影响接口调用流程），开启上报之后，方便微信监控请求调用的质量，建议至少
     * 开启错误上报。
     * 上报等级，0.关闭上报; 1.仅错误出错上报; 2.全量上报.
     * @var int
     */
    public static $REPORT_LEVENL = 1;
}
