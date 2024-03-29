<?php

declare(strict_types=1);
/**
 * This file is part of Ink.
 */

namespace WxPay\Example;

use WxPay\ApiObj\WxPayJsApiPay;
use WxPay\WxPayApi;
use WxPay\WxPayConfig;
use WxPay\WxPayException;

/**
 * JSAPI支付实现类
 * 该类实现了从微信公众平台获取code、通过code获取openid和access_token、
 * 生成jsapi支付js接口所需的参数、生成获取共享收货地址所需的参数.
 *
 * 该类是微信支付提供的样例程序，商户可根据自己的需求修改，或者使用lib中的api自行开发
 */
class JsApiPay extends WxPayConfig
{
    /**
     * 网页授权接口微信服务器返回的数据，返回样例如下
     * {
     *  "access_token":"ACCESS_TOKEN",
     *  "expires_in":7200,
     *  "refresh_token":"REFRESH_TOKEN",
     *  "openid":"OPENID",
     *  "scope":"SCOPE",
     *  "unionid": "o6_bmasdasdsad6_2sgVt7hMZOPfL"
     * }
     * 其中access_token可用于获取共享收货地址
     * openid是微信支付jsapi支付接口必须的参数.
     * @var array
     */
    public $data;

    private $curl_timeout = 10;

    /**
     * 加载配置.
     * @throws WxPayException
     * @return bool
     */
    public function __construct(array $config = [])
    {
        if (!empty($config)) {
            foreach ($config as $key => $val) {
                if (!isset(parent::${$key})) {
                    throw new WxPayException($key . ' is undefine ');
                }
                parent::${$key} = $val;
            }
        }
        return true;
    }

    /**
     * 构造获取code的url连接.
     * @param string $redirectUrl 微信服务器回跳的url，需要url编码
     */
    private function __CreateOauthUrlForCode($redirectUrl)
    {
        $urlObj['appid'] = parent::$APPID;
        $urlObj['redirect_uri'] = "{$redirectUrl}";
        $urlObj['response_type'] = 'code';
        $urlObj['scope'] = 'snsapi_base';
        $urlObj['state'] = 'STATE' . '#wechat_redirect';
        $bizString = $this->ToUrlParams($urlObj);
        return 'https://open.weixin.qq.com/connect/oauth2/authorize?' . $bizString;
    }

    /**
     * 构造获取open和access_toke的url地址
     * @param string $code 微信跳转带回的code
     */
    private function __CreateOauthUrlForOpenid($code)
    {
        $urlObj['appid'] = parent::$APPID;
        $urlObj['secret'] = parent::$APPSECRET;
        $urlObj['code'] = $code;
        $urlObj['grant_type'] = 'authorization_code';
        $bizString = $this->ToUrlParams($urlObj);
        return 'https://api.weixin.qq.com/sns/oauth2/access_token?' . $bizString;
    }

    /**
     * 通过跳转获取用户的openid，跳转流程如下：
     * 1、设置自己需要调回的url及其其他参数，跳转到微信服务器https://open.weixin.qq.com/connect/oauth2/authorize
     * 2、微信服务处理完成之后会跳转回用户redirect_uri地址，此时会带上一些参数，如：code.
     */
    public function GetOpenid()
    {
        //通过code获得openid
        if (!isset($_GET['code'])) {
            //触发微信返回code码
            $baseUrl = urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . $_SERVER['QUERY_STRING']);
            $url = $this->__CreateOauthUrlForCode($baseUrl);
            header("Location: {$url}");
            exit();
        }
        //获取code码，以获取openid
        $code = $_GET['code'];
        return $this->getOpenidFromMp($code);
    }

    /**
     * 获取jsapi支付的参数.
     * @param array $UnifiedOrderResult 统一支付接口返回的数据
     * @throws WxPayException
     */
    public function GetJsApiParameters($UnifiedOrderResult)
    {
        if (!array_key_exists('appid', $UnifiedOrderResult)
            || !array_key_exists('prepay_id', $UnifiedOrderResult)
            || $UnifiedOrderResult['prepay_id'] == '') {
            throw new WxPayException('参数错误');
        }
        $jsapi = new WxPayJsApiPay();
        $jsapi->SetAppid($UnifiedOrderResult['appid']);
        $timeStamp = time();
        $jsapi->SetTimeStamp("{$timeStamp}");
        $jsapi->SetNonceStr(WxPayApi::getNonceStr());
        $jsapi->SetPackage('prepay_id=' . $UnifiedOrderResult['prepay_id']);
        $jsapi->SetSignType('MD5');
        $jsapi->SetPaySign($jsapi->SetSign(parent::$KEY));
        return json_encode($jsapi->GetValues());
    }

    /**
     * 通过code从工作平台获取openid机器access_token.
     * @param string $code 微信跳转回来带上的code
     */
    public function GetOpenidFromMp($code)
    {
        $url = $this->__CreateOauthUrlForOpenid($code);
        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->curl_timeout);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (parent::$CURL_PROXY_HOST != '0.0.0.0'
            && parent::$CURL_PROXY_PORT != 0) {
            curl_setopt($ch, CURLOPT_PROXY, parent::$CURL_PROXY_HOST);
            curl_setopt($ch, CURLOPT_PROXYPORT, parent::$CURL_PROXY_PORT);
        }
        //运行curl，结果以jason形式返回
        $res = curl_exec($ch);
        curl_close($ch);
        //取出openid
        $data = json_decode($res, true);
        $this->data = $data;
        return $data['openid'];
    }

    /**
     * 获取地址js参数.
     */
    public function GetEditAddressParameters()
    {
        $getData = $this->data;
        $data = [];
        $data['appid'] = parent::$APPID;
        $data['url'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $time = time();
        $data['timestamp'] = "{$time}";
        $data['noncestr'] = '1234568';
        $data['accesstoken'] = $getData['access_token'];
        ksort($data);
        $params = $this->ToUrlParams($data);
        $addrSign = sha1($params);

        $afterData = [
            'addrSign' => $addrSign,
            'signType' => 'sha1',
            'scope' => 'jsapi_address',
            'appId' => parent::$APPID,
            'timeStamp' => $data['timestamp'],
            'nonceStr' => $data['noncestr'],
        ];
        return json_encode($afterData);
    }

    /**
     * 拼接签名字符串.
     */
    private function ToUrlParams(array $urlObj): string
    {
        $buff = '';
        foreach ($urlObj as $k => $v) {
            if ($k != 'sign') {
                $buff .= $k . '=' . $v . '&';
            }
        }

        return trim($buff, '&');
    }
}
