<?php

class Wx_service extends Base_Service
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_wx_params($params){
        $url = urldecode($params['url']);
        $timestamp = time();
        $wxConfig = $this->config->item('wx');
        $appId = $wxConfig['AppID'];
        $nonceStr = $this->getNonceStr();
        $accessToken = $this->GetAcessToken($wxConfig['AppID'],$wxConfig['AppSecret']);
        if(empty($accessToken)){
            return false;
        }
        $ticket = $this->GetJsApiTicket($accessToken);
        if(empty($ticket)){
            return false;
        }
        $signature_param_arr = array(//字典序
            'jsapi_ticket' => $ticket,
            'noncestr' => $nonceStr,
            'timestamp' => $timestamp,
            'url' => $url,
        );
        $signature_params = array();
        foreach ($signature_param_arr as $key => $value) {
            $signature_params[] = $key.'='.$value;
        }
        $str1 = implode('&', $signature_params);
        $signature = sha1($str1);
        $return_data = array(
            'appId' => $appId,
            'timestamp' => $timestamp,
            'nonceStr' => $nonceStr,
            'signature' => $signature,
            'url' => $url,
            'access_token' => $accessToken,
            'ticket' => $ticket,
        );
        return $return_data;
    }
    /**
     * 
     * 产生随机字符串，不长于32位
     * @param int $length
     * @return 产生的随机字符串
     */
    public function getNonceStr($length = 32) 
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {  
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);  
        } 
        return $str;
    }

    /** 
     * [http_curl 通用Curl方法] 
     * @param  [type] $url          [传入请求url地址] 
     * @param  string $request_type [发送请求类型，默认get] 
     * @param  string $data_type    [返回数据格式，默认json] 
     * @param  any    $arr          [post可能传入参数,默认为空] 
     * @return [type]               [返回值] 
     */  
    public function http_curl($url,$request_type='get',$data_type='json',$arr='')  
    {  
        $ch=curl_init();//初始化curl  
        curl_setopt($ch, CURLOPT_URL, $url);//定义一些curl参数  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
        if ($request_type=='post') {//如果是post请求  
            curl_setopt($ch, CURLOPT_POST, 1);  
            curl_setopt($ch, CURLOPT_POSTFIELDS, $arr);  
        }  
        $output=curl_exec($ch);//执行请求  
        if ($data_type=='json') {  
            if ( curl_errno($ch) ) {//如果请求出错，返回错误信息  
                return curl_error($ch);  
            }  
            else{//请求成功获取数据  
                $res=json_decode($output,true);  
            }  
            curl_close($ch);//curl关闭  
            return $res;//返回数据  
        }  
    } 
    /**
     * 
     * 获取accesstoken
     * @return 字符串
     */    
    public function GetAcessToken($appid,$appsecret)  
    {
        if (@$_SESSION['access_token']&&@$_SESSION['alive']>time()) {//token存在且未过期  
            $access_token=$_SESSION['access_token'];  
        }else{//token不存在或已过期  
            $url='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$appsecret;  
            $res = $this->http_curl($url);//这是自己写的curl方法，用于调用接口
            if(isset($res['access_token'])&&$res['access_token']){
                $_SESSION['access_token']= $access_token = $res['access_token'];  
                $_SESSION['alive']=time()+7000;
            }else{
                $access_token = '';
                log_message('error','get wx access_token fail.error message:'.$res['errmsg']);
            }
        }  
        return $access_token;  
    } 
    /**
     * 
     * 获取ticket
     * @return 字符串
     */    
    public function GetJsApiTicket($access_token)  
    {

        if (@$_SESSION['ticket']&&@$_SESSION['alive']>time()) {//ticket存在且未过期  
            $ticket=$_SESSION['ticket'];  
        }else{//ticket不存在或已过期  
            $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$access_token.'&type=jsapi';
            $res = $this->http_curl($url);
            if(isset($res['ticket'])&&!empty($res['ticket'])){
                $_SESSION['ticket']= $ticket = $res['ticket'];  
                $_SESSION['alive']=time()+7000;                  
            }else{
                $ticket = '';
                log_message('error','get wx jsapiticket fail.error message:'.$res['errmsg']);
            }
        }  
        return $ticket;  
    } 
}