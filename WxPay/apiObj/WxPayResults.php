<?php
namespace WxPay\apiObj;

use WxPay\WxPayException;
/**
 * 
 * 接口调用结果类
 * @author widyhu
 *
 */
class WxPayResults extends WxPayDataBase
{
	/**
	 * 检测签名
	 * @param 用作生成签名的加密key
	 * @throws WxPayException
	 * @return boolean
	 */
	public function CheckSign($apiKey)
	{
		//fix异常
		if(!$this->IsSignSet()){
			throw new WxPayException("签名错误！123");
		}
		
		$sign = $this->MakeSign($apiKey);
		if($this->GetSign() == $sign){
			return true;
		}
		throw new WxPayException("签名错误！456");
	}
	
	/**
	 * 
	 * 使用数组初始化
	 * @param array $array
	 */
	public function FromArray($array)
	{
		$this->values = $array;
	}
	
	/**
	 * 
	 * 使用数组初始化对象
	 * @param array $array
	 * @param 是否检测签名 $noCheckSign
	 * @param 用作生成签名的加密key
	 */
	public static function InitFromArray($array, $noCheckSign = false, $apiKey = '')
	{
		$obj = new self();
		$obj->FromArray($array);
		if($noCheckSign == false){
			$obj->CheckSign($apiKey);
		}
        return $obj;
	}
	
	/**
	 * 
	 * 设置参数
	 * @param string $key
	 * @param string $value
	 */
	public function SetData($key, $value)
	{
		$this->values[$key] = $value;
	}
	
    /**
     * 将xml转为array
     * @param string $xml
     * @param 用作生成签名的加密key
     * @throws WxPayException
     */
	public static function Init($xml, $apiKey)
	{	
		$obj = new self();
		$obj->FromXml($xml);
		//fix bug 2015-06-29
		if($obj->values['return_code'] != 'SUCCESS'){
			 return $obj->GetValues();
		}
		$obj->CheckSign($apiKey);
        return $obj->GetValues();
	}
}