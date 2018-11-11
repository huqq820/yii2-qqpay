<?php
namespace huqq\qqpay;
use yii\base\Component;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use huqq\qqpay\lib\QqPayConfig;
use huqq\qqpay\lib\QqPayUnifiedOrder;
use huqq\qqpay\lib\QqPayApi;

/**
 * 入口文件
 * @author huqq
 */
class QqPay extends Component
{
    public $app_id;
    public $mch_id;
    public $key;
    public $app_secret;
    public $ssl_cert_path;
    public $ssl_key_path;
    public $notify_url;
    public $curl_proxy_host = '0.0.0.0';
    public $curl_proxy_port = 0;
    public $report_level = 1;
    
    public function init()
    {
        parent::init();
        if (!isset($this->app_id)) {
            throw new InvalidConfigException('请先配置app_id');
        }
        if (!isset($this->mch_id)) {
            throw new InvalidConfigException('请先配置mch_id');
        }
        if (!isset($this->key)) {
            throw new InvalidConfigException('请先配置key');
        }
        if (!isset($this->app_secret)) {
            throw new InvalidConfigException('请先配置app_secret');
        }
        if (!isset($this->notify_url)) {
            throw new InvalidConfigException('请先配置使用的notify_url');
        }
        QqPayConfig::$qqpay = $this;
    }
    
    /**
     * 支付统一下单请求发送
     * @param  Array $request 请求参数
     * @param  string $notify_url 微信通知地址
     * @return $result 成功时返回，参数错误抛异常
     */
    public static function sendUnifiedorderRequest($request)
    {
        $time_start = date("YmdHis");
        //创建请求数据对象
        $input = new QqPayUnifiedOrder();
        $input->SetBody($request['body']);  //商品描述
        $input->SetOut_trade_no($request['out_trade_no']);  //商户订单号
        $input->SetTotal_fee($request['total_fee']);  //金额(分)
        $input->SetTime_start($time_start);  //订单生成时间
        //$input->SetTime_expire(date("YmdHis", time() + 1800));  //交易订单失效时间
        //$input->SetGoods_tag("default");  //商品标记
        $input->SetNotify_url($request['notify_url']);  //通知地址
        $input->SetTrade_type("APP");  //交易类型
        $input->SetFee_type("CNY");
    
        //发送统一下单请求到商户平台
        $result = QqPayApi::unifiedOrder($input);
        //发起支付失败
        if($result['result_code'] != 'SUCCESS'){
            return array();
        }
    
        //拼接返回给APP的支付参数
        $response = array(
            'result_code' => $result['result_code'],
            'app_id' => QqPayConfig::APPID(),
            'mch_id' => QqPayConfig::MCHID(),
            'prepay_id' => $result['prepay_id'],
            'sign' => $result['sign'],
            'nonce_str' => $result['nonce_str'],
            'time_start' => $time_start,
        );
        return $response;
    }
    
}