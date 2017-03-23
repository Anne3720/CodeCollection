<?php
class Client{
	private static $yarclientArr=array();
	/**  php  Yar请求
	*参数
		*url    			yar接口地址
		*method_name   		请求接口名称
		*params 			请求参数
	**/
	public static function yar_client($url,$method_name,$params,$time_out=1)
	{
		$id=md5($url);
		if (self::$yarclientArr[$id]){
			// $client = self::$yarclientArr[$id];
		}else{
			try{
				self::$yarclientArr[$id] = new Yar_client($url);
				
			}catch(Exception $e){
				file_put_contents("{$GLOBALS['temp_dir']}/exception/yarException".date("Ymd").".log", date("Y-m-d H:i:s  ") ."    url:".$url."      method_name:".$method_name."       params:".json_encode($params)."    Exception: ".var_export($e->getMessage(),true) ."\n", FILE_APPEND);
				return false;
			}
			
		}
		$client=self::$yarclientArr[$id];
		
		/* the following setopt is optinal */
		//$client->SetOpt(YAR_OPT_CONNECT_TIMEOUT, $time_out); 

		//需要服务端支持keepalive
		//$client->SetOpt(YAR_OPT_PERSISTENT, 1); 
		//$client->SetOpt(YAR_OPT_TIMEOUT, 20); 
		/*Set packager to JSON ,defaul msgpack*/
		// $client->SetOpt(YAR_OPT_PACKAGER,'json');
		try{
			$result = call_user_func_array(array($client,$method_name), $params);
				
		}catch(Exception $e){
			file_put_contents("{$GLOBALS['temp_dir']}/exception/yarException".date("Ymd").".log", date("Y-m-d H:i:s  ") ."    url:".$url."      method_name:".$method_name."       params:".json_encode($params)."    Exception: ".var_export($e->getMessage(),true) ."\n", FILE_APPEND);
			return false;
		}
		return $result;
	}
	
	/**** 
		服务端swoole调用方法  By xiaosx 
		params:
			Server 				string  接口地址
			Module 				string  接口模块名
			Controller 			string  接口控制器名
			Action 				string  接口方法名
			Method 				string  请求方式
			params 				array   请求参数
			option 				array   其他选项
		result:
			data 				array   返回值
	****/
	public static function yars($Server,$Module,$Controller,$Action,$Method='get',$params=array(),$option=array())
	{	
		if(!isset($option['cache']) || empty($option['cache'])){
			$option['cache']=array(false,0);
		}
		if(!isset($option['mode']) || empty($option['mode'])){
			$option['mode']=array("swoole","yars");
		}
		if(!isset($option['port']) || empty($option['port'])){
			
			$option['port']=9501;
			
		}
		if(empty($Server) || empty($Module) || empty($Controller) ||empty($Action)) return false;
		try{
			$data = \Dj\Client::init($option['mode'][0])
			->setServer($Server)
			->setPort($option['port'])
			->setKeep(true)
			->setSpare($option['mode'][1])
			->setModule($Module)
			->setController($Controller)
			->setAction($Action)
			->setMethod($Method)
			->setTimeout(30)
			->autoCache($option['cache'][0],$option['cache'][1])
			->send($params);
		}catch(Exception $e){
			file_put_contents("{$GLOBALS['temp_dir']}/exception/swooleException".date("Ymd").".log", date("Y-m-d H:i:s  ") ."    Server:".$Server."      Module:".$Module."         Controller:".$Controller."     Action:".$Action."       params:".json_encode($params)."    Exception: ".var_export($e->getMessage(),true) ."\n", FILE_APPEND);
			return false;
		}
		
		return $data;
	}
	
	
}






?>
