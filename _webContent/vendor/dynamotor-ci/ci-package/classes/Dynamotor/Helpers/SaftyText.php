 <?php

namespace Dynamotor\Helpers;

class SaftyText
{
	public function __construct(){}
	
	public static function int($str){
		if(is_string($str)){
			if( preg_match('/^\-?[0-9]+$/',$str)) return intval($str);
			return 0;
		}
		return intval($str);
	}
	
	public static function float($str){
		if(is_string($str)){
			 if(preg_match('/^\-?[0-9]+(\.[0-9]+)?$/',$str)) return floatval($str);
			 return 0;
		}
		return floatval($str);
	}
	
	public static function money($val,$currency='$',$length=2){
		$val = SaftyText:: float($val);
		$prefix = $val < 0?'- ':'';
		return sprintf('%s%s %0.'.$length.'f',$prefix,$currency, abs($val));
		
		return NULL;
	}
	
	public static function regExpText($str){
		return preg_replace("/([\(\)\/\?\:\*\.])/",'\\\\\1',$str);
	}
	
	public static function text($str){
		$str = preg_replace("/[\s]+/"," ",$str);
		$str = preg_replace("/[\n\r]+/","",$str);
		$str = htmlentities(str_replace("'","",str_replace("\"","&quot;",trim($str))));
		$str = str_replace("<","&lt;",str_replace(">","&gt;",$str));
		$str = str_replace("\n","",str_replace("\r","",$str));
		
		return $str;
	}
	
	public static function getParam($key){
		return self::saftyText(Yii::app()->request->getParam($key));
	}
	
	public static function getInt($key){
		return self::saftyInt(Yii::app()->request->getParam($key));
	}
}
