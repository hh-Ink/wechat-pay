# wechat-pay

# 做这个包的原因

    我们公司有（ 微信app支付、微信公众号支付 ）两个商户号，微信官网给的 sdk 在 `lib\\WxPay.Config.php` 里面配置项都是`const`修饰所以只能配置一个商户号的信息，这样的问题就是：两个商户号就要有两个 sdk，在不同的 function 分别调用`WxPayApi()`没有问题，可是一旦在同一个 function 使用就会告诉报类重复定义的错误。第一次遇到这个问题是在自动退款脚本，不能使用一个退款脚本，所以定义了两个。但后来代码重构，将相同的逻辑写成 serverApi ，如果 serverApi 也写两个方法就失去了重构的意义，所以就决定重新定义一下官网的 sdk。这个包就这样诞生了

# 对微信官网支付sdk做了以下几点优化：

-  对`WxPayDataBase.php`做了拆分，将每个接口对应的类拆分成了单独的类
-  全部支持命名空间
-  支持多个商户号分别调用
  
# 说明
  
-  为了兼容商户号自定义配置，将 `lib\WxPay.Config.php` 里的配置都使用 `static` 替换 `const` ，`WxPayApi()` 和 `JsApiPay()` 都继承了 `WxPayConfig` ，所以在使用 `WxPayApi()` 和 `JsApiPay()` 时都需要先做配置
-  由于加密 KEY 需要配置，所以在使用 `SetSign()` 、 `MakeSign()` 、 `Handle()` 、 `CheckSign()` 、 `WxPayResults::Init()` 都增加了 `$apiKey` 参数

# 安装
```comporser
  composer require xuzhen/wechat-pay：dev-master
```

# 使用

```php
use WxPay\apiObj\WxPayOrderQuery;
use WxPay\WxPayApi;

$transaction_id = 'transaction_id';
$input = new WxPayOrderQuery();
$input->SetTransaction_id($transaction_id);

# 配置方法一
WxPayApi::$APPID = 'your_app_id';
WxPayApi::$MCHID = 'your_mch_id';
WxPayApi::$KEY   = 'your_key';
print_r(WxPayApi::orderQuery($input));

# 配置方法二
$configArr = [
 'APPID' => 'your_app_id',
 'MCHID' => 'your_mch_id',
 'KEY'   => 'your_key'
];
print_r( (new WxPayApi($configArr)) -> orderQuery($input));
```
