<?php

use Dynamotor\Helpers\PostHelper;

class Category extends MY_Controller {
	var $section    = 'page';
	var $seg_offset = 2;
	var $ph         = NULL;

	function __construct() {
		parent::__construct();

		$this->load->config('ph');

		$this->seg_offset = 3;
		$this->section    = $this->uri->segment($this->seg_offset - 1);
		if (preg_match('/^[a-zA-Z]+$/', $this->section)) {
			$this->ph = PostHelper::get_section($this->section);
		} else {
			$this->seg_offset = 4;
			$this->section    = $this->uri->segment($this->seg_offset - 1);
			if (preg_match('/^[a-zA-Z]+$/', $this->section)) {
				$this->ph = PostHelper::get_section($this->section);
			}
		}

		$this->load->model('text_locale_model');

	}

	function _render($view, $vals=false, $layout = false, $theme = false) {

		if(!empty($this->ph)){
			if(!empty($this->ph->config('admin_menu_tree'))){
				$ary = $this->ph->config('admin_menu_tree');
				$ary[] = 'category';
				$this->config->set_item('main_menu_selected',$ary);
			}else{
				
				$this->config->set_item('main_menu_selected', array($this->ph->section,'category'));
			}

			$vals['section_path_prefix'] = ('s/'.$this->ph->section);
			$vals['section_url_prefix'] = site_url($vals['section_path_prefix']);
			$vals['endpoint_path_prefix'] = ('s/'.$this->ph->section.'/category');
			$vals['endpoint_url_prefix'] = site_url($vals['endpoint_path_prefix']);
			$vals['view_path_prefix'] = ('ph/category');
		}

		$vals['section'] = $this->section;
		$vals['ph']      = $this->ph;
		return parent::_render($view, $vals, $layout, $theme);
	}

	function _remap() {

		if (empty($this->ph)) {
			return $this->_error(ERROR_INVALID_DATA, 'Instance of PostHelper "' . $this->section . '" does not exist.');
		}

		$seg_offset = $this->seg_offset;

		$s1 = $this->uri->segment($this->seg_offset + 1);
		$s2 = $this->uri->segment($this->seg_offset + 2);

		if (in_array($s1, array('', 'index', 'selector', 'browse'))) {
			return $this->_list();
		}
		if (in_array($s1, array('all'))) {
			return $this->_all();
		}
		if (in_array($s1, array('search'))) {
			return $this->_search();
		}
		if (in_array($s1, array('reposition'))) {
			return $this->_reposition();
		}
		if (in_array($s1, array('batch'))) {
			return $this->_batch($s2);
		}
		if (in_array($s1, array('save'))) {
			return $this->_save();
		}
		if (in_array($s1, array('remove'))) {
			return $this->_remove();
		}
		if (in_array($s1, array('add'))) {
			return $this->_editor();
		}
		if (in_array($s1, array('publish'))) {
			return $this->_publish($s2);
		}
		if (preg_match("/^[a-zA-Z\-0-9]+$/", $s1)) {
			if ($s2 == 'publish') {
				return $this->_publish($s1);
			}

			if ($s2 == 'edit') {
				return $this->_editor($s1);
			}

			if (empty($s2)) {
				return $this->_view($s1);
			}
		}

		return $this->_show_404();
	}

	function _mapping_row($raw_row,$options = NULL) {

		$parent_data = $this->ph->path_data($raw_row['id_path'], array('is_live' => '0'));

		$row       = array();
		$row['id'] = $raw_row['id'];

		$row['owner_type'] = $raw_row['owner_type'];
		$row['owner_id']   = $raw_row['owner_id'];

		$row['slug']               = $raw_row['section'];
		$row['parent_id']          = $raw_row['parent_id'];
		$row['_mapping']           = $raw_row['_mapping'];
		$row['id_path']            = $raw_row['id_path'];
		$row['path']               = !empty($parent_data['path']) ? $parent_data['path'] . '/' . $raw_row['_mapping'] : $raw_row['_mapping'];
		$row['path_nodes']         = $parent_data['nodes'];
		$row['path_titles']        = $parent_data['titles'];
		$row['top_parent_id']      = $parent_data['root_id'];
		$row['top_parent_title']   = $parent_data['root_title'];
		$row['top_parent_mapping'] = $parent_data['root_mapping'];
		$row['icon']               = isset($raw_row['icon']) ? $raw_row['icon'] : '';
		$row['title']              = $raw_row['title'];
		$row['description']        = $raw_row['description'];
		if($this->ph->is_localized){
			$row['loc_title'] = $raw_row['loc_title'];
			if(isset($raw_row['loc_description']))
				$row['loc_description'] = $raw_row['loc_description'];
			if(isset($raw_row['loc_content']))
				$row['loc_content'] = $raw_row['loc_content'];
		}
		$child_ids = $this->ph->find_child_category_ids($raw_row['id'],'0');
		$row['child_ids'] = $child_ids;
		$child_ids[] = $raw_row['id'];
		$row['num_posts'] = $this->ph->post_model->get_total(array('category_id'=> $child_ids ,'is_live'=>'0'));
		$row['slug']               = $raw_row['slug'];
		$row['status']             = $raw_row['status'];
		$row['status_str']         = lang('status_' . $raw_row['status']);
		$row['is_pushed']          = $raw_row['is_pushed'];
		$row['is_pushed_str']      = lang('is_pushed_' . $raw_row['is_pushed']);
		$row['priority']           = $raw_row['priority'];
		$row['published']          = $raw_row['publish_date'];
		$row['created']            = $raw_row['create_date'];
		$row['modified']           = !$raw_row['modify_date'] || substr($raw_row['modify_date'], 0, 4) == '0000' ? $raw_row['create_date'] : $raw_row['modify_date'];

		return $row;
	}

	function _list() {

		if ($this->_restrict()) {
			return;
		}
		$vals = array();
		$section_prefix = $this->ph->is_default ? '/':$this->ph->section.'/';
		$vals['preview_url'] = base_url('preview/'.$section_prefix.'/category');
		$vals['live_url'] = web_url($section_prefix.'/category');
		$this->_render('ph/category_index', $vals);
	}

	function _view($record_id = false) {

		if ($this->_restrict()) {
			return;
		}

		$vals = array();

		if (!empty($record_id)) {
			$vals = $this->ph->category_model->read(array('section' => $this->ph->section, 'id' => $record_id, 'is_live' => '0'));
			if (isset($vals['id']) && $this->_is_ext('data')) {
				return $this->_api($this->_mapping_row($vals));
			}
		}

		return $this->_show_404();
	}

	function _editor($record_id = false) {

		if ($this->_restrict()) {
			return;
		}

		$record = NULL;
		$vals = array();

		$vals['data'] = $this->ph->category_model->new_default_values();
		$vals['data']['default_locale'] = $this->lang->locale();
		$vals['record'] = NULL;
		$vals['id'] = $record_id;
		if (!empty($record_id)) {
			$record = $this->ph->category_model->read(array('section' => $this->ph->section, 'id' => $record_id, 'is_live' => '0'));
			$vals['record'] = $this->_mapping_row($record);
			$vals['data'] = $record;
		}
		if($this->ph->is_localized){
			$vals['loc'] = array();

			if(!empty($record)){
				$this->load->model('text_locale_model');
				$vals['loc'] = $this->text_locale_model->find(array('ref_table'=>$this->ph->category_model->table,'ref_id'=>$record_id,'is_live'=>'0','_field_based'=>'locale'));
			}
		}

		$vals['categories'] = $this->ph->category_model->find(array('section' => $this->ph->section, 'is_live' => '1'));

		$vals['parameter_fields'] = $this->ph->config('category_parameters');
		$vals['parameter_view'] = $this->ph->config('category_parameter_admin_view');

		if ($this->uri->is_extension('js')) {
			return $this->_render('ph/category_editor.js', $vals);
		}

		$this->_render('ph/category_editor', $vals);
	}

	function _all() {
		if ($this->_restrict()) {
			return;
		}

		$vals = array();

		$product_categories = $this->ph->category_model->find(array('section' => $this->ph->section, 'is_live' => '0', '_order_by' => array('parent_id'=>'asc', 'priority'=>'asc')));
		$vals['data']       = $product_categories;

		return $this->_api($vals);
	}

	function _search() {
		if ($this->_restrict(null, false)) {
			return $this->_error(ERROR_INVALID_SESSION, 'Valid session required.');
		}

		if (!$this->_is_ext('data')) {
			return $this->_show_404();
		}
		$vals = array();

		$product_categories = array();
		$have_query         = false;
		$success            = true;

		$options = array(
			'_keyword_fields' => array('title'),
			'_order_by' => array('priority'=>'asc'),
		);

		$options['section'] = $this->ph->section;
		$options['is_live'] = 0;
		if($this->ph->is_localized)
			$options['_with_locale'] = $this->lang->locale();

		$last_path = NULL;

		$parent_path     = $this->input->get_post('path');
		$has_parent_path = !empty($parent_path);

		if ($has_parent_path) {
			if (substr($parent_path, 0, 1) != '/') {
				$parent_path = '/' . $parent_path;
			}
			if (empty($parent_path)) {$parent_path = '';
			}

			$parent_paths = explode("/", $parent_path);

			$breadcrumb         = array();
			$last_path_category = NULL;
			$category_ids            = array();
			$paths              = array();

			if (is_array($parent_paths) && count($parent_paths) > 0) {
				foreach ($parent_paths as $idx => $path) {
					if ($idx < 1) {continue;
					}

					if (empty($path)) {continue;
					}

					$opts = array('section' => $this->ph->section, 'is_live' => 0, '_mapping' => $path);

					if (isset($last_path_category['id'])) {
						$opts['parent_id'] = $last_path_category['id'];
					} else {
						$opts['parent_id'] = '0';
					}

					$row = $this->ph->category_model->read($opts);

					if (isset($row['id'])) {

						$paths[]            = $row['_mapping'];
						$category_ids[]          = $row['id'];
						$last_path_category = $row;

						$new_row         = $this->_mapping_row($row);
						$new_row['path'] = '/' . implode('/', $paths);
						$breadcrumb[]    = $new_row;
					} else {
						return $this->_error(ERROR_INVALID_DATA, 'Specified path is not exist when looking for "' . $path . '".');
					}
				}
			}

			$category_id_path = '/' . implode('/', $category_ids);

			if ($has_parent_path) {
				$options['parent_id'] = '0';
				if (isset($last_path_category['id'])) {
					$options['parent_id'] = $last_path_category['id'];
				}
			}
		}

		if ($this->input->get('term') != NULL) {
			if ($this->input->get('term') == '') {
			} else {
				$have_query          = true;
				$options['_keyword'] = $this->input->get('term');
			}
		}

		if ($this->input->get('q') != NULL) {
			if ($this->input->get('q') == '') {
			} else {
				$have_query          = true;
				$options['_keyword'] = $this->input->get('q');
			}
		}

		if ($this->input->get('id') != NULL) {
			if ($this->input->get('ids') == '') {
			} else {
				$have_query    = true;
				$options['id'] = $this->input->get('id');
			}
		}
		if ($this->input->get('ids') != NULL) {
			if ($this->input->get('ids') == '') {
			} else {
				$have_query    = true;
				$options['id'] = explode(",", trim($this->input->get('ids')));
			}
		}

		if ($this->input->get('autocomplete') == 'yes') {
			$categories = array();
			$data       = array();
			if ($have_query) {
				$categories = $this->ph->category_model->find($options);
			}
			if (is_array($categories) && count($categories) > 0) {
				foreach ($categories as $idx => $row) {
					$new_row = $this->_mapping_row($row);
					$data[]  = array(
						'label' => count($new_row['path_titles']) > 0 ? implode(' / ', $new_row['path_titles']) . ' / ' . $row['title'] : $row['title'],
						'value' => $row['id'],
					);
				}
			}
			return $this->_api($data);
		}

		$direction = 'asc';
		$sort      = 'priority';
		$start     = 0;
		$limit     = 20;

		if ($this->input->get('direction') !== false) {
			$direction = $this->input->get('direction');
		}
		if ($this->input->get('sort') !== false) {
			$sort = $this->input->get('sort');
		}

		if ($this->input->get('offset') !== false) {
			$offset = $this->input->get('offset');
		}
		if ($this->input->get('limit') !== false) {
			$limit = $this->input->get('limit');
		}

		if ($this->input->get('page') !== FALSE) {
			$start = ($this->input->get('page') - 1) * $limit;
		}

		if ($this->input->get('q') != false && $this->input->get('q') != '') {
			$options['_keyword']        = $this->input->get('q');
			$options['_keyword_fields'] = array('id', 'slug', 'title', 'description', 'content');
		}

		if ($start < 0) {$start = 0;
		}

		if ($limit < 5) {$limit            = 10;
		} elseif ($limit % 5 != 0) {$limit = 10;
		}

		if ($has_parent_path) {$limit = 1000000;
		}

		if (!in_array($sort, array('id', 'slug', 'title', 'priority', 'create_date', 'modify_date'))) {$sort = 'priority';
		}

		if (strtolower($direction) != 'desc') {$direction = 'asc';
		}

		$options['_order_by'] = array($sort=>$direction,'create_date'=>$direction);
		//$options['_with_text'] = $this->lang->locale();

		$result                  = $this->ph->category_model->find_paged($start, $limit, $options, false);
		$vals['has_parent_path'] = $has_parent_path;

		$vals['paging']['offset']     = 0;
		$vals['paging']['total']      = 0;
		$vals['paging']['limit']      = $limit;
		$vals['paging']['page']       = 0;
		$vals['paging']['total_page'] = 0;
		$vals['data']                 = array();

		if ($has_parent_path) {
			$vals['breadcrumb'] = $breadcrumb;
			$vals['paths']      = $paths;
			$vals['id_path']    = $category_id_path;
			$vals['parent_id']  = isset($last_path_category['id']) ? $last_path_category['id'] : NULL;
		}
		if (!$has_parent_path) {

		}

		if (isset($result['data'])) {
			$vals['paging']['offset']     = $result['index_from'];
			$vals['paging']['total']      = $result['total_record'];
			$vals['paging']['limit']      = $result['limit'];
			$vals['paging']['page']       = $result['page'];
			$vals['paging']['total_page'] = $result['total_page'];
			$frontend_url = $this->ph->is_default ? '/' : $this->ph->section.'/';

			foreach ($result['data'] as $idx => $row) {
				$new_row = $this->_mapping_row($row);

				$new_row['_index']       = $result['index_from']+$idx;
				$new_row['_edit_url']    = site_url('s/' . $this->ph->section . '/category/' . $row['id'] . '/edit');
				$new_row['_preview_url'] = base_url('preview/' . $frontend_url . 'category/' . $new_row['path'] . '');
				$new_row['_live_url']    = web_url('../'.$frontend_url . 'category/' . $new_row['path'] . '');
				$vals['data'][]          = $new_row;
			}
			/*
		$vals['paging']['offset'] = $result['index_from'];
		$vals['paging']['total'] = $result['total_record'];
		$vals['paging']['limit'] = $result['limit'];
		$vals['paging']['page'] = $result['page'];
		$vals['paging']['total_page'] = $result['total_page'];
		//*/
		}

		return $this->_api($vals);
	}

	function _publish($id = false, $return = false) {
		if ($this->_restrict(null, false)) {
			$this->_error(ERROR_INVALID_SESSION, 'Valid session required.');
			return;
		}

		if (empty($id)) {
			$this->_error(ERROR_INVALID_DATA, 'Passed invalid value.');
			return;
		}

		$old_row = $this->ph->category_model->read(array('id' => $id, 'is_live' => 1));
		if (isset($old_row['id'])) {
			cache_remove('ph/category/' . $old_row['id']);
			cache_remove('ph/category/' . $old_row['id'] . '/*');

			cache_remove('ph/category/' . $old_row['_mapping']);
			cache_remove('ph/category/' . $old_row['_mapping'] . '/*');

			$this->ph->category_model->delete(array('id' => $id, 'is_live' => 1));
		}
		$new_row = $this->ph->category_model->read(array('id' => $id, 'is_live' => 0));
		//log_message('debug', 'Ph/category/publish, new row:' . print_r($new_row, true));
		$new_row['is_live'] = 1;
		$result             = $this->ph->category_model->save($new_row);

		$new_state = array('is_pushed' => 1, 'last_pushed' => time_to_date());
		$this->ph->category_model->save($new_state, array('id' => $new_row['id']));


		if($this->ph->is_localized){
			$all_curr_loc_rows = $this->text_locale_model->find(array('is_live'=>'0','ref_table'=>$this->ph->category_model->table, 'ref_id'=>$id,'_field_based'=>'locale'));

			$this->text_locale_model->delete(array('is_live'=>'1','ref_table'=>$this->ph->category_model->table, 'ref_id'=>$id));
			foreach($all_curr_loc_rows as $loc_code => $loc_data){
				$loc_data['is_live'] = '1';
				$this->text_locale_model->save($loc_data);
			}
		}

		$output = array('id' => $id, 'last_pushed' => $new_state['last_pushed']);

		if (isset($result['id'])) {
			if ($result['id'] == $id) {
				if ($return) {
					return $output;
				}

				return $this->_api($output);
			}
			return $this->_handle_error($return, ERROR_INVALID_DATA, 'Invalid id after save live content.');
		}
		return $this->_handle_error($return, ERROR_RECORD_SAVE_ERROR, 'Cannot save record in database.');
	}

	function _batch($action = '') {
		if ($this->_restrict(null, false)) {
			$this->_error(ERROR_INVALID_SESSION, 'Valid session required.');
			return;
		}

		$ids = $this->input->post('ids');
		$ids = explode(",", trim($ids));
		if (!is_array($ids)) {
			$this->_error(ERROR_INVALID_DATA, 'Passed invalid value.');
			return;
		}

		$records = $this->ph->category_model->find(array('id' => $ids, 'is_live' => '0'));

		$data = array();
		if (is_array($records) && count($records) > 0) {
			foreach ($records as $idx => $row) {

				if ($action == 'enable') {
					$this->ph->category_model->save(array('status' => 1), array('id' => $row['id'], 'is_live' => '0'));
					$data[$row['id']] = TRUE;
				} elseif ($action == 'disable') {
					$this->ph->category_model->save(array('status' => 0), array('id' => $row['id'], 'is_live' => '0'));
					$data[$row['id']] = TRUE;
				} elseif ($action == 'publish') {
					$data[$row['id']] = $this->_publish($row['id'], true);
				}

			}

			return $this->_api(array('data' => $data));
		} else {
			return $this->_error(ERROR_NO_RECORD_LOADED, 'No record has been loaded.');
		}
	}

	function _save($id = false) {

		$vals = array();
		if ($this->_restrict()) {return;
		}

		$vals    = array();
		$success = true;

		if (!$id) {
			$id = $this->input->get_post('id');
		}

		$record = $id ? $this->ph->category_model->read(array('id' => $id, 'is_live' => '0')) : NULL;
		if ($id && (!isset($record['id']) || $record['id'] != $id)) {
			show_404();
			return;
		}


		$def_vals = $this->ph->category_model->new_default_values();
		foreach ($def_vals as $key => $defVal) {
			$data[$key] = $defVal;
			if (isset($record[$key])) {
				$data[$key] = $record[$key];
			}
			if ($this->input->post($key) !== false) {
				$data[$key] = $this->input->post($key);
			}
		}

		$last_path = NULL;

		$parent_path = $this->input->get_post('path');
		if (!empty($parent_path)) {


			if(!empty($data['parent_id'])){
				$parent = $this->ph->category_model->read(array('id'=>$data['parent_id'],'is_live'=>'1'));
				if(!empty( $parent['id_path']))
					$parent_path = $parent['id_path'].'/'.$parent['id'];
			}

			if (substr($parent_path, 0, 1) != '/') {
				$parent_path = '/' . $parent_path;
			}
			if (empty($parent_path)) {
				$parent_path = '';
			}

			$parent_paths = explode("/", $parent_path);

			$breadcrumb         = array();
			$last_path_category = NULL;
			$category_ids            = array();

			if (is_array($parent_paths) && count($parent_paths) > 0) {
				foreach ($parent_paths as $idx => $path) {
					if ($idx < 1) {
						continue;
					}

					if (empty($path)) {
						continue;
					}

					$opts = array('is_live' => 0, '_mapping' => $path);
					if (isset($last_path_category['id'])) {
						$opts['parent_id'] = $last_path_category['id'];
					} else {
						$opts['parent_id IS'] = NULL;
					}

					$row = $this->ph->category_model->read($opts);


					if (isset($row['id'])) {
						$category_ids[]          = $row['id'];
						$last_path_category = $row;
						$breadcrumb[]       = $this->_mapping_row($row);
					} else {
						return $this->_error(ERROR_INVALID_DATA, 'Specified path is not exist when looking for "' . $path . '".');
					}
				}
			}

			if (isset($last_path_category['id'])) {
				$data['parent_id'] = $last_path_category['id'];
			}
		}elseif(!empty($data['parent_id'])){
			$parent = $this->ph->category_model->read(array('id'=>$data['parent_id'],'is_live'=>'0'));
			if(!empty($parent['id'])){
				$data['id_path'] = !empty($parent['id_path']) ? $parent['id_path'].'/'.$parent['id'] : $parent['id'];
			}else{
				$data['id_path'] = '';
			}
		}

		$success = true;

		if ($success) {

			if (empty($data['slug'])) {
				$data['slug'] = ($data['title']);
			}
			$data['slug'] = seo_string($data['slug']);

			$edit_info =$this->_get_editor_info();


			$all_parameters_info = NULL;
			if(isset($this->ph->config('category_parameters')) && is_array($this->ph->config('category_parameters'))){
				$all_parameters_info = $this->ph->config('category_parameters');
			}

			if(!empty($all_parameters_info)){
				$posted_parameters = $this->input->post('parameters');
				$new_parameters = array();
				foreach($all_parameters_info as $p_field => $p_info){

					if(!empty($p_info['localized']) && $p_info['localized']){
						continue;
					}

					if(isset($posted_parameters[ $p_field])){
						$new_parameters[$p_field] = $posted_parameters[ $p_field];
					}else{
						$new_parameters[$p_field] = isset($p_info['default_value']) ? $p_info['default_value'] : NULL;
					}

				}
				$data['parameters'] = $new_parameters;
			}

			// locale data
			$all_loc_data = $this->input->post('loc');
			$default_locale = $this->input->post('default_locale');
			$locale = $this->lang->locale();
			$locale_fields = array('title','description','content','parameters','status','cover_id');
			if($this->ph->is_localized){
				if(empty($default_locale)) $data['default_locale'] = $default_locale = $this->lang->locale();

				$loc_data = isset($all_loc_data[$default_locale]) ? $all_loc_data[$default_locale] : NULL;

				$sql_loc_data = array();
				foreach($locale_fields as $idx => $field_name){
					if(isset($loc_data[$field_name]))
						$data [$field_name] = $loc_data[$field_name];
				}

				if(empty($all_loc_data)){
					foreach($this->lang->get_available_locale_keys() as $loc_code){
						$loc_data = array();
						foreach($locale_fields as $idx => $field_name){
							if(isset( $data[$field_name]))
							$loc_data[$field_name] = $data[$field_name];
						}
						$all_loc_data[$loc_code] = $loc_data;
					}
				}
			}

			if (!$id) {
				$data['owner_type'] = $edit_info['type'];
				$data['owner_id'] = $edit_info['id'];
				$data['is_live']  = '0';
				$data['section'] = $this->ph->section;
				$result           = $this->ph->category_model->save($data, NULL, $edit_info);
				$result['action'] = 'add';
			} else {
				unset($data['id']);
				if ($record['is_pushed'] > 0) {
					$data['is_pushed'] = '2';
				}

				$result           = $this->ph->category_model->save($data, array('id' => $id, 'is_live' => '0'), $edit_info);
				$result['action'] = 'edit';
			}
			//$messages['sqls'][]= $this->db->last_query();

			
			if($this->ph->is_localized){
				// required model
				$this->load->model('text_locale_model');
				// data
				$all_loc_data = $this->input->post('loc');
				$all_curr_loc_rows = $this->text_locale_model->find(array('ref_table'=>$this->ph->category_model->table,'ref_id'=>$result['id'], 'is_live'=>0,'_field_based'=>'locale'));



				foreach($this->lang->get_available_locale_keys() as $loc_code){
					$loc_data = isset($all_loc_data[$loc_code]) ? $all_loc_data[$loc_code] : NULL;

					// skip it if no data for this locale
					//if(empty($loc_data)) continue;
					$curr_loc_row = isset($all_curr_loc_rows[$loc_code]) ? $all_curr_loc_rows[$loc_code] : NULL;

					$sql_loc_data = array();
					foreach($locale_fields as $idx => $field_name){
						if(isset($loc_data[$field_name]))
							$sql_loc_data [$field_name] = $loc_data[$field_name];
					}
					if(!empty($all_parameters_info)){
						$posted_parameters = isset($loc_data['parameters']) ? $loc_data['parameters'] : NULL;
						$new_parameters = array();
						foreach($all_parameters_info as $p_field => $p_info){
							if(empty($p_info['localized']) || !$p_info['localized']){
								continue;
							}

							if(isset($posted_parameters[ $p_field])){
								$new_parameters[$p_field] = $posted_parameters[ $p_field];
							}else{
								$new_parameters[$p_field] = isset($p_info['default_value']) ? $p_info['default_value'] : NULL;
							}

						}
						$sql_loc_data['parameters'] = $new_parameters;
					}else{
						unset($sql_loc_data['parameters']);
					}

					if(empty($curr_loc_row['id'])){
						$sql_loc_data['ref_table'] = $this->ph->category_model->table;
						$sql_loc_data['ref_id'] = $result['id'];
						$sql_loc_data['is_live'] = '0';
						$sql_loc_data['locale'] = $loc_code;
						$this->text_locale_model->save($sql_loc_data);

					}else{
						$this->text_locale_model->save($sql_loc_data, array('id'=>$curr_loc_row['id'],'is_live'=>'0','locale'=>$loc_code));
					}
				}
			}

			cache_remove('ph/category_list');
			cache_remove('ph/category/' . $result['id']);
			cache_remove('ph/category/' . $result['id'] . '/*');

			$vals['queries'] = $this->db->queries;
			$vals['id']     = $result['id'];
			$vals['method'] = $result['action'];
		}

		if ($this->uri->is_extension('')) {
			redirect('s/'.$section.'/category/' . $id);
			return;
		}
		return $this->_api($vals);
	}

	function _remove() {

		if ($this->_restrict(null, false)) {
			return $this->_error(ERROR_INVALID_SESSION, 'Valid session required.');
		}

		$ids = $this->input->post('ids');
		$ids = explode(",", trim($ids));
		if (!is_array($ids)) {
			return $this->_error(ERROR_INVALID_DATA, 'Passed invalid value.');
		}

		$records = $this->ph->category_model->find(array('id' => $ids));
		if (is_array($records) && count($records) > 0) {
			foreach ($records as $idx => $row) {

				cache_remove('ph/category/' . $row['id']);
				cache_remove('ph/category/' . $row['id'] . '/*');

				cache_remove('ph/category/' . $row['_mapping']);
				cache_remove('ph/category/' . $row['_mapping'] . '/*');
			}


			$this->text_locale_model->delete(array('ref_table'=>$this->ph->category_model->table, 'ref_id'=>$ids));

			$this->ph->post_model->remove_categories($ids);

			$this->ph->category_model->delete(array('id' => $ids));

			return $this->_api(array('data' => $ids));
		} else {
			return $this->_error(ERROR_NO_RECORD_LOADED, 'No record has been loaded.');
		}

	}
}
