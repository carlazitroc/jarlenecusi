<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if(!function_exists('print_backtrace')){
	function print_backtrace($message='',$title='',$printStack=true,$exit = true,$offset = 0){
		$stackStr = '';
		if(defined('DEBUG') && !DEBUG) $printStack = false;
		if($printStack ){
			$ary = debug_backtrace();
			for($i=0;$i<count($ary);$i++){
				$caller = '';
				if($i < $offset )continue;
				if(isset($ary[$i]['class'])) $caller.=$ary[$i]['class'];
				if(isset($ary[$i]['type'])) $caller.= $ary[$i]['type'];
				if(isset($ary[$i]['function'])) $caller.= $ary[$i]['function'];

			//if($ary[$i]['function'] == 'errorHandler') continue;
				if($ary[$i]['function'] == 'print_backtrace') continue;
				$stackStr.= "<li style=\"margin:5px 0 5px 5px;\">\n";
				$stackStr.= "<code>";
				$stackStr.= $caller ."(";
				$args = array();
				if(isset($ary[$i]['args'])){
					foreach($ary[$i]['args'] as $idx => $val){
						if(is_object($val)) $args[] = '&lt;Object>';
						elseif(is_array($val)) $args[] = ''.var_export($val,true).'';
						else $args[] = $val;
					}
				}
				$stackStr.= implode(',',$args);
				$stackStr.= ")</code>\n";
				$stackStr.= "<br />";
				if(isset($ary[$i]['file'])){
					$stackStr.= "<small><u>".$ary[$i]['file'];
					$stackStr.= "</u> @ ".$ary[$i]['line']."] </small>";
				}
				$stackStr.= "</li>\n";
			}
			$stackStr = "<ul style=\"list-style-type:square;margin:0;padding:0; padding-left:10px;\">\n$stackStr\n</ul>";
		}

		$str = "<div style=\"font-size:12px; text-align:left;\">\n<h1>$title</h1>\n<p>$message</p>\n$stackStr\n</div>";
		print $str;
		if($exit) exit();
	}
}

if(!function_exists('print_plain_backtrace')){
	function print_plain_backtrace($return = false,$offset = 0){
		$stackStr = '';

		$ary = debug_backtrace();
		
		for($i=0;$i<count($ary);$i++){
			$caller = '';
			if($i < $offset )continue;
			if(isset($ary[$i]['class'])) $caller.=$ary[$i]['class'];
			if(isset($ary[$i]['type'])) $caller.= $ary[$i]['type'];
			if(isset($ary[$i]['function'])) $caller.= $ary[$i]['function'];

		//if($ary[$i]['function'] == 'errorHandler') continue;
			if($ary[$i]['function'] == 'print_plain_backtrace') continue;
			$stackStr.= "\r\n\t";

			if(isset($ary[$i]['file'])){
				$stackStr.= "[".$ary[$i]['file'];
				$stackStr.= "@ ".$ary[$i]['line']."]";
			}else{
				$stackStr.= "[Unknown file]";
			}
			$stackStr.= "\t";

			$stackStr.= $caller ."(";
			$args = array();
			if(isset($ary[$i]['args'])){
				foreach($ary[$i]['args'] as $idx => $val){
					if(is_object($val)) $args[] = '&lt;Object>';
					elseif(is_array($val)) $args[] = '&lt;Array>';
					else $args[] = $val;
				}
			}
			$stackStr.= implode(',',$args);
			$stackStr.= ")";
			$stackStr.= "";
		}
		$stackStr = "\r\nBacktrace:$stackStr\r\n";

		if(!$return) print $stackStr;
		return $stackStr;
	}
}