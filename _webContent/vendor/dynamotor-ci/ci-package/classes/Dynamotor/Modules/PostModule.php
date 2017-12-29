<?php
/** 
 * Post module for CodeIgniter
 * @author      leman 
 * @copyright   Copyright (c) 2015, LMSWork. 
 * @link        http://lmswork.com 
 * @since       Version 1.0 
 *  
 */

namespace Dynamotor\Modules{

class PostModule extends \Dynamotor\Core\HC_Module
{

	var $section = 'page';

	var $_config = NULL;

	var $post_model;
	var $category_model;
	var $tag_model;
	var $text_locale_model;

	var $is_category_enabled = false;
	var $is_tag_enabled      = false;
	var $is_localized   = false;
	var $is_media_enabled    = true;
	var $is_default			 = false;
	var $is_listing_enabled  = true;

	function __construct($section = 'page', $config = NULL) {
		$this->section = $section;
		$this->_config = $config;

		if (isset($this->_config['tag_enabled'])) {
			$this->is_tag_enabled = $this->_config['tag_enabled'] ? true : false;
		}
		if (isset($this->_config['category_enabled'])) {
			$this->is_category_enabled = $this->_config['category_enabled'] ? true : false;
		}
		if (isset($this->_config['localized'])) {
			$this->is_localized = $this->_config['localized'] ? true : false;
		}
		if (isset($this->_config['listing_enabled'])) {
			$this->is_listing_enabled = $this->_config['listing_enabled'] ? true : false;
		}
		if (isset($this->_config['media_enabled'])) {
			$this->is_media_enabled = $this->_config['media_enabled'] ? true : false;
		}
		if ($this->config->item('ph_section_default') == $section) {
			$this->is_default = true;
		}

		$this->load->driver('cache');
		$this->load->helper('cache');

		$post_model_name = 'ph_' . $section . '_post_model';
		$this->load->model('ph_post_model', $post_model_name);
		$this->post_model          = $this->$post_model_name;
		$this->post_model->section = $section;

		//if($this->is_tag_enabled){
		$tag_model_name = 'ph_' . $section . '_tag_model';
		$this->load->model('ph_tag_model', $tag_model_name);
		$this->tag_model          = $this->$tag_model_name;
		$this->tag_model->section = $section;
		//}

		//if($this->is_category_enabled){
		$category_model_name = 'ph_' . $section . '_category_model';
		$this->load->model('ph_category_model', $category_model_name);
		$this->category_model          = $this->$category_model_name;
		$this->category_model->section = $section;
		//}

		if($this->is_localized){
			$this->load->model('text_locale_model');
			$this->text_locale_model = $this->text_locale_model;
		}
	}

	public function config($key){
		if(isset($this->_config[$key])){
			return $this->_config[$key];
		}
		return NULL;
	}

	public function breadcrumb_format($parent_row) {
		$row = array(
			'id'        => $parent_row['id'],
			'icon'      => isset($parent_row['icon']) ? $parent_row['icon'] : '',
			'parent_id' => $parent_row['parent_id'],
			'_mapping'  => $parent_row['_mapping'],
			'id_path'   => $parent_row['id_path'],
			'path'      => isset($parent_row['path']) ? $parent_row['path'] : '',
			//'url'=> site_url('post/category/'.$row['path']),
			'title'       => $parent_row['title'],
			'description' => $parent_row['description'],
			'parameters' => $parent_row['parameters'],
			'def_title'       => $parent_row['title'],
			'def_description' => $parent_row['description'],
			'def_parameters' => $parent_row['parameters'],
			'locale'=>'',
			'loc_title'       => $parent_row['title'],
			'loc_description' => $parent_row['description'],
			'loc_parameters' => $parent_row['parameters'],

		);


		if(!empty($parent_row['locale']))
			$row['locale']   = $parent_row['locale'];
		if(!empty($parent_row['loc_title']))
			$row['loc_title']   = $parent_row['loc_title'];
		if(!empty($parent_row['loc_description']))
			$row['loc_description']   = $parent_row['loc_description'];
		if(!empty($parent_row['loc_parameters']))
			$row['loc_parameters']   = $parent_row['loc_parameters'];

		if(!empty($parent_row['loc_title']))
			$row['title']   = $parent_row['loc_title'];
		if(!empty($parent_row['loc_description']))
			$row['description']   = $parent_row['loc_description'];
		if(!empty($parent_row['loc_parameters']) && is_array($parent_row['loc_parameters']))
			$row['parameters']   = array_replace_recursive($parent_row['parameters'], $parent_row['loc_parameters'] );

		return $row;
	}

	public function get_category($_mapping, $is_live='1', $status='1', $extra_options=false, $cache_time=3600){
		

		if( is_bool($cache_time) ) $cache_time = $cache_time ?  3600 : 0;

		if(! $extra_options)  $extra_options = array();
		$options = array_merge(compact('_mapping','is_live','status'), $extra_options);

		$hash = md5(json_encode($options));
		$cache_key = 'ph/category/'.$_mapping.'/'.$hash;

		$locale = $this->lang->locale();
		if($this->is_localized){
			if(empty($options['_with_locale'])){
				$options['_with_locale'] = $locale;
			}
			$cache_key .= '/'.$locale;
		}

		$row = cache_get($cache_key);

		if(empty($row) || $this->config->item('is_refresh')){
			$row = $this->category_model->read($options);
			if(!empty($row) && $cache_time > 0){

				$cache_key = 'ph/category/'.$row['id'].'/'.$hash;
				if($this->is_localized) $cache_key .= '/'.$locale;
				cache_set($cache_key, $row, $cache_time);

				$cache_key = 'ph/category/'.$row['_mapping'].'/'.$hash;
				if($this->is_localized) $cache_key .= '/'.$locale;
				cache_set($cache_key, $row, $cache_time);
			}
		}
		return $row;
	}

	public function get_category_path($category_id, $is_live = '1', $options = NULL, $cache_time=3600) {

		$ids        = array();
		$rows       = array();
		$paths      = array();
		$breadcrumb = array();
		$titles     = array();

		$options            = array();
		$my_row             = $root_row             = $this->get_category($category_id, $is_live, $cache_time);

		if (empty($my_row['id'])) {
			return $this->_error(404, 'Cannot find matched record.');
		}

		$path_config = $this->path_data($my_row['id_path'],NULL, $cache_time);

		$my_row['path'] = isset($path_config['path']) ? $path_config['path'] . '/' . $my_row['_mapping'] : $my_row['_mapping'];

		$breadcrumb   = $path_config['breadcrumb'];
		$breadcrumb[] = $this->breadcrumb_format($my_row);

		if (isset($breadcrumb[0])) {
			$root_row = $breadcrumb[0];
		}

		$vals                = array();
		$vals['id']          = $my_row['id'];
		$vals['_mapping']    = $my_row['_mapping'];
		$vals['title']       = $my_row['title'];
		$vals['description'] = $my_row['description'];
		$vals['parameters'] = $my_row['parameters'];
		$vals['def_title']       = $my_row['title'];
		$vals['def_description'] = $my_row['description'];
		$vals['def_parameters'] = $my_row['parameters'];
		$vals['loc_title']       = $my_row['title'];
		$vals['loc_description'] = $my_row['description'];
		$vals['loc_parameters'] = $my_row['parameters'];
		$vals['icon']        = isset($my_row['icon']) ? $my_row['icon'] : '';
		$vals['id_path']     = $my_row['id_path'];
		$vals['path']        = $my_row['path'];
		$row['locale']   = '';
		if(!empty($my_row['locale']))
			$vals['locale']   = $my_row['locale'];
		if(!empty($my_row['loc_title']))
			$vals['title']   = $my_row['loc_title'];
		if(!empty($my_row['loc_description']))
			$vals['description']   = $my_row['loc_description'];
		if(!empty($my_row['loc_parameters']))
			$vals['parameters']   = array_replace_recursive($my_row['parameters'], $my_row['loc_parameters']);
		if(!empty($my_row['loc_title']))
			$vals['loc_title']   = $my_row['loc_title'];
		if(!empty($my_row['loc_description']))
			$vals['loc_description']   = $my_row['loc_description'];
		if(!empty($my_row['loc_parameters']))
			$vals['loc_parameters']   = $my_row['loc_parameters'];

		$vals['parents']     = $path_config;

		$vals['root_id']      = $root_row['id'];
		$vals['root_icon']    = $root_row['icon'];
		$vals['root_mapping'] = $root_row['_mapping'];
		$vals['root_title']   = $root_row['title'];
		$vals['root_description']   = $root_row['description'];
		$vals['root_parameters']   = $root_row['parameters'];
		$vals['root_def_title']   = $root_row['title'];
		$vals['root_def_description']   = $root_row['description'];
		$vals['root_def_parameters']   = $root_row['parameters'];
		$vals['root_loc_title']   = $root_row['title'];
		$vals['root_loc_description']   = $root_row['description'];
		$vals['root_loc_parameters']   = $root_row['parameters'];
		if(!empty($root_row['locale']))
			$vals['root_locale']   = $root_row['locale'];
		if(!empty($root_row['loc_title']))
			$vals['root_title']   = $root_row['loc_title'];
		if(!empty($root_row['loc_description']))
			$vals['root_description']   = $root_row['loc_description'];
		if(!empty($root_row['loc_parameters']))
			$vals['root_parameters']   = array_replace_recursive($root_row['parameters'],$root_row['loc_parameters']);
		if(!empty($root_row['loc_title']))
			$vals['root_loc_title']   = $root_row['loc_title'];
		if(!empty($root_row['loc_description']))
			$vals['root_loc_description']   = $root_row['loc_description'];
		if(!empty($root_row['loc_parameters']))
			$vals['root_loc_parameters']   = $root_row['loc_parameters'];

		//$vals['category_child'] = $this->find_child_category_ids($child_row['id'], $is_live);

		$vals['breadcrumb'] = ($breadcrumb);

		return $vals;
	}

	public function path_data($path, $options = NULL, $cache_time=3600) {

		if (substr($path, 0, 1) == '/') {
			$path = substr($path, 1);
		}

		$row                 = array();
		$row['path']         = $path;
		$row['breadcrumb']   = array();
		$row['root_id']      = NULL;
		$row['root_mapping'] = NULL;
		$row['root_title']   = NULL;
		$row['root_description']   = NULL;
		$row['root_parameters']   = NULL;
		$row['root_locale']   = NULL;
		$row['root_def_title']   = NULL;
		$row['root_def_description']   = NULL;
		$row['root_def_parameters']   = NULL;
		$row['root_loc_title']   = NULL;
		$row['root_loc_description']   = NULL;
		$row['root_loc_parameters']   = NULL;
		$row['ids']          = array();
		$row['nodes']        = array();
		$row['titles']       = array();

		$locale = $this->lang->locale();
		if(!empty($options['locale'])){
			$locale = $options['locale'];
		}

		$id_path_nodes = explode('/', $path);
		if (count($id_path_nodes) > 0) {
			$path_nodes = array();

			if (empty($options)) {
				$options = array();
			}
			$opts['_mapping']        = $id_path_nodes;
			$options['_field_based'] = 'id';

			if (!isset($options['is_live'])) {
				$options['is_live'] = '1';
			}

			if (!isset($options['status'])) {
				$options['status'] = '1';
			}

			$cache_key = 'ph/category/path/'.$path;

			if($this->is_localized){
				$cache_key .='/'.$locale;
				$options['_with_locale'] = $locale;
			}

			$is_refresh = $this->config->item('is_refresh') == true ;
			$parents = cache_get($cache_key);

			if($is_refresh || empty($parents)){

				$parents = $this->category_model->find($options);
				if(!empty($parents)){
					cache_set($cache_key, $parents, $cache_time);
				}
			}


			foreach ($id_path_nodes as $idx => $id_path_node) {
				if (!isset($parents[$id_path_node])) {
					log_message('error', 'PostHelper/category@_mappingRow, cannot trace category path for id_path=' . $path);
					break;
				}
				$parent_row = $parents[$id_path_node];
				if ($idx < 1) {
					$row['root_id']      = $id_path_node;
					$row['root_mapping'] = $parent_row['_mapping'];
					$row['root_title']   = $parent_row['title'];
					$row['root_description']   = $parent_row['description'];
					$row['root_parameters']   = $parent_row['parameters'];
					$row['root_def_title']   = $parent_row['title'];
					$row['root_def_description']   = $parent_row['description'];
					$row['root_def_parameters']   = $parent_row['parameters'];
					$row['root_locale'] = '';
					if(!empty($parent_row['locale']))
						$row['root_locale']   = $parent_row['locale'];
					if(!empty($parent_row['loc_title']))
						$row['roo_title']   = $parent_row['loc_title'];
					if(!empty($parent_row['loc_description']))
						$row['root_description']   = $parent_row['loc_description'];
					if(!empty($parent_row['loc_parameters'])  && is_array($parent_row['loc_parameters']) )
						$row['root_parameters']   = array_replace_recursive($row['root_parameters'], $parent_row['loc_parameters']);
					if(!empty($parent_row['loc_title']))
						$row['root_loc_title']   = $parent_row['loc_title'];
					if(!empty($parent_row['loc_description']))
						$row['root_loc_description']   = $parent_row['loc_description'];
					if(!empty($parent_row['loc_parameters']))
						$row['root_loc_parameters']   = $parent_row['loc_parameters'];
				}
				$row['ids'][]        = $parent_row['id'];
				$row['titles'][]     = $parent_row['title'];
				$row['nodes'][]      = $parent_row['_mapping'];
				$path_nodes[]        = $parent_row['_mapping'];
				$parent_row['path']  = implode('/', $path_nodes);
				$row['breadcrumb'][] = $this->breadcrumb_format($parent_row);
			}
			$row['path'] = implode('/', $path_nodes);
		}
		return $row;
	}

	public function tree_data($segments,  $offset = 0, $total = -1, $locale=false) {
		/*
		$seg_str = $segments;
		if(is_array($segments)){
			$seg_str = implode('/',$segments);
		}

		if(substr($seg_str,0,1) == '/'){
			$seg_str = substr($seg_str,1);
		}

		$segments = explode('/',$seg_str);
		//*/
		$parent_row = array(
			'id'       => 0,
			'_mapping' => '',
			'path'     => '',
			'id_path'  => '',
			'icon'     => '',

			'title'       => 'All',
			'description' => '',
			'parameters' => NULL,
			'locale'       => '',
			'def_title'       => 'All',
			'def_description' => '',
			'def_parameters' => NULL,
			'loc_title'       => 'All',
			'loc_description' => '',
			'loc_parameters' => NULL,
			'num_child'   => 0,
		);

		$breadcrumb = array();

		if ($total < 0) {
			$total = count($segments);
		}
		$deep = 0;
		for ($i = $offset; $i <= $total; $i++) {
			$deep++;
			if (!isset($segments[$i])) {
				break;
			}
			$opts = array(
				'parent_id' => $parent_row['id'],
				'_mapping'  => $segments[$i],
				'is_live'   => '1',
				'status'    => '1',
			);
			if(!empty($locale)){
				$opts['_with_locale'] = $locale;
			}
			$row = $this->category_model->read($opts);
			if (empty($row['id'])) {
				return $this->_error(0,  'Parent node does not exist or removed for deep '.$deep.'='.$segments[$i].' for parent='.$parent_row['id'].' (segs='.json_encode($segments).')');
			}
			$row['id_path'] = ($parent_row['id'] == '' ? '' : $parent_row['id_path'] . '/') . $row['id'];
			$row['path']    = ($parent_row['path'] == '' ? '' : $parent_row['path'] . '/') . $row['_mapping'];
			$breadcrumb[]   = $this->breadcrumb_format($row);
			$parent_row     = $row;
		}

		$opts = array(
			'parent_id' => '' . $parent_row['id'],
			'is_live'   => '1',
			'status'    => '1',
			'_order_by' => array('priority'=>'ASC', 'id'=>'ASC'),
		);
		if(!empty($locale)){
			$opts['_with_locale'] = $locale;
		}
		$_child = $this->category_model->find($opts);

		$child = array();
		if (is_array($_child) && count($_child) > 0) {
			foreach ($_child as $idx => $row) {

				$child[] = array(
					'id'        => $row['id'],
					'icon'      => isset($row['icon']) ? $row['icon'] : '',
					'_mapping'  => $row['_mapping'],
					'parent_id' => $row['parent_id'],
					'id_path'   => '/' . ($parent_row['id_path'] == '' ? '' : $parent_row['id_path'] . '/') . $row['id'],
					'path'      => ($parent_row['path'] == '' ? '' : $parent_row['path'] . '/') . $row['_mapping'],
					//'url'=> site_url('post/category/'.($parent_row['path'] == '' ? '' : $parent_row['path'].'/').$row['_mapping']),
					'title'       => isset($row['loc_title']) ? $row['loc_title'] : $row['title'],
					'description' => isset($row['loc_description']) ? $row['loc_description'] : $row['description'],
					'parameters' => isset($row['loc_parameters'])  && is_array($row['loc_parameters']) ? array_replace_recursive($row['parameters'], $row['loc_parameters']) : $row['parameters'],
					'def_title'       => $row['title'],
					'def_description' => $row['description'],
					'def_parameters' => $row['parameters'],
					'locale'       => isset($row['locale']) ? $row['locale'] : '',
					'loc_title'       => isset($row['loc_title']) ? $row['loc_title'] : '',
					'loc_description' => isset($row['loc_description']) ? $row['loc_description'] : '',
					'loc_parameters' => isset($row['loc_parameters']) ? $row['loc_parameters'] : '',
					//'num_child'=> isset($row['num_child']) ? intval(  $row['num_child'] ) : 0,
				);
			}
		}

		$rst = array(
			'id'       => $parent_row['id'],
			'icon'     => isset($parent_row['icon']) ? $parent_row['icon'] :'',
			'_mapping' => $parent_row['_mapping'],
			'path'     => $parent_row['path'],
			'id_path'  => '/' . $parent_row['id_path'],
			//'url'=> site_url('post/category/'.$parent_row['path']),
			'title'       => isset($parent_row['loc_title']) ? $parent_row['loc_title'] : $parent_row['title'],
			'description' => isset($parent_row['loc_description']) ? $parent_row['loc_description'] : $parent_row['description'],
			'parameters' => isset($parent_row['loc_parameters'])  && is_array($parent_row['loc_parameters']) ? array_replace_recursive($parent_row['parameters'],$parent_row['loc_parameters']) : $parent_row['parameters'],
			'def_title'       => $parent_row['title'],
			'def_description' => $parent_row['description'],
			'def_parameters' => $parent_row['parameters'],
			'locale'       => isset($parent_row['locale']) ? $parent_row['locale'] : '',
			'loc_title'       => isset($parent_row['loc_title']) ? $parent_row['loc_title'] : '',
			'loc_description' => isset($parent_row['loc_description']) ? $parent_row['loc_description'] : '',
			'loc_parameters' => isset($parent_row['loc_parameters']) ? $parent_row['loc_parameters'] : '',
			//'num_child'=> isset($parent_row['num_child']) ? intval(  $parent_row['num_child'] ) : 0,
		);
		$rst['breadcrumb'] = $breadcrumb;
		$rst['child']      = $child;
		return $rst;
	}

	public function find_child_category_ids($parent_id = NULL, $is_live = '1', $options = NULL, $level = -1, $current_level=0 , $cache_time=3600) {
		$hash = md5(json_encode(compact('is_live','options')));
		$cache_key = 'ph/category/'.$parent_id.'/'.$hash;
		if($cache_time>0){
			$child_ids = cache_get($cache_key);
			if(!empty($child_ids)) return $child_ids;
		}

		if (!is_array($options)) {
			$options = array();
		}

		if ($parent_id === NULL) {
			$options['parent_id IS'] = NULL;
		} else {
			$options['parent_id'] = $parent_id;
		}

		$options['is_live'] = $is_live;
		if (!isset($options['status'])) {
			$options['status'] = '1';
		}

		$options['_field_based'] = 'id';

		$child_ids = array();
		$child     = $this->category_model->find($options);
		if (!empty($child)) {
			foreach ($child as $cat_id => $cat_row) {

				if (!in_array($cat_id, $child_ids)) {
					$child_ids[] = $cat_id;

					if($level <0 || $current_level < $level){

						$sub_ids = $this->find_child_category_ids($cat_id, $is_live, $options, $level, $current_level+1);
						if (!empty($sub_ids)) {
							foreach ($sub_ids as $idx => $sub_id) {
								if (!in_array($sub_id, $child_ids)) {
									$child_ids[] = $sub_id;
								}
							}
						}
					}
				}
			}
		}


		if($cache_time>0){
			cache_set($cache_key, $child_ids, $cache_time);
		}

		return $child_ids;
	}

	public function find_posts($offset = 0, $limit = 30,$options = NULL) {

		if (!is_array($options)) {
			$options = array();
		}

		if (!isset($options['is_live'])) {
			$options['is_live'] = '1';
		}

		if (!isset($options['status'])) {
			$options['status'] = '1';
		}

		$_tag_post_ids      = NULL;
		$_category_post_ids = NULL;

		if (!empty($options['tag_ids'])) {
			$_tag_post_ids = $this->post_model->get_id_by_tags($options['tag_ids'], $options['is_live']);
			unset($options['tag_ids']);
		}

		if (!empty($options['category_ids'])) {
			$_category_post_ids = $this->post_model->get_id_by_categories($options['category_ids'], $options['is_live']);
			unset($options['category_ids']);
		}

		if (empty($options['_with_locale']) && $this->is_localized) {
			$options['_with_locale'] = $this->lang->locale();
		}
		$ids = NULL;
		if ($_tag_post_ids !== NULL && $_category_post_ids !== NULL) {
			$ids = array();
			foreach ($_tag_post_ids as $idx => $_post_id) {
				if (in_array($_post_id, $_category_post_ids)) {
					if (!in_array($_post_id, $ids)) {
						$ids[] = $_post_id;
					}
				}
			}
		} elseif ($_tag_post_ids !== NULL) {
			$ids = array();
			foreach ($_tag_post_ids as $idx => $_post_id) {
				if (!in_array($_post_id, $ids)) {
					$ids[] = $_post_id;
				}
			}
		} elseif ($_category_post_ids !== NULL) {
			$ids = array();

			foreach ($_category_post_ids as $idx => $_post_id) {
				if (!in_array($_post_id, $ids)) {
					$ids[] = $_post_id;
				}
			}
		}

		if (is_array($ids)) {
			if (empty($ids)) {
				return array('data' => array());
			}
			$options['id'] = $ids;
		}

		$result = $this->post_model->find_paged($offset, $limit, $options);
		if(isset($result['data']) && is_array($result['data'])){
			foreach($result['data'] as $idx => $row){
				$result['data'][$idx] = $this->post_mapping_row($row);
			}
		}
		return $result;
	}

	public function read_post($options = NULL){
		if(!is_array($options)){
			$options = array('_mapping'=>$options);
		}
		if(!isset($options['is_live'])) $options['is_live'] = '1';
		if(!isset($options['status'])) $options['status'] = '1';

		if($this->is_localized && empty($options['_with_locale'])){
			$options['_with_locale'] = $this->lang->locale();
		}

		$result = $this->find_posts(0, 1, $options);
		if(isset($result['data'][0]['id'])){
			return $result['data'][0];
		}
		return NULL;
	}

	public function post_mapping_row($row, $options= false) {
		//log_message('debug','Ph['.$this->section.']/post_mapping_row['.$row['id'].']#begin, row='.print_r(compact('options'),true));

		//log_message('debug','Ph['.$this->section.']/post_mapping_row, row='.print_r($row,true));

		$this->load->model(array('file_model','album_model','album_photo_model'));

		$is_refresh = $this->config->item('is_refresh') ;

		//die('is_refresh?'. ($is_refresh ? 'Y':'N'));

		$locale = $this->lang->locale();
		if(!empty($row['locale']))
			$locale = $row['locale'];

		if($this->is_tag_enabled ){

			if(!isset($options['tags_enabled']) || $options['tags_enabled']!==FALSE){
				//log_message('debug','Ph['.$this->section.']/post_mapping_row['.$row['id'].']/findrelated/tags');

				$tag_ids = $this->post_model->get_tags($row['id'],$row['is_live']);
				$row['tags'] = array();
				if(!empty($tag_ids)){
					$tag_options = array('id'=> $tag_ids, 'is_live'=> $row['is_live'], 'status'=> $row['status'] ,'_order_by'=>array('publish_date'=>'desc','create_date'=>'desc'));
/*
					if($this->is_localized){
						$tag_options['_with_locale'] = $locale;
					}
//*/
					$row['tags'] = $this->tag_model->find($tag_options);
				}
			}
		}


		if($this->is_category_enabled ){

			if(!isset($options['category_enabled']) || $options['category_enabled']!==FALSE){
				//log_message('debug','Ph['.$this->section.']/post_mapping_row['.$row['id'].']/findrelated/categories');

				$extra_options = array();
				if($this->is_localized){
					$extra_options['_with_locale'] = $locale;
				}

				$row['category'] = $this->get_category($row['category_id'], $row['is_live'], $row['status'], $extra_options);
				$row['categories_mapping'] = array();
				$row['categories'] = array();

				if(!empty($row['category'])){
					$row['categories_mapping'] [] = $row['category']['_mapping'];
					$row['categories'] [] = $row['category'];
				}
				
				if(!isset($options['categories_enabled']) || $options['categories_enabled']){
					//$row['categories'] [] = null;

					$category_ids = $this->post_model->get_categories($row['id'],$row['is_live']);

					foreach($category_ids as $category_id){

						$category_row = $this->get_category($category_id, $row['is_live'], $row['status'], $extra_options);

						if(isset($category_row[ 'id'])){
							if(!in_array( $category_row['_mapping'], $row['categories_mapping'] )){
								$row['categories_mapping'][] = $category_row['_mapping'];
								$row['categories'][] = $category_row;
							}
						}
					}
				}
			}
		}

		if(!isset($options['cover_enabled']) || $options['cover_enabled']!==FALSE){
			//log_message('debug','Ph['.$this->section.']/post_mapping_row['.$row['id'].']/findrelated/cover');
			if(!empty($row['loc_cover_id'])){
				$row['cover_row'] = $this->resource->get_file($row['loc_cover_id'], !$is_refresh);
			}elseif(!empty($row['cover_id'])){
				$row['cover_row'] = $this->resource->get_file($row['cover_id'], !$is_refresh);
			}
		}

		if(!empty($row['album_id'])){
			if(!isset($options['album_enabled']) || $options['album_enabled']!==FALSE){
				//log_message('debug','Ph['.$this->section.']/post_mapping_row['.$row['id'].']/findrelated/album');

				$album = $this->resource->get_album(array('id'=>$row['album_id'],'is_live'=>$row['is_live'] ), !$is_refresh);
				$row['album_row'] = $album;
				//$row['album_photo_files'] = $photo_files;
				//$row['album_files'] = $files;
			}
		}
		if(!isset($row['path'])){
			$row['path'] = $this->section.'/'.$row['_mapping'];

			if($this->config->item('ph_section_default') == $this->section){
				$row['path'] = $row['_mapping'];
			}
		}else{
			$row['path'] = $row['path'];
		}

		$row['url'] = web_url($row['path']);
		$row['loc_url'] = web_url($this->lang->locale().'/'.$row['path']);

		//log_message('debug','Ph['.$this->section.']/post_mapping_row['.$row['id'].']#end, row='.print_r(compact('row'),true));
		return $row;
	}

	// for live
	function read_post_cache($_mapping, $locale = 'en', $cache = true){

		$cache_key = 'ph/post/'.$_mapping.'/'.$locale.'/live';
		$r = cache_get($cache_key,$cache);

		if(!$cache || empty($r)){
			$query = array('_mapping'=>$_mapping,'is_live'=>'1','status'=>'1','_with_locale'=>$locale);
			$r = $this->post_model->read($query);
			if(empty($r['id'])){
				return NULL;
			}

			cache_set($cache_key, $r,$cache);
		}
		return $this->post_mapping_row($r);
	}

	function reset_post_cache($r){
		if(!empty($r['id'])){
			$cache_key = 'ph/post/'.$r['id'].'/*';
			cache_remove($cache_key);
		}
		if(!empty($r['_mapping'])){
			$cache_key = 'ph/post/'.$r['_mapping'].'/*';
			cache_remove($cache_key);
		}
		if(!empty($r['slug'])){
			$cache_key = 'ph/post/'.$r['slug'].'/*';
			cache_remove($cache_key);
		}
	}

}
}