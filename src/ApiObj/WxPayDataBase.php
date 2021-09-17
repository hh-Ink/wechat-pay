<?php

declare(strict_types=1);
/**
 * This file is part of Ink.
 */

namespace WxPay\ApiObj;

use WxPay\WxPayException;

/**
 * 数据对象基础类，该类中定义数据类最基本的行为，包括：
 * 计算/设置/获取签名、输出xml格式的参数、从xml读取数据对象等.
 */
class WxPayDataBase
{
    public $values = [];

    /**
     * 设置签名，详见签名生成算法.
     * @param string $key 公众号平台填写的加密key
     * @param string $value
     * @param mixed $apiKey
     */
    public function SetSign($apiKey)
    {
        if (!$apiKey) {
            throw new WxPayException('加密 KEY 长度错误');
        }
        $sign = $this->MakeSign($apiKey);
        $this->values['sign'] = $sign;
        return $sign;
    }

    /**
     * 获取签名，详见签名生成算法的值
     */
    public function GetSign()
    {
        return $this->values['sign'];
    }

    /**
     * 判断签名，详见签名生成算法是否存在.
     * @return true 或 false
     */
    public function IsSignSet()
    {
        return array_key_exists('sign', $this->values);
    }

    /**
     * 输出xml字符.
     * @throws WxPayException
     */
    public function ToXml()
    {
        if (!is_array($this->values)
            || count($this->values) <= 0) {
            throw new WxPayException('数组数据异常！');
        }

        $xml = '<xml>';
        foreach ($this->values as $key => $val) {
            $xml .= '<' . $key . '>' . $val . '</' . $key . '>';
        }
        $xml .= '</xml>';
        return $xml;
    }

    /**
     * 输出xml字符.
     * @throws WxPayException
     */
    public function ToXmlNew()
    {
        if (!is_array($this->values)
            || count($this->values) <= 0) {
            throw new WxPayException('数组数据异常！');
        }

        $xml = '<xml>';
        foreach ($this->values as $key => $val) {
            if (is_numeric($val)) {
                $xml .= '<' . $key . '>' . $val . '</' . $key . '>';
            } else {
                $xml .= '<' . $key . '><![CDATA[' . $val . ']]></' . $key . '>';
            }
        }
        $xml .= '</xml>';
        return $xml;
    }

    /**
     * 将xml转为array.
     * @param string $xml
     * @throws WxPayException
     */
    public function FromXml($xml)
    {
        if (!$xml) {
            throw new WxPayException('xml数据异常！');
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $this->values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $this->values;
    }

    /**
     * 格式化参数格式化成url参数.
     */
    public function ToUrlParams()
    {
        $buff = '';
        foreach ($this->values as $k => $v) {
            if ($k != 'sign' && $v != '' && !is_array($v)) {
                $buff .= $k . '=' . $v . '&';
            }
        }

        return trim($buff, '&');
    }

    /**
     * 生成签名.
     * @param string $key 公众号平台填写的加密key
     * @param mixed $apiKey
     */
    public function MakeSign($apiKey)
    {
        //签名步骤一：按字典序排序参数
        ksort($this->values);
        $string = $this->ToUrlParams();
        //签名步骤二：在string后加入KEY
        $string = $string . '&key=' . $apiKey;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        return strtoupper($string);
    }

    /**
     * 获取设置的值
     */
    public function GetValues()
    {
        return $this->values;
    }
}
