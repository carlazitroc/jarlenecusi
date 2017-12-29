<?php 

namespace Dynamotor\Helpers;

class ResourceHelper
{


	static function get_file($file_id, $options = NULL, $cache_ttl = 3600){
		
		// load cache helper if it does not exist
		$CI = &get_instance();

		return $CI->resource->get_file($file_id, $options, $cache_ttl);

	}

	static function reset_file_cache($r){
		// load cache helper if it does not exist
		$CI = &get_instance();

		return $CI->resource->reset_file_cache($r);
	}


	static function get_album($album_id, $options = NULL, $cache_ttl = 3600){
		// load cache helper if it does not exist
		$CI = &get_instance();

		return $CI->resource->get_album($album_id, $options, $cache_ttl);
	}

	static function reset_album_cache($r){
		// load cache helper if it does not exist
		$CI = &get_instance();
		
		return $CI->resource->reset_album_cache($r);
	}
}