<?php

class QqPayData
{
    /**
     * 获取通知接口发送的参数
     * @return array|mixed
     * @throws lib\QqPayException
     */
    public static function getNotifyParams($xml)
    {
        //实例化sdk基类数据对象
        $data = new \QqPayDataBase();
        $array = $data->FromXml($xml);
    
        //检测通信是否成功
        if(@$array['return_code'] != 'SUCCESS'){
            self::respondFail('获取通知接口参数失败:通信失败' . $xml);
        }
    
        //校验签名
        if(@$array['sign'] != $data->MakeSign()){
            self::respondFail('获取通知接口参数失败:签名校验失败');
        }
    
        //        self::writeLog(var_export($array,true));  //记录微信发送的参数到日志
        return $array;
    }
    
    /**
     * 生成返回给APP之前的第二次签名
     * @param $params
     * @return string
     */
    public static function getSign($params) {
        ksort($params);        //将参数数组按照参数名ASCII码从小到大排序
        foreach ($params as $key => $item) {
            if (!empty($item)) {         //剔除参数值为空的参数
                $newArr[] = $key.'='.$item;     // 整合新的参数数组
            }
        }
        $stringA = implode("&", $newArr);         //使用 & 符号连接参数
        $stringSignTemp = $stringA."&key=".QqPayConfig::KEY;        //拼接key
        // key是在商户平台API安全里自己设置的
        $stringSignTemp = MD5($stringSignTemp);       //将字符串进行MD5加密
        $sign = strtoupper($stringSignTemp);      //将所有字符转换为大写
        return $sign;
    }
    
    
    /**
     * 回复微信通知接口通知失败
     * @param $msg
     */
    public static function respondFail($msg)
    {
        header("Content-type: text/xml");
        echo self::toXml(
            array(
                'root' => array(
                    'return_code' => 'FAIL',
                    'return_msg' => $msg
                )
            ),
            false
            );
        exit;
    }
    
    /*
     * 回复微信通知接口通知成功
     */
    public static function respondSuccess()
    {
        header("Content-type: text/xml");
        echo self::toXml(
            array(
                'root' => array(
                    'return_code' => 'SUCCESS',
                    'return_msg' => 'OK'
                )
            ),
            false
            );
        exit;
    }
    
    
    /**
     * 把XML转换为数组
     * @param $xml
     * @return mixed
     */
    public static function toArray($xml){
        //禁止引用外部xml实体
    
        libxml_disable_entity_loader(true);
    
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
    
        $array = json_decode(json_encode($xmlstring),true);
    
        return $array;
    }
    
    
    /**
     *  将多维数组转换为xml
     *  @param array $array  要转换的数组
     *  @param bool $root   是否要根节点
     *  @return string     xml字符串
     */
    public static function toXml($array, $root = true, $charset=""){
        //参数不为数组或数组为空直接返回false
        if(!is_array($array) || count($array) <= 0) {
            return false;
        }
    
        $xml = "";
        if($root) {
            if($charset){
                $xml .= '<?xml version="1.0" encoding="' . $charset . '"?>';
            }
        };
    
        //拼装xml字符串
        foreach($array as $key => $val){
            if(is_array($val)){
                $child = self::toXml($val, false);
                $xml .= "<$key>$child</$key>";
            }else{
                $xml.="<".$key.">".$val."</".$key.">";
            }
        }
        return $xml;
    }
}