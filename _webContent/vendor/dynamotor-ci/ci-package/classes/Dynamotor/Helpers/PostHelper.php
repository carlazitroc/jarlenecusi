<?php

namespace Dynamotor\Helpers;

use Dynamotor\Modules\PostModule;

class PostHelper
{

	static $CI;
	static $instances = array();
	static function get_section($section = 'page') {
		if (!isset(PostHelper::$instances[$section])) {
			return PostHelper::create($section);
		}
		return PostHelper::$instances[$section];
	}

	static function is_allowed($section = 'page') {
		if (empty(PostHelper::$CI)) {
			PostHelper::$CI = &get_instance();
		}

		$CI       = PostHelper::$CI;
		$sections = $CI->config->item('ph_sections');
		
		if($CI->config->item('is_debug')){
			log_message('debug','PostHelper::is_allowed, sections= '.print_r($sections, true));
		}

		return isset($sections[$section]);
	}

	static function create($section = 'page', $config = NULL) {
		if (!PostHelper::is_allowed($section)) {
			log_message('debug','PostHelper::create, cannot create new instance for section "'.$section.'"');
			return NULL;
		}
		$CI       = PostHelper::$CI;

		if(empty($config)){

			$sections = $CI->config->item('ph_sections');
			if (isset($sections[$section])) {
				$config = $sections[$section];
			}
		}

		$className = isset($config['class']) ? $config['class'] : 'Dynamotor\\Modules\\PostModule';
		if(!class_exists($className))
			return $this->error(0, 'Unknown PostModule defined');



		$ins = new $className($section, $config);
		PostHelper::$instances[$section] = $ins;

		return $ins;
	}

}

/** Used for different type of section **/

function ph_create($section, $config = NULL) {
	return PostHelper::create($section, $config);
}

function ph_get_album($album_id,$ttl=3600){
	return ResourceHelper::get_album($album_id,$ttl);
}

function ph_get_file($file,$ttl=3600){
	return ResourceHelper::get_file($file,$ttl);
}

function ph_get_category_path($section, $category_id, $is_live = '1', $options = NULL) {
	$ins = ph_create($section);
	if (!$ins) {
		return NULL;
	}

	return $ins->get_category_path($category_id, $is_live, $options);
}

function ph_path_data($section, $path, $options = NULL) {
	$ins = ph_create($section);
	if (!$ins) {
		return NULL;
	}

	return $ins->path_data($path, $options);
}

function ph_find_child_category_ids($section, $parent_id = NULL, $is_live = '1', $options = NULL) {
	$ins = ph_create($section);
	if (!$ins) {
		return NULL;
	}

	return $ins->find_child_category_ids($parent_id, $is_live, $options);
}

function ph_find_posts($section, $offset = 0, $limit = 30, $options = NULL) {
	$ins = ph_create($section);
	if (!$ins) {
		return NULL;
	}

	return $ins->find_posts($offset, $limit, $options);
}

function ph_read_post($section, $options = NULL){
	if(!is_array($options)){
		$options = array('_mapping'=>$options);
	}
	if(!isset($options['is_live'])) $options['is_live'] = '1';
	if(!isset($options['status'])) $options['status'] = '1';

	$result = ph_find_posts($section, 0, 1, $options);
	if(isset($result['data'][0]['id'])){
		return $result['data'][0];
	}
	return NULL;
}

function ph_tree_data($section, $segments, $offset = 0, $total = -1) {
	$ins = ph_create($section);
	if (!$ins) {
		return NULL;
	}

	return $ins->tree_data($segments, $offset, $total);
}

function ph_post_mapping_row($section, $row) {
	$ins = ph_create($section);
	if (!$ins) {
		return NULL;
	}

	return $ins->post_mapping_row($segments, $row);
}
