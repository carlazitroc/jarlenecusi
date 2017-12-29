<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// A list of shortcut functions for accessing Cache driver

function cache_init(){
	global $LDR,$CFG;
	$CI = &get_instance();

	if(!isset($CI->cachehelper_ins)){

		$cache_path = $CI->config->item('cache_path');
		if(substr($cache_path,-1,1) != DS ){
			$cache_path.= DS;
			$CI->config->set_item('cache_path', $cache_path);
			
			if($CI->config->item('is_debug') )
				log_message('debug','cache_init/update cache path='.$cache_path);
		}else{
			if($CI->config->item('is_debug') )
				log_message('debug','cache_init/use cache path='.$cache_path);
		}
		
		$cfg = cache_get_config();
		$adapter = $cfg['adapter'];

		
		if($CI->config->item('is_debug') )
			log_message('debug','cache_helper//init, cache_config='.print_r($cfg,true));
		

		$CI->load->driver('cache',$cfg,'cachehelper_ins');

		if(!isset($CI->cachehelper_ins)){
			log_message('error', 'cache_helper//init, libray not exist ('.$adapter.')' );
			show_error('Cache Helper cannot load cache libray.'); return;
		}
	}
}

function cache_get_instance()
{
	global $LDR,$CFG;
	cache_init();
	$CI = &get_instance();

	return $CI->cachehelper_ins;
}

function cache_get_config(){
	global $LDR,$CFG;

	$CI = &get_instance();
		
	$cfg = $CFG->item('cache');
	$cache_path = $CFG->item('cache_path');

	if(!is_dir($cache_path)){
		@mkdir($cache_path,0777);
	}

	if(empty($cfg) || !is_array($cfg['adapter'])){	
		$cfg= array('adapter' => 'file');
	}

	return $cfg;
}

function cache_is_allowed(){
	global $LDR,$CFG;
	cache_init();
	$CI = &get_instance();

	$cfg = cache_get_config();
	$adapter = $cfg['adapter'];

	return $CI->cachehelper_ins->is_supported($adapter);
}

function cache_filename($path){
	if(is_array($path)) $path = md5(serialize($path));
	$nodes = explode('/',strtolower($path),10);
	return implode('_',$nodes);
}

function cache_remove_all(){

	if(!cache_is_allowed()){
		log_message('error', 'cache_helper//init, adapter does not supported');
		return;
	}
	$CI = &get_instance();

	$CI->cachehelper_ins->clean();
}

// remove cache file by passing path components
// wild-card * is supported for matching with name
// for example, passing "some/object/*/get" will remove all matched format cache 
function cache_remove($path=''){

	if(!cache_is_allowed()){
		log_message('error', 'cache_helper//init, adapter does not supported');
		return;
	}
	$CI = &get_instance();
	
	$cache_list = $CI->cachehelper_ins->cache_info();
	//$cache_list = $cache_info['cache_list'];
	$filename = cache_filename($path);

	$paths = array();
	$counter = 0;
	if(preg_match("/\*/",$filename)){
		
		$pattern = $filename;
		$pattern = str_replace("_","\\_",$pattern);
		$pattern = str_replace("/","\\_",$pattern);
		$pattern = str_replace("*",".+",$pattern);
		$pattern = "/".$pattern."/";
			if($CI->config->item('debug_mode') == 'yes')
		log_message('debug','cache_remove/wildcard.path='.$path.',pattern='.$pattern);
		
		if(!empty($cache_list)){
			foreach($cache_list as $cache_key => $cache_item_info){
				if($cache_key == $filename || preg_match($pattern, $cache_key)){
			if($CI->config->item('debug_mode') == 'yes')
					log_message('debug','cache_remove/wildcard.path/matched='.$cache_key);
					$paths[] = $cache_key;
					$counter ++;
				}
			}

		}else{
			if($CI->config->item('debug_mode') == 'yes')
			log_message('info','cache_remove/wildcard.path/empty='.$filename);
		}
	}else{
		if(isset($cache_list[$filename])){
			$paths[] = $filename;
			$counter ++;
			if($CI->config->item('debug_mode') == 'yes')
			log_message('debug','cache_remove/static.path='.$filename);
		}
	}

			if($CI->config->item('debug_mode') == 'yes')
	log_message('debug','cache_remove/matched.path='.$filename.'('.$counter.')');

	if($counter>0){
		foreach($paths as $idx => $cache_key){
			log_message('debug','cache_remove/removing.path='.$cache_key);
			$CI->cachehelper_ins->delete($cache_key);
		
		}
	}
	return;
}

function cache_get($path=''){

	if(!cache_is_allowed()){
		log_message('error', 'cache_helper//init, adapter does not supported');
		return;
	}
	$CI = &get_instance();
	
	$filename = cache_filename($path);
	$data = $CI->cachehelper_ins->get( $filename);
	
	return $data;
}

function cache_set($path='',$data=NULL, $ttl=3600){

	if(!cache_is_allowed()){
		log_message('error', 'cache_helper//init, adapter does not supported');
		return;
	}
	$CI = &get_instance();

	$filename = cache_filename($path);

	if($CI->config->item('debug_mode') == 'yes')
		log_message('debug', 'cache_set('.$ttl.':'.print_r($path,true).')');
	$CI->cachehelper_ins->save( $filename, $data, $ttl );
}

// Alias function for old version.
function cache_save($path='',$data=NULL, $ttl=3600){
	call_user_func('cache_set',$path,$data,$ttl);
}

//cache_init();