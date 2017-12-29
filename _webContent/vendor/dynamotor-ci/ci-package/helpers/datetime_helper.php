<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function get_system_timezone()
{
	if(isset($GLOBALS['system_timezone'])) return $GLOBALS['system_timezone'];
	$CI =& get_instance();
	
	$CI->load->config('datetime');
	return $GLOBALS['system_timezone'] = $CI->config->item('timezone');
}

function current_time()
{
	return time() + get_system_timezone()  * 3600;
}

function format_date($format='',$date=false){
	$time = strtotime($date);
	$timezone = 0;
	if(!$date) {
		$time = time() + get_system_timezone()  * 3600;
	}
	return time_to_date($format, $time);	
}

function time_to_date($format='',$time=-1){
	if(strlen(trim($format)) <1) $format = "Y-m-d H:i:s";
	if($time <0 ) $time = current_time();
	return gmdate($format,$time);
}
