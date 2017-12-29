<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use Dynamotor\Helpers\PostHelper;
use Dynamotor\Helpers\ResourceHelper;

function menu_get($sys_name, $is_live=FALSE,$cache_time=3600){
	$CI = &get_instance();
	$CI->load->model('menu_model');
	$CI->load->model('menu_item_model');
	$CI->load->model('text_locale_model');
	$CI->load->helper('cache');

	$is_refresh  = $CI->config->item('is_refresh') == true;
	if($is_live === FALSE){
		$is_live = $CI->config->item('is_live');
	}

	$is_preview = $CI->config->item('preview_mode') == true;


	$source_types = $CI->config->item('menu_source_types');

	$locale_code = $CI->lang->locale();

	$cache_key = 'menu/'.$sys_name.'/'.$locale_code.'';
	$list_row = cache_get($cache_key);

	if(((empty($list_row) || $is_refresh) && $cache_time> 0) || $is_preview){
		$list_row = $CI->menu_model->read(array('_mapping'=> $sys_name, 'status'=>'1', 'is_live'=> $is_live, '_available_date'=>time_to_date(), '_with_locale'=>$locale_code));

		// save into cache file
		if($cache_time>0){
			if(!$is_preview)
			cache_set($cache_key, $list_row, $cache_time);
		}
	}
	if(empty($list_row['id'])) return NULL;

	$vals = array();
	$vals['id'] = $list_row['id'];
	$vals['title'] = $list_row['loc_title'];
	$vals['child'] = array();


	$cache_key = 'menu/'.$sys_name.'/'.$locale_code.'/content';

	$child_rows = cache_get($cache_key);
	if(((empty($child_rows) || $is_refresh) && $cache_time> 0) || $is_preview){
		$child_rows = $CI->menu_item_model->find(array('menu_id'=> $list_row['id'],'is_live'=>$is_live,'_order_by'=>array('sequence'=>'asc')));

		// save into cache file
		if($cache_time>0){
			if(!$is_preview)
				cache_set($cache_key, $child_rows, $cache_time);
		}
	}

	// Load PostHelper's configuration
	$CI->load->config('ph');

	if(is_array($child_rows) && !empty($child_rows)){
		foreach($child_rows as $idx => $raw_row){
			if(empty($raw_row['type'])) continue;
			$row = array();
			$row['id'] = $raw_row['id'];
			$row['type'] = $raw_row['type'];
			$row['sequence'] = $raw_row['sequence'];
			$row['ref_table'] = $raw_row['ref_table'];

			$row['ref_id'] = $raw_row['ref_id'];
			$row['url'] = '';
			$row['title'] = '';
			$row['content'] = '';
			$row['description'] = '';
			$row['parameters'] = $raw_row['parameters'];
			$row['cover'] = NULL;

			//if(!empty($source_types[ $raw_row['ref_table']]['label']))
			//	$row['ref_table_str'] = lang($source_types[ $raw_row['ref_table']]['label']);

			if($row['type'] == 'db'){
				if(substr($row['ref_table'],0,3) == 'ph_' && !empty($row['ref_id'])){
					// Format: ph_{type}

					$pairs = explode('.',substr($row['ref_table'],3),3);

					if(count($pairs)>=2){
						$type = $pairs[0];
						$section = $pairs[1];


						$ph = PostHelper::get_section($section );

						if(empty($ph)){

							log_message('debug','menu_helper//menu_get: unsupported ph section for menu item = '.$section );
						}else{
							log_message('debug','menu_helper//menu_get: support ph section for menu item = '.$section );

							$model_name = $type.'_model';
							$ph_cache_key = 'ph/'.$type.'/'.$raw_row['ref_id'].'/'.$locale_code.'/menu';

							if(!isset($ph->$model_name)){

								log_message('debug','menu_helper//menu_get: unsupported ph model = '.$model_name);
								continue;
							}


							// Try to get content by cache
							$ref_row = cache_get($ph_cache_key);

							// If cache does not exist or client request clear cache.
							if(empty($ref_row['id']) || $is_refresh || $is_preview){

								if($is_refresh) 
									cache_remove($ph_cache_key);

								$options = array('id'=> $raw_row['ref_id'],'is_live'=>$is_live);
								if($ph->is_localized) $options['_with_locale'] = $locale_code;

								
								$ref_row = $ph->$model_name->read($options);

								if(isset($ref_row['id'])){
									if(!$is_preview)
										cache_set($ph_cache_key, $ref_row, $cache_time);
								}
							}

							if(empty($ref_row['id'])){

								log_message('debug','menu_helper//menu_get: target object does not exist. section='.$section.', type='.$type.', id='.$raw_row['ref_id'].', is_live='.$is_live);
							}else{
								log_message('debug','menu_helper//menu_get: target object found. section='.$section.', type='.$type.', id='.$raw_row['ref_id'].', is_live='.$is_live);

								$locale_prefix = $CI->config->item('default_locale') == $CI->lang->locale() ? '' : $CI->lang->locale().'/'; 
								$row['url'] = web_url($locale_prefix.($section == $CI->config->item('ph_section_default') ? '' : $section.'/' ).($type == 'post' ? '' : $type.'/').$ref_row['_mapping']);
								//$row['ref_mapping'] = $ref_row['_mapping'];

								$row['title'] = $ref_row['title'];
								$row['description'] =  $ref_row['description'];

								if($ph->is_localized && (!isset($ref_row['loc_status']) || ($ref_row['loc_status']  == '1'))){
									$row['title'] =  $ref_row['loc_title'];
									$row['description'] = $ref_row['loc_description'];
								}

								if(!empty($ref_row['cover_id'])){
									$file_row = ResourceHelper::get_file($ref_row['cover_id']);

									// try to expode the cover image by asking controller
									if(method_exists($CI,'_picture_mapping')){
										$row['cover'] = $CI->_picture_mapping($file_row, 'file','source');
									}else{
										$row['cover']['url'] = picture_url($file_row, 'file','source');
									}

								}

							}
						}
					}
				}else{
					log_message('debug','menu_helper//menu_get: unsupported db type for menu item = '.print_r($row,true));

				}
			}elseif($row['type'] =='custom_link' || $row['type'] =='link'){
				$p = json_decode(json_encode($raw_row['parameters']));

				if(!empty($p->href)){
					$_loc_str = is_object($p->href) ? $p->href->$locale_code : $p->href;
					$_loc_str = preg_match('#^[a-z0-9]+\:\/\/.+$#', $_loc_str) ? $_loc_str : base_url($_loc_str);
					$row['url'] = stext($_loc_str,array('locale_code'=>$locale_code,'locale'=>$locale_code));
				}
				
				if(!empty($p->title)){
					$_loc_str = is_object($p->title) ? $p->title->$locale_code : $p->title;
					$row['title'] = $_loc_str;
				}

				if(!empty($p->content)){
					$_loc_str = is_object($p->content) ? $p->content->$locale_code : $p->content;
					$row['content'] = $_loc_str;
				}

				if(!empty($p->description)){
					$_loc_str = is_object($p->description) ? $p->description->$locale_code : $p->description;
					$row['description'] = $_loc_str;
				}

				if(!empty($p->target))
					$row['target'] = $p->target;

				if(!empty($p->loc->$locale_code->title))
					$row['title'] = $p->loc->$locale_code->title;

				if(!empty($p->loc->$locale_code->content))
					$row['content'] = $p->loc->$locale_code->content;

				if(!empty($p->loc->$locale_code->description))
					$row['description'] = $p->loc->$locale_code->description;



				if(!empty($raw_row['parameters']['cover_id'])){
					$_loc_str = is_array($raw_row['parameters']['cover_id']) ? $raw_row['parameters']['cover_id'][ $locale_code] : $raw_row['parameters']['cover_id'];
					if(!empty($_loc_str)){
						$file_row = ResourceHelper::get_file($_loc_str);
						if(!empty($file_row['id'])){
							if(method_exists($CI,'_picture_mapping')){
								$row['cover'] = $CI->_picture_mapping($file_row, 'file','source');
							}else{
								$row['cover']['url'] = picture_url($file_row, 'file','source');
							}
						}
					}
				}
			}

			if(!empty($raw_row['parameters']['custom_title'])){
				$_loc_str = is_array($raw_row['parameters']['custom_title']) ? $raw_row['parameters']['custom_title'][$locale_code] :$raw_row['parameters']['custom_title'];
				if(!empty($_loc_str))
					$row['title'] = $_loc_str;
			}

			if(!empty($raw_row['parameters']['custom_url'])){
				$_loc_str = is_array($raw_row['parameters']['custom_url']) ? $raw_row['parameters']['custom_url'][$locale_code] :$raw_row['parameters']['custom_url'];
				if(!empty($_loc_str))
					$row['url'] = stext($_loc_str,array('locale_code'=>$locale_code,'locale'=>$locale_code));
			}

			if(!empty($raw_row['parameters']['custom_cover_id'])){
				$_loc_str = is_array($raw_row['parameters']['custom_cover_id']) ? $raw_row['parameters']['custom_cover_id'][ $locale_code] : $raw_row['parameters']['custom_cover_id'];
				if(!empty($_loc_str)){
					$file_row = ResourceHelper::get_file($_loc_str);
					if(!empty($file_row['id'])){
						if(method_exists($CI,'_picture_mapping')){
							$row['cover'] = $CI->_picture_mapping($file_row, 'file','source');
						}else{
							$row['cover']['url'] = picture_url($file_row, 'file','source');
						}
					}
				}
			}

			if(empty($row['description'])){
				$row['description']= '';
			}
			$vals['child'][] = $row;
		}
	}
	return $vals;
}