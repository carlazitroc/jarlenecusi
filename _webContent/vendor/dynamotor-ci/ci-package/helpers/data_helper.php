<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function json_output($a,$returned = false,$options=false){
	$fileName = isset($options['fileName']) ? $options['fileName'] : 'data'.date("YmdHis").md5(microtime(true));
	
	if(isset($options['callback']) && !empty($options['callback'])){
		$str = $options['callback']."(".json_encode($a).")";

		$type = 'js';
		if(isset($options['script'])) {
			$type = 'html';
			$str = '<script>'.$str.'</script>';
		}
		if($returned) return $str;
		if($type == 'js'){
			@header("Content-type:text/javascript");
			//@header('Content-Disposition: inline; filename="'.$fileName.'.js"');
		}
		print $str;
		exit;
	}
	if($returned) return json_encode($a);
	@header("Content-type:text/plain");
	//@header('Content-Disposition: inline; filename="'.$fileName.'.json"');
	print json_encode($a);
	exit;
}
function xml_output($a,$returned = false,$options=false){
	$fileName = isset($options['fileName']) ? $options['fileName'] : 'data'.date("YmdHis").md5(microtime(true));
	$charset = isset($options['charset']) ? $options['charset'] : 'utf-8';
	$rootName = isset($options['rootName']) ? $options['rootName'] : 'result';
	
	if($returned)
		return array_to_xml($a,$charset,$rootName);
	@header("Content-type:text/xml; charset=".$charset);
	//@header('Content-Disposition: inline; filename="'.$fileName.'.xml"');
	print array_to_xml($a,$charset,$rootName);
	exit;
}
function plist_output($a,$returned = false,$options=false){
	$fileName = isset($options['fileName']) ? $options['fileName'] : 'data'.date("YmdHis").md5(microtime(true));
	$charset = isset($options['charset']) ? $options['charset'] : 'utf-8';
	$type = isset($options['type']) ? $options['type'] : 'xml';
	
	$rst = NULL;
	if($type == 'text'){
		if(!$returned)
			@header("Content-type:text/plain; charset=".$charset);
			//@header("Content-type:application/x-apple-plist; charset=".$charset);
		$rst = plist_encode_text($a);
	}else{
		if(!$returned)
			@header("Content-type:text/xml; charset=".$charset);
		$rst = array_to_xml_plist($a);
	}
	if($returned)
		return $rst;
	//@header('Content-Disposition: inline; filename="'.$fileName.'.plist"');
	print $rst;
	exit;
}
function array_to_xml_plist($source,$level=0,$version='1.0',$encoding='utf-8'){
	$root_type = 'plist';
	if(is_array($source)){
		if(isset($source[0])){
			$root_type = 'array';
		}else{
			$root_type = 'dict';
		}
	}
	$prefix_char = '	';
	$prefix = str_pad('',$level,$prefix_char,STR_PAD_LEFT);
	
	$string = '';
	if($level <=0){
		$string = '<?xml version="1.0" encoding="'.$encoding.'"?>'."\n";
		$string.= '<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">'."\n";
		$string.= '<plist version="'.$version.'">'."\n";
	}
	$string.= $prefix.'<'.$root_type.'>'."\n";
	if(is_array($source)){
		foreach($source as $key => $row){
			
			if(is_null($row) ) continue;
			if($root_type == 'dict' || $root_type == 'plist'){
				$string.= $prefix.$prefix_char.'<key>'.$key.'</key>'."\n";
			}
			if(is_int($row)){
				$string.= $prefix.$prefix_char.'<integer>'.$row.'</integer>'."\n";
			}elseif( is_float($row) || is_double($row)){
				$string.= $prefix.$prefix_char.'<real>'.$row.'</real>'."\n";
			}elseif(is_bool($row)){
				$string.= $prefix.$prefix_char.($row==true ? '<true/>':'<false/>')."\n";
			}elseif(is_string($row) && preg_match('/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}T[0-9]{2}\:[0-9]{2}/',$row)){
				$string.= $prefix.$prefix_char.'<date>'.$row.'</date>'."\n";
			}elseif(is_string($row)){
				$string.= $prefix.$prefix_char.'<string><![CDATA['.$row.']]></string>'."\n";
			}elseif(is_array($row)){
				$string.= array_to_xml_plist($row,$level+1);
			}
		}
	}
	
	$string.= $prefix.'</'.$root_type.'>'."\n" ;
	if($level <=0){
		$string.='</plist>';
	}
	return $string;
}
/////////////////////////////////////////////////////////////////////////////////////
// source : http://blog.51yip.com/php/660.html
function array_to_xml($source,$charset='utf-8',$rootName='result') {
	if(empty($source)){
		return false;
	}
	$j = json_encode(array($rootName=>$source));
	$array = json_decode($j); 
	$xml  ='<'.'?'.'xml version="1.0" encoding="'.$charset.'"?'.'>'."\n";
	$xml .= _array_to_xml_change($array);
	return $xml;
}
function _array_to_xml_change($source) {
	if(is_string($source)) return '<![CDATA['.$source.']]>';
	//if(!is_array($source) && !is_object($source))return '';
	$string="";
	foreach($source as $k=>$v){
		$content = '';
		
		if(is_bool($v)){
			$content.= "<".$k.">";
			$content.= $v ? 'true':'false';
			$content.= "</".$k.">\n";
		}elseif(is_int($v) || is_float($v) || is_double($v)){
			$content.= "<".$k.">";
			$content.= $v ;
			$content.= "</".$k.">\n";
		}elseif(is_string($v)){
			$content.= "<".$k.">";
			$content.= '<![CDATA['.$v.']]>';      // append to the body as CDATA node
			$content.= "</".$k.">\n";
		}elseif(is_array($v)){
			
			foreach($v as $idx =>$r){
				$content.= "<".$k.">\n";
				$content.= _array_to_xml_change($r);  
				$content.= "</".$k.">\n";
			}
			
		}elseif(is_object($v)){		 // ensure the variable is an array or object
			$content.= "<".$k.">\n";
			$content.= _array_to_xml_change($v);  
			$content.= "</".$k.">\n";
		}else{
			// do nothing
			$content.= "<".$k.">";
			$content.= $v;                        // append to the body as simple content
			$content.= "</".$k.">\n";
		}
		
		$string .= $content;
		//$string .="";
	}
	return $string;
}


function seo_string($string='',$separator = '-', $allowed_uppercase = FALSE){
	$ts = array("/[À-Å]/","/Æ/","/Ç/","/[È-Ë]/","/[Ì-Ï]/","/Ð/","/Ñ/","/[Ò-ÖØ]/","/×/","/[Ù-Ü]/","/[Ý-ß]/","/[à-å]/","/æ/","/ç/","/[è-ë]/","/[ì-ï]/","/ð/","/ñ/","/[ò-öø]/","/÷/","/[ù-ü]/","/[ý-ÿ]/");
    $tn = array("A","AE","C","E","I","D","N","O","X","U","Y","a","ae","c","e","i","d","n","o","x","u","y");
    //$string = preg_replace($ts,$tn, $string);
    //$accents_regex = '~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i';
    $special_cases = array( '&' => $separator.'and'.$separator,'+'=>$separator.'plus'.$separator);
    if(!$allowed_uppercase)
	    $string = mb_strtolower(  $string , 'UTF-8' );
    $string = str_replace( array_keys($special_cases), array_values( $special_cases), $string );
    //$string = preg_replace( $accents_regex, '$1', htmlentities( $string, ENT_QUOTES, 'UTF-8' ) );
    $string = preg_replace("/\s+/u", "$separator", $string);
    $string = preg_replace("/$separator+/u", $separator, $string);
    return $string;
    
}
