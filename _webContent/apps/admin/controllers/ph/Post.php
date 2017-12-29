<?php

use Dynamotor\Helpers\PostHelper;

class Post extends MY_Controller {
	var $section    = 'page';
	var $seg_offset = 2;
	var $ph         = NULL;

	function __construct() {
		parent::__construct();

		$this->load->config('ph');

		$this->load->model('file_model');
		$this->load->model('album_model');
		$this->load->model('album_photo_model');
		$this->load->model('relationship_model');

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
				$ary[] = 'post';
				$this->config->set_item('main_menu_selected',$ary);
			}else{
				
				$this->config->set_item('main_menu_selected', array($this->ph->section,'post'));
			}

			$vals['section_path_prefix'] = ('s/'.$this->ph->section);
			$vals['section_url_prefix'] = site_url($vals['section_path_prefix']);
			$vals['endpoint_path_prefix'] = ('s/'.$this->ph->section.'/post');
			$vals['endpoint_url_prefix'] = site_url($vals['endpoint_path_prefix']);
			$vals['view_path_prefix'] = ('ph/post_');
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

		$s1 = $this->uri->segment($seg_offset + 1);
		$s2 = $this->uri->segment($seg_offset + 2);

		if (in_array($s1, array('', 'index', 'selector'))) {
			return $this->_list();
		}
		if (in_array($s1, array('search'))) {
			return $this->_search();
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
		if (in_array($s1, array('priority'))) {
			return $this->_priority();
		} 
		if (in_array($s1, array('submit'))) {
			return $this->_submit();
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

	function _mapping_row($raw_row) {
		$row             = array();
		$row['id']       = $raw_row['id'];
		$row['slug']     = $raw_row['section'];
		$row['_mapping'] = $raw_row['_mapping'];

		$row['owner_type'] = $raw_row['owner_type'];
		$row['owner_id']   = $raw_row['owner_id'];

		$row['lock_status'] = $raw_row['lock_status'];
		$row['lock_reason'] = $raw_row['lock_reason'];
		$row['lock_date']   = $raw_row['lock_date'];
		$row['lock_by']     = $raw_row['lock_by'];
		$row['lock_by_id']  = $raw_row['lock_by_id'];

		$row['title']       = $raw_row['title'];
		$row['description'] = $raw_row['description'];
		$row['content']     = $raw_row['content'];
		$row['parameters']  = $raw_row['parameters'];

		if($this->ph->is_localized){
			$row['loc_title'] = !empty($raw_row['loc_title']) ? $raw_row['loc_title'] : '';
			$row['loc_description'] = !empty($raw_row['loc_description']) ? $raw_row['loc_description'] : '';
			$row['loc_content'] = !empty($raw_row['loc_content']) ? $raw_row['loc_content'] : '';
		}
		
		$row['slug']     = $raw_row['slug'];

		if($this->ph->is_media_enabled){
			$row['cover_id'] = $raw_row['cover_id'];
			if (!empty($raw_row['cover_id'])) {
				$row['cover_url'] = site_url('file/' . $raw_row['cover_id'] . '/picture?size=thumb');
			}
			$row['album_id'] = $raw_row['album_id'];
			if (!empty($raw_row['album_id'])) {
				$row['album_cover_url'] = site_url('album/' . $raw_row['album_id'] . '/picture?size=thumb');
			}
		}

		if($this->ph->config('post_priority_enabled') === TRUE){
			$row['priority'] = $raw_row['priority'];
		}

		$row['status']        = $raw_row['status'];
		$row['status_str']    = lang('status_' . $raw_row['status']);
		$row['is_pushed']     = $raw_row['is_pushed'];
		$row['is_pushed_str'] = lang('is_pushed_' . $raw_row['is_pushed']);
		$row['last_pushed']   = $raw_row['last_pushed'];
		$row['publish_date']  = $raw_row['publish_date'];
		$row['create_date']   = $raw_row['create_date'];
		$row['modify_date']   = $raw_row['modify_date'];

		if($this->ph->is_tag_enabled){
			$row['tags']       = array();

			$tag_ids           = $this->ph->post_model->get_tags($raw_row['id'], '0');
			if (!empty($tag_ids)) {
				$tags = $this->ph->tag_model->find(array('id' => $tag_ids, 'is_live' => '0'));
				if(is_array($tags)){
					foreach ($tags as $idx => $tag_row) {
						$row['tags'][] = array(
							'id'       => $tag_row['id'],
							'title'    => $tag_row['title'],
							'_mapping' => $tag_row['_mapping'],
						);
					}
				}
			}
		}
		if($this->ph->is_category_enabled){
			$row['categories'] = array();
			/*
			$category_ids = $this->ph->post_model->get_categories($raw_row['id'], '0');

			if (!empty($category_ids)) {
				$categories = $this->ph->category_model->find(array('id' => $category_ids, 'is_live' => '0'));
				foreach ($categories as $idx => $cat_row) {
					$parent_data         = $this->ph->path_data($cat_row['id_path']);
					$row['categories'][] = array(
						'id'         => $cat_row['id'],
						'title'      => $cat_row['title'],
						'title_full' => count($parent_data['titles']) > 0 ? implode(' / ', $parent_data['titles']) . ' / ' . $cat_row['title'] : $cat_row['title'],
						'breadcrumb' => $parent_data['breadcrumb'],
						'_mapping'   => $cat_row['_mapping'],
					);
				}
			}
			/*/
			if (!empty($raw_row['category_id'])) {
				$categories = $this->ph->category_model->find(array('id' => $raw_row['category_id'], 'is_live' => '0'));
				foreach ($categories as $idx => $cat_row) {
					$parent_data         = $this->ph->path_data($cat_row['id_path']);
					$row['categories'][] = array(
						'id'         => $cat_row['id'],
						'title'      => $cat_row['title'],
						'title_full' => count($parent_data['titles']) > 0 ? implode(' / ', $parent_data['titles']) . ' / ' . $cat_row['title'] : $cat_row['title'],
						'breadcrumb' => $parent_data['breadcrumb'],
						'_mapping'   => $cat_row['_mapping'],
					);
				}
			}

			//*/
		}

		return $row;
	}

	function _list() {

		if ($this->_restrict()) {
			return;
		}

		$vals = array();
		$section_prefix = $this->ph->is_default ? '/':$this->ph->section.'/';
		$vals['preview_url'] = base_url('preview/'.$section_prefix);
		$vals['live_url'] = web_url($section_prefix);

		if ($this->uri->is_extension('js')) {
			return $this->_render('ph/post_index.js', $vals);
		}

		if($this->ph->is_tag_enabled){
			$vals['tags'] = array();
			$tags         = $this->ph->tag_model->find(array('status' => 1, '_order_by' => array('title'=>'asc')));
			if (!empty($tags)) {
				foreach ($tags as $idx => $tag_row) {
					$vals['tags'][] = array(
						'id'       => $tag_row['id'],
						'title'    => $tag_row['title'],
						'_mapping' => $tag_row['_mapping'],
					);
				}
			}
		}

		if($this->ph->is_category_enabled){
			$vals['categories'] = array();
			$categories         = $this->ph->category_model->find(array('status' => 1, '_order_by' => array('title'=>'asc')));
			if (!empty($categories)) {
				foreach ($categories as $idx => $cat_row) {
					$vals['categories'][] = array(
						'id'       => $cat_row['id'],
						'title'    => $cat_row['title'],
						'_mapping' => $cat_row['_mapping'],
					);
				}
			}
		}

		$this->_render('ph/post_index', $vals);
	}

	function _view($record_id = false) {

		if ($this->_restrict()) {
			return;
		}

		if (!empty($record_id)) {
			$vals         = array();
			$vals['data'] = $this->ph->post_model->read(array('id' => $record_id, 'is_live' => 0, ));
			if ($this->_is_ext('data')) {
				return $this->_api($vals);
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

		$vals['record'] = NULL;
		$vals['id'] = $record_id;
		$vals['data'] = $this->ph->post_model->new_default_values();
		$vals['data']['default_locale'] = $this->lang->locale();

		$vals['parameter_fields'] = $this->ph->config('post_parameters');
		$vals['parameter_view'] = $this->ph->config('post_parameter_admin_view');

		if ($this->ph->is_tag_enabled) {
			$vals['data']['tag_ids'] = array();
			$vals['tag_list']        = array();
		}
		if ($this->ph->is_category_enabled) {
			$vals['data']['category_ids'] = array();
			$vals['category_list']        = array();
		}

		if (!empty($record_id)) {
			$record = $this->ph->post_model->read(array('section' => $this->ph->section, 'id' => $record_id, 'is_live' => 0, ));

			$vals['data'] = $record;
			$vals['record'] = $this->_mapping_row($record);

			if ($this->ph->is_tag_enabled) {
				$vals['data']['tag_ids'] = $this->ph->post_model->get_tags($record_id, '0');
				$tags                    = $this->ph->tag_model->find(array('is_live' => '0', '_order_by' => array('title ASC')));
				if (!empty($tags) && is_array($tags)) {
					foreach ($tags as $idx => $tag_row) {

						$vals['tag_list'][] = array(
							'id'    => $tag_row['id'],
							'title' => $tag_row['title'],
						);
					}
				}
			}

			if ($this->ph->is_category_enabled) {
				$vals['data']['category_ids'] = $this->ph->post_model->get_categories($record_id, '0');
				$categories                   = $this->ph->category_model->find(array('is_live' => '0', '_order_by' => array('id_path ASC')));
				if (!empty($categories) && is_array($categories)) {
					foreach ($categories as $idx => $cat_row) {

						$parent_data             = $this->ph->path_data($cat_row['id_path']);
						$vals['category_list'][] = array(
							'id'            => $cat_row['id'],
							'title'         => $cat_row['title'],
							'id_titles'     => $parent_data['ids'],
							'parent_titles' => $parent_data['titles'],
						);
					}
				}
			}
		}

		$section_prefix = $this->ph->is_default ? '/':$this->ph->section.'/';
		$vals['live_url'] = web_url($section_prefix.$record['_mapping']);
		$vals['preview_url'] = base_url('preview/'.$section_prefix.$record['_mapping']);

		if($this->ph->is_localized){
			$vals['loc'] = array();

			if(!empty($record)){
				$this->load->model('text_locale_model');
				$vals['loc'] = $this->text_locale_model->find(array('ref_table'=>$this->ph->post_model->table,'ref_id'=>$record_id,'is_live'=>'0','_field_based'=>'locale'));
			}
		}

		if ($this->uri->is_extension('js')) {
			return $this->_render('ph/post_editor.js', $vals);
		}

		if($this->ph->config('post_album_enabled')!==FALSE){

			if(!empty($vals['data']['album_id'])){
				$album_row = $this->album_model->read(array('is_live'=>'0', 'id'=> $vals['data']['album_id']));
				if(empty($album_row['id'])){
					$vals['data']['album_id'] = '';
				}
			}
		}

		$this->_render('ph/post_editor', $vals);
	}

	function _priority() {

		$this->_render('ph/post_position');
	}
	function _search() {
		if ($this->_restrict(null, false)) {
			return $this->_error(ERROR_INVALID_SESSION, ERROR_INVALID_SESSION_MSG);
		}

		if (!$this->_is_ext('data')) {
			return $this->_show_404();
		}

		$vals = array();

		$direction = 'desc';
		$sort      = 'create_date';
		$start     = 0;
		$limit     = 50;

		if ($this->input->get('direction') !== false) {
			$direction = $this->input->get('direction');
		}
		if ($this->input->get('sort') !== false) {
			$sort = $this->input->get('sort');
		}

		if ($this->input->get('offset') !== false) {
			$start = $this->input->get('offset');
		}
		if ($this->input->get('limit') !== false) {
			$limit = $this->input->get('limit');
		}

		if ($this->input->get('page') !== FALSE) {
			$start = ($this->input->get('page') - 1) * $limit;
		}

		if ($this->input->get('q') != false && $this->input->get('q') != '') {

			$req_q = rawurldecode($this->input->get('q'));
			$req_q = preg_replace('#[\s]+#',' ', $req_q);


			$matches = NULL;
			if(preg_match_all('#(tag|category):([^\s\:]+)#',$req_q,$matches)){

				$_tag_ids = array();
				$_category_ids = array();


				if(!empty($matches[0]) && is_array($matches[0])){
					foreach($matches[0] as $idx => $search_keyword){

						$req_q = str_replace($search_keyword, '',$req_q);

						$_type = $matches[1][$idx];
						$_id = $matches[2][$idx];


						if($_type == 'tag' && !in_array($_id, $_tag_ids)){
							$_tag_ids[] = $_id;
						}

						if($_type == 'category' && !in_array($_id, $_category_ids)){
							$_category_ids[] = $_id;
						}
					}
				}

				if(!empty($_tag_ids)){
					$options['_with_tag'] = $_tag_ids;
				}

				if(!empty($_category_ids)){
					$options['category_id'] = $_category_ids;
				}
			}

			if($this->_is_debug()){
				$vals['req_q'] = $req_q;
				$vals['_matches'] = $matches;
				$vals['_with_tag'] = $_tag_ids;
				$vals['_with_category'] = $_category_ids;
			}

			$req_q = preg_replace('#[\s]+#',' ', $req_q);

			if(!empty($req_q)){
				$options['_keyword']        = $req_q;
				$options['_keyword_fields'] = array('id', 'slug', 'title', 'description', 'content');
			}
		}

		$_tag_need     = false;
		$_tag_prod_ids = array();
		$_cat_need     = false;
		$_cat_prod_ids = array();

		if ($this->input->get_post('tags') != false && $this->input->get_post('tags') != '') {
			$req_tags  = explode(",", trim($this->input->get_post('tags')));
			$_tag_need = true;
			if (!empty($req_tags)) {
				$_tag_prod_ids = $this->ph->post_model->get_id_by_tags($req_tags, '0');
			}
		}

		if ($this->input->get_post('categories') != false && $this->input->get_post('categories') != '') {
			$req_categories = explode(",", trim($this->input->get_post('categories')));
			$_cat_need      = true;
			if (!empty($req_categories)) {
				$_cat_prod_ids = $this->ph->post_model->get_id_by_categories($req_categories, '0');
			}
		}

		$_ids_need = false;
		$_ids      = array();

		if ($this->input->get_post('ids') != false && $this->input->get_post('ids') != '') {
			$_ids      = explode(",", trim($this->input->get_post('ids')));
			$_ids_need = true;

		}

		if ($_tag_need) {
			$_ids_need = true;
			foreach ($_tag_prod_ids as $idx => $tag_prod_id) {
				if (!$_cat_need || in_array($tag_prod_id, $_cat_prod_ids)) {
					$_ids[] = $tag_prod_id;
				}
			}
		} elseif ($_cat_need) {
			$_ids_need = true;
			foreach ($_cat_prod_ids as $idx => $cat_prod_id) {
				$_ids[] = $cat_prod_id;
			}
		}

		if ($_ids_need) {
			$options['id'] = $_ids;
		}

		if ($start < 0) {$start = 0;
		}

		if ($limit < 5) {
			$limit            = 10;
		} elseif ($limit % 5 != 0) {
			$limit = 10;
		}

		if (!in_array($sort, array('id', 'slug', 'title', 'publish_date','priority', 'create_date', 'modify_date'))) {
			$sort = 'priority';
		}

		if (strtolower($direction) != 'desc') {
			$direction = 'asc';
		}

		$options['section']   = $this->ph->section;
		$options['is_live']   = '0';
		$options['_order_by'] = array($sort => $direction, 'create_date'=> $direction);

		if($this->ph->is_localized)
			$options['_with_locale'] = $this->lang->locale();

		$result = array(
			'data' => array(),
			'index_from'   => 0,
			'total_record' => 0,
			'limit'        => 0,
			'page'         => 0,
			'total_page'   => 0,
		);
		if ($_ids_need && count($_ids) < 1) {
		} else {
			$start = intval($start);
			$limit = intval($limit);
			$result = $this->ph->post_model->find_paged($start, $limit, $options, false);
		}

		$vals['paging']['offset']     = 0;
		$vals['paging']['total']      = 0;
		$vals['paging']['limit']      = $limit;
		$vals['paging']['page']       = 0;
		$vals['paging']['total_page'] = 0;
		//$vals['__']                 = compact('start','limit','options','result');
		$vals['data']                 = array();
		//$this->params['paginator'] = $paginator;

		if (isset($result['data'])) {

			$section_prefix = $this->ph->is_default ? '/' : $this->ph->section.'/';
			$data = array();
			foreach ($result['data'] as $idx => $row) {
				$new_row              = $this->_mapping_row($row);
				$new_row['_index']    = $result['index_from']+$idx;


				$new_row['_edit_url'] = site_url('s/' . $this->ph->section. '/post/' . $row['id'] . '/edit');

				$new_row['_live_url'] = web_url($section_prefix.$row['id']);
				$new_row['_preview_url'] = base_url('preview/'.$section_prefix.$row['id']);
				$data[]                  = $new_row;
			}

			$vals['paging']['offset']     = $result['index_from'];
			$vals['paging']['total']      = $result['total_record'];
			$vals['paging']['limit']      = $result['limit'];
			$vals['paging']['page']       = $result['page'];
			$vals['paging']['total_page'] = $result['total_page'];
			$vals['data']                 = $data;
		}
		return $this->_api($vals);
	}

	function _publish_album($album_id, $new_state){
		$edit_info = $this->_get_editor_info();

		if(!empty($album_id)){
			$old_album_row = $this->album_model->read(array('id' =>$album_id, 'is_live' => 1));
			
			$this->album_model->delete(array('id' => $album_id, 'is_live' => 1));
			$this->album_photo_model->delete(array('album_id' => $album_id, 'is_live' => 1));

			$new_album_row = $this->album_model->read(array( 'id' => $album_id, 'is_live' => 0));
			$new_album_row['is_live'] = 1;
			$this->album_model->save($new_album_row,NULL,$edit_info);

			$photos = $this->album_photo_model->find(array('is_live'=>'0', 'album_id'=>$album_id));
			if(!empty($photos) && is_array($photos)){
				foreach($photos as $idx => $photo_row){
					$photo_row['is_live'] = 1;

					//log_message('debug','Ph/post/publish_album#'.$album_id.'@'.$idx.' = '.print_r($photo_row,true));
					$this->album_photo_model->save($photo_row, NULL, $edit_info);
					//log_message('debug','Ph/post/publish_album#'.$album_id.'@'.$idx.' > '.$this->db->last_query());
				}
			}

			$this->album_model->save($new_state, array('id' => $album_id));
			$this->album_photo_model->save($new_state, array('album_id' => $album_id));
		}
	}

	function _publish($id = false, $return = false) {
		if ($this->_restrict(null, false)) {
			$this->_error(ERROR_INVALID_SESSION, ERROR_INVALID_SESSION_MSG);
			return;
		}

		if (empty($id)) {
			$this->_error(ERROR_INVALID_DATA, 'Passed invalid value.');
			return;
		}

		$edit_info = $this->_get_editor_info();

		$old_row = $this->ph->post_model->read(array('id' => $id, 'is_live' => 1));

		// clear cache
		if (isset($old_row['id'])) {
			cache_remove('ph/post/' . $old_row['id']);
			cache_remove('ph/post/' . $old_row['id'] . '/*');

			cache_remove('ph/post/' . $old_row['_mapping']);
			cache_remove('ph/post/' . $old_row['_mapping'] . '/*');

			$this->ph->post_model->delete(array('section' => $this->ph->section, 'id' => $id, 'is_live' => 1));
			
			$this->relationship_model->delete(array('ref_table'=>$this->ph->post_model->table, 'ref_id'=>$id,'is_live'=>1));
		}
		$new_row = $this->ph->post_model->read(array('section' => $this->ph->section, 'id' => $id, 'is_live' => '0'));
		//log_message('debug', 'Staticpages/post/publish, new row:' . print_r($new_row, true));
		$new_row['is_live'] = 1;
		$result             = $this->ph->post_model->save($new_row);

		$new_state = array('is_pushed' => 1, 'last_pushed' => time_to_date());
		$this->ph->post_model->save($new_state, array('section' => $this->ph->section, 'id' => $new_row['id']));

		// publish album records
		if($this->ph->config('post_album_enabled')!==FALSE){

			if(!empty($new_row['album_id']))
				$this->_publish_album($new_row['album_id'],$new_state);
		}



		// publish ContentBuilder's album records
		if(!empty($new_row['parameters']['cb'])){
			$cb = NULL;
			try{
				$cb = json_decode($new_row['parameters']['cb'],true);
			}catch(Exception $exp){}
			//log_message('debug','Ph/post/publish/cb: '.print_r($cb,true));

			if(is_array($cb)){
				foreach($cb as $cbRow){
					if($cbRow['control'] != 'gallery')continue;

					if(!empty($cbRow['data']['album_id'])){
						$this->_publish_album($cbRow['data']['album_id'],$new_state);
					}
				}
			}
		}


		if($this->ph->is_tag_enabled){
			// relation tables
			$tag_ids = $this->ph->post_model->get_tags($id, '0');
			$this->ph->post_model->clear_tags($id, '1');
			if(!empty($tag_ids) && is_array($tag_ids)){

				// find the staging content
				$tags = $this->ph->tag_model->find(array('id'=>$tag_ids,'is_live'=>'0','_field_based'=>'id'));
				$_tag_ids = array();
				foreach($tag_ids as $tag_id){
					if(empty($tag_id) || in_array($tag_id,$_tag_ids)) continue;
					$new_tag_row = $tags[$tag_id];
					$_tag_ids[] = $tag_id;

					// replace the live record if exist or insert new live record
					$live_tag_row = $this->ph->tag_model->read(array('id'=>$tag_id,'is_live'=>'1'));

					cache_remove('ph/tag/' . $live_tag_row['id']);
					cache_remove('ph/tag/' . $live_tag_row['id'] . '/*');

					cache_remove('ph/tag/' . $live_tag_row['_mapping']);
					cache_remove('ph/tag/' . $live_tag_row['_mapping'] . '/*');

					if(!empty($live_tag_row['id'])){
						unset($new_tag_row['id']);
						unset($new_tag_row['is_live']);
						$this->ph->tag_model->save($new_tag_row, array('id'=>$tag_id,'is_live'=>'1'), $edit_info);
					}else{
						$new_tag_row['is_live'] = '1';
						$this->ph->tag_model->save($new_tag_row, NULL, $edit_info);
					}
				}
				if(!empty($_tag_ids))
					$this->ph->post_model->set_tags($id, $_tag_ids, '1');
			}
		}

		if($this->ph->is_category_enabled){
			$category_ids = $this->ph->post_model->get_categories($id, '0');
			$this->ph->post_model->clear_categories($id, '1');
			if(!empty($category_ids) && is_array($category_ids)){

				// find the staging content
				$categories = $this->ph->category_model->find(array('id'=>$category_ids,'is_live'=>'0','_field_based'=>'id'));
				$_category_ids = array();
				foreach($category_ids as $category_id){
					if(empty($category_id) || in_array($category_id,$_category_ids)) continue;
					$new_category_row = $categories[$category_id];

					// replace the live record if exist or insert new live record
					$live_category_row = $this->ph->category_model->read(array('id'=>$category_id,'is_live'=>'1'));

					cache_remove('ph/category/' . $live_category_row['id']);
					cache_remove('ph/category/' . $live_category_row['id'] . '/*');

					cache_remove('ph/category/' . $live_category_row['_mapping']);
					cache_remove('ph/category/' . $live_category_row['_mapping'] . '/*');

					if(!empty($live_category_row['id'])){
						unset($new_category_row['id']);
						unset($new_category_row['is_live']);
						$this->ph->tag_model->save($new_category_row, array('id'=>$category_id,'is_live'=>'1'), $edit_info);
					}else{
						$new_category_row['is_live'] = '1';
						$this->ph->tag_model->save($new_category_row, NULL, $edit_info);
					}
				}
				if(!empty($category_ids))
					$this->ph->post_model->set_categories($id, $category_ids, '1');
			}
		}
		
		// Copy relationships
		$relationships = $this->relationship_model->find(array('ref_table'=>$this->ph->post_model->table, 'ref_id'=>$id,'is_live'=>0));
		if(!empty($relationships) && is_array($relationships)){
			foreach($relationships as $r_row){
				$r_row['is_live'] = 1;
				$this->relationship_model->save($r_row);
			}
		}
		

		// re-publish content
		$this->load->helper('file');
		$pub_folders = array('cb','cover','attachments','files','photos');
		foreach($pub_folders as $folder_name){
			$folder_dir = PUB_DIR.DS.$this->ph->section.DS.$result['id'].DS.$folder_name;
			if(file_exists($folder_dir) && is_dir($folder_dir))
				delete_files($folder_dir);
		}

		if($this->ph->is_localized){
			$all_curr_loc_rows = $this->text_locale_model->find(array('is_live'=>'0','ref_table'=>$this->ph->post_model->table, 'ref_id'=>$id,'_field_based'=>'locale'));

			$this->text_locale_model->delete(array('is_live'=>'1','ref_table'=>$this->ph->post_model->table, 'ref_id'=>$id));
			if(is_array($all_curr_loc_rows)){
				foreach($all_curr_loc_rows as $loc_code => $loc_data){
					$loc_data['is_live'] = '1';
					$this->text_locale_model->save($loc_data);
				}
			}
		}

		$output = array('id' => $id, 'last_pushed' => $new_state['last_pushed']);
		$this->db->queries = array();

		//$output['_queries'] = $this->db->queries;

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

		$records = $this->ph->post_model->find(array('section' => $this->ph->section, 'id' => $ids, 'is_live' => 0, ));

		$data = array();
		if (is_array($records) && count($records) > 0) {
			foreach ($records as $idx => $row) {

				if ($action == 'enable') {
					$this->ph->post_model->save(array('status' => 1, 'is_pushed' => '0'), array('section' => $this->ph->section, 'id' => $row['id'], 'is_live' => 0));
					$data[$row['id']] = TRUE;
				} elseif ($action == 'disable') {
					$this->ph->post_model->save(array('status' => 0, 'is_pushed' => '0'), array('section' => $this->ph->section, 'id' => $row['id'], 'is_live' => 0));
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

	function validate_slug($str){
		if(empty($str)) return TRUE;
		if(strlen($str) < 5) {

			$this->form_validation->set_message('validate_slug', '%s is not a valid shorten name.');
			return FALSE;
		}
		/*
		$row = $this->ph->post_model->read(array('slug'=>$str,'_select'=>'id'));
		if(!empty($row['id'])){
			$this->form_validation->set_message('validate_slug', '%s is exist.');
			return FALSE;
		}
		//*/
		return TRUE;
	}

	function _save($id = false) {

		$vals = array();
		if ($this->_restrict()) {
			return;
		}

		$vals    = array();
		$success = true;

		if (!$id) {
			$id = $this->input->get_post('id');
		}

		$record = $id ? $this->ph->post_model->read(array('id' => $id, 'is_live' => 0)) : NULL;
		if ($id && (!isset($record['id']) || $record['id'] != $id)) {
			return $this->_show_404('record_not_found');
		}
		$data     = array();
		$def_vals = $this->ph->post_model->new_default_values();
		foreach ($def_vals as $key => $defVal) {
			$data[$key] = $defVal;
			if (isset($record[$key])) {
				$data[$key] = $record[$key];
			}

			if ($this->input->post($key) !== false) {
				$data[$key] = $this->input->post($key);
			}
		}

		if ((substr($data['publish_date'], 0, 4) == '0000' || empty($data['publish_date'])) && $data['status'] == '1') {
			$data['publish_date'] = date('Y-m-d H:i:s');
		}

		if ($this->ph->is_tag_enabled) {
			$tags_str = $this->input->post('tag_ids');
			$tags     = explode(",", trim($tags_str));
		}

		if ($this->ph->is_category_enabled) {
			$categories_str = $this->input->post('category_ids');
			$categories     = explode(",", trim($categories_str));
		}

		// remark this content is not pushed to live
		$data['is_pushed'] = '0';
		if (isset($record['is_pushed']) && $record['is_pushed'] > 0) {
			$data['is_pushed'] = '2';
		}

		$success = true;

		// locale data
		$all_loc_data = $this->input->post('loc');

		$this->load->library('form_validation');
		$this->form_validation->set_rules('slug', ('lang:slug'), 'trim|callback_validate_slug');


		if($this->ph->is_localized){

			$has_any_enabled_locale = FALSE;
			foreach($this->lang->get_available_locale_keys() as $loc_code){
				if(!isset($loc[$loc_code]['status']) || $loc[$loc_code]['status'] == '1'){
					$has_any_enabled_locale = TRUE;
					$this->form_validation->set_rules('loc['.$loc_code.'][title]', ('lang:title'), 'trim|required');
				}
			}

			if(!$has_any_enabled_locale){
				$this->form_validation->set_message('loc_status',lang('none_loc_status_enabled'));
			}

		}else{
			$this->form_validation->set_rules('title', ('lang:slug'), 'trim|required');

		}
		
		$success = $this->form_validation->run() != FALSE;

		if(!$success){
			$validate = array();
			foreach($data as $field => $val){
				
				if($this->ph->is_localized){
					$_field = $field ;
					foreach($this->lang->get_available_locale_keys() as $loc_code){
						$_field = 'loc['.$loc_code.']['.$field.']';
						$error = $this->form_validation->error($_field,NULL,NULL);
						if(!empty($error))
							$validate['fields'][$_field] = $error;
					}
				}else{

					$error = $this->form_validation->error($field,NULL,NULL);
					if(!empty($error))
						$validate['fields'][$field] = $error;
				}
			}
			return $this->_error(ERROR_INVALID_DATA, '', 200, $validate);
		}

		if ($success) {

			$default_locale = $this->input->post('default_locale');
			$locale = $this->lang->locale();
			
			
			
			// copying below contents for localized
			$locale_fields = array('title','description','content','parameters','status','cover_id');
			if($this->ph->is_localized){
				if(empty($default_locale)) $data['default_locale'] = $default_locale = $this->lang->locale();

				$loc_data = isset($all_loc_data[$default_locale]) ? $all_loc_data[$default_locale] : NULL;

				$sql_loc_data = array();
				foreach($locale_fields as $idx => $field_name){
					// loc.status will not be used for replacing raw data.
					if($field_name != 'status' && isset($loc_data[$field_name]))
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

			if(empty($data['slug']))  $data['slug'] = $data['title'];
			$data['slug'] = seo_string($data['slug']);

			$edit_info =$this->_get_editor_info();
			if (!$id) {
				$exist_row = $this->ph->tag_model->find(array(
					'section'=>$this->ph->section,
					'slug'=>$data['slug'], 
					));

				if(!empty($exist_row['id'])){
					$data['slug'] = random_string('alnum', 16);
				}

				$data['owner_type'] = '';
				$data['owner_id'] = '';
				$data['is_live']  = '0';
				if(!isset($data['priority']))$data['priority']  = '0';
				$data['section'] = $this->ph->section;
				$result           = $this->ph->post_model->save($data,null, $edit_info);
				$result['action'] = 'add';
			} else {
				$exist_row = $this->ph->tag_model->find(array(
					'section'=>$this->ph->section,
					'slug'=>$data['slug'], 
					'id !'=>$id));

				if(!empty($exist_row['id'])){
					$data['slug'] = random_string('alnum', 16);
				}

				unset($data['id']);
				$result           = $this->ph->post_model->save($data, array('section' => $this->ph->section, 'id' => $id, 'is_live' => 0, ), $edit_info);
				$result['action'] = 'edit';
			}
			//$messages['sqls'][]= $this->db->last_query();
			$record = $this->ph->post_model->read(array('section' => $this->ph->section, 'id' => $result['id'], 'is_live' => 0));
			
			
			$this->relationship_model->delete(array('is_live'=>'0','ref_table'=>$this->ph->post_model->table, 'ref_id'=>$record['id']));
						
			$all_paramaters = $this->ph->config('post_parameters');
			if(!empty($all_parameters )){
				foreach( $all_parameters as $parameter_name => $parameter_info) {
				
					
				
					$allowed = false;
					if(isset($parameter_info['is_external_data']) && $parameter_info['is_external_data'] == TRUE) {
						$allowed = true;
					}
					if(isset($parameter_info['control']) && $parameter_info['control'] == 'select' ) {
						$allowed = true;
					}
					
					if(!$allowed ) continue;
					
					$external_table = data('external_table', $parameter_info,'');
					
					if(isset($parameter_info['localized']) && $parameter_info['localized'] == TRUE){
						if($this->ph->is_localized){
							foreach($this->lang->get_available_locale_keys() as $loc_code){
							
								$r_data = array();
								$r_data['ref_table'] = $this->ph->post_model->table;
								$r_data['ref_section'] = $this->ph->section;
								$r_data['locale_code'] = $loc_code;
								$r_data['ref_id'] = $record['id'];
								$r_data['is_live'] = '0';
								$r_data['term_type'] = 'parameters';
								$r_data['term_field'] = $parameter_name;
								$r_data['term_table'] = $external_table;
								$r_data['term_id'] = isset($loc[$loc_code]['parameters']) ? data($paramter_name, $loc[$loc_code]['parameters']) : NULL;
								
								$this->relationship_model->save($r_data, NULL, $edit_info);
							}
						}
					}else{
						
						$r_data = array();
						$r_data['ref_table'] = $this->ph->post_model->table;
						$r_data['ref_section'] = $this->ph->section;
						$r_data['locale_code'] = NULL;
						$r_data['ref_id'] = $record['id'];
						$r_data['is_live'] = '0';
						$r_data['term_type'] = 'parameters';
						$r_data['term_field'] = $parameter_name;
						$r_data['term_table'] = $external_table;
						$r_data['term_id'] = isset($data['parameters']) ? data($paramter_name, $data['parameters']) : NULL;
						
						$this->relationship_model->save($r_data, NULL, $edit_info);
					}
				}
			}
			
			if ($this->ph->is_tag_enabled) {
				$tags_records = $this->ph->tag_model->find(array('_mapping' => $tags, 'is_live' => '0'));
				$tags_mapping = array();
				if (is_array($tags_records) && count($tags_records) > 0) {
					foreach ($tags_records as $idx => $tag_row) {
						$tags_mapping[] = $tag_row['id'];
						
						
						$r_data = array();
						$r_data['ref_table'] = $this->ph->post_model->table;
						$r_data['ref_section'] = $this->ph->section;
						$r_data['locale_code'] = NULL;
						$r_data['ref_id'] = $record['id'];
						$r_data['is_live'] = '0';
						$r_data['sequence'] = $idx;
						$r_data['term_type'] = 'tags';
						$r_data['term_field'] = 'tag';
						$r_data['term_table'] = $this->ph->tag_model->table;
						$r_data['term_id'] = $tag_row['id'];
						
						$this->relationship_model->save($r_data, NULL, $edit_info);
					}
				}

				$this->ph->post_model->set_tags($result['id'], $tags_mapping, '0');
			}

			if ($this->ph->is_category_enabled) {
				$categories_records = $this->ph->category_model->find(array('_mapping' => $categories, 'is_live' => '0'));

				$categories_mapping = array();
				if (is_array($categories_records) && count($categories_records) > 0) {
					foreach ($categories_records as $idx => $cat_row) {
						$categories_mapping[] = $cat_row['id'];
						
						
						
						$r_data = array();
						$r_data['ref_table'] = $this->ph->post_model->table;
						$r_data['ref_section'] = $this->ph->section;
						$r_data['locale_code'] = NULL;
						$r_data['ref_id'] = $record['id'];
						$r_data['is_live'] = '0';
						$r_data['sequence'] = $idx;
						$r_data['term_type'] = 'categories';
						$r_data['term_field'] = 'category';
						$r_data['term_table'] = $this->ph->category_model->table;
						$r_data['term_id'] = $tag_row['id'];
						
						$this->relationship_model->save($r_data, NULL, $edit_info);
					}
				}

				$this->ph->post_model->set_categories($result['id'], $categories_mapping, '0');
			}
			
			if($this->ph->is_localized){
				// required model
				$this->load->model('text_locale_model');


				$all_curr_loc_rows = $this->text_locale_model->find(array('ref_table'=>$this->ph->post_model->table,'ref_id'=>$result['id'], 'is_live'=>0,'_field_based'=>'locale'));

				foreach($this->lang->get_available_locale_keys() as $loc_code){
					$loc_data = isset($all_loc_data[$loc_code]) ? $all_loc_data[$loc_code] : NULL;

					//log_message('debug','old_loc['.$loc_code.']='.print_r($loc_data,true));

					// skip it if no data for this locale
					if(empty($loc_data)) continue;
					$curr_loc_row = isset($all_curr_loc_rows[$loc_code]) ? $all_curr_loc_rows[$loc_code] : NULL;

					$sql_loc_data = array();
					foreach($locale_fields as $idx => $field_name){
						if(isset($loc_data[$field_name]))
							$sql_loc_data [$field_name] = $loc_data[$field_name];
					}

					if(empty($curr_loc_row['id'])){
						$sql_loc_data['ref_table'] = $this->ph->post_model->table;
						$sql_loc_data['ref_id'] = $result['id'];
						$sql_loc_data['is_live'] = '0';
						$sql_loc_data['locale'] = $loc_code;
						$this->text_locale_model->save($sql_loc_data, NULL, $edit_info);

					}else{
						$this->text_locale_model->save($sql_loc_data, array('id'=>$curr_loc_row['id'],'is_live'=>'0','locale'=>$loc_code), $edit_info);
					}
					//log_message('debug','new_loc['.$loc_code.']='.print_r($sql_loc_data,true));
					//log_message('debug','set_locale_content['.$loc_code.']='.$this->db->last_query());
				}
			}

			//log_message('debug','Ph/post/save: '.print_r($this->db->queries,true));

			// clear content list
			cache_remove('ph/post/' . $result['id']);
			cache_remove('ph/post/' . $result['id'] . '/*');

			$vals['id']       = $result['id'];
			$vals['method']   = $result['action'];
			//$vals['_mapping'] = $record['_mapping'];
			$section_prefix = $this->ph->is_default ? '/':$this->ph->section.'/';
			$vals['live_url'] = web_url($section_prefix.$record['_mapping']);
			$vals['preview_url'] = base_url('preview/'.$section_prefix.$record['_mapping']);
			$vals['data']['slug'] = !empty($record['slug']) ? $record['slug'] : '';
			$vals['data']['publish_date'] = !empty($record['publish_date']) ? $record['publish_date'] : '';
		}

		if ($this->uri->is_extension('')) {
			redirect('s/' . $this->ph->section . '/post/' . $id);
			return;
		}
		return $this->_api($vals);
	}

	function _remove() {
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

		$records = $this->ph->post_model->find(array('id' => $ids));
		if (is_array($records) && count($records) > 0) {
			foreach ($records as $idx => $row) {

				cache_remove('ph/post/' . $row['id']);
				cache_remove('ph/post/' . $row['id'] . '/*');

				cache_remove('ph/post/' . $row['_mapping']);
				cache_remove('ph/post/' . $row['_mapping'] . '/*');
			}

			$this->text_locale_model->delete(array('ref_table'=>$this->ph->post_model->table, 'ref_id'=>$ids));
			
			$this->relationship_model->delete(array('ref_table'=>$this->ph->post_model->table, 'ref_id'=>$ids));

			$this->ph->post_model->remove(array('section' => $this->ph->section, 'id' => $ids));
			$this->ph->post_model->clear_tags($ids, '0');
			$this->ph->post_model->clear_tags($ids, '1');
			$this->ph->post_model->clear_categories($ids, '0');
			$this->ph->post_model->clear_categories($ids, '1');

			return $this->_api(array('data' => $ids));
		} else {
			return $this->_error(ERROR_NO_RECORD_LOADED, 'No record has been loaded.');
		}
	}
	
	public function _submit(){
			 
			foreach($_POST['items'] as $post_id=>$post_priority)
				$post_row = $this->ph->post_model->save( array('priority'=>''.$post_priority), array('id'=> $post_id, 'is_live'=>'0'));
			
			

			return $this->_api($post_row);
		
// die("23");
	}	
}
