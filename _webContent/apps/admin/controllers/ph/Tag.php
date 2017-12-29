<?php

use Dynamotor\Helpers\PostHelper;

class Tag extends MY_Controller
{
	var $section = 'page';
	var $seg_offset = 2;
	var $ph = NULL;

	function __construct(){
		parent::__construct();

		$this->load->config('ph');
		
		$this->seg_offset = 3;
		$this->section = $this->uri->segment($this->seg_offset-1);
		if(preg_match('/^[a-zA-Z]+$/', $this->section)){
			$this->ph = PostHelper::get_section($this->section);
		}else{
			$this->seg_offset = 4;
			$this->section = $this->uri->segment($this->seg_offset-1);
			if(preg_match('/^[a-zA-Z]+$/', $this->section)){
				$this->ph = PostHelper::get_section($this->section);
			}
		}

		$this->load->model('text_locale_model');
	}
	
	function _render($view, $vals=false, $layout = false, $theme = false) {

		if(!empty($this->ph)){
			if(!empty($this->ph->config('admin_menu_tree'))){
				$ary = $this->ph->config('admin_menu_tree');
				$ary[] = 'tag';
				$this->config->set_item('main_menu_selected',$ary);
			}else{

				$this->config->set_item('main_menu_selected', array($this->section,'tag'));
			}

			$vals['section_path_prefix'] = ('s/'.$this->ph->section);
			$vals['section_url_prefix'] = site_url($vals['section_path_prefix']);
			$vals['endpoint_path_prefix'] = ('s/'.$this->ph->section.'/tag');
			$vals['endpoint_url_prefix'] = site_url($vals['endpoint_path_prefix']);
			$vals['view_path_prefix'] = ('ph/tag_');
		}
		$vals['section'] = $this->section;
		$vals['ph'] = $this->ph;
		return parent::_render($view,$vals, $layout, $theme);
	}
	
	function _remap(){

		if(empty($this->ph))
			return $this->_error(ERROR_INVALID_DATA, 'Instance of PostHelper "'.$this->section.'" does not exist.');
		
		if(!$this->ph->is_tag_enabled)
			return;

		$seg_offset = $this->seg_offset;

		$s1 = $this->uri->segment($seg_offset+1);
		$s2 = $this->uri->segment($seg_offset+2);
		
		if(in_array($s1, array('','index','selector'))){
			return $this->_list();
		}
		if(in_array($s1, array('all'))){
			return $this->_all();
		}
		if(in_array($s1, array('search'))){
			return $this->_search();
		}
		if(in_array($s1, array('batch'))){
			return $this->_batch($s2);
		}
		if(in_array($s1, array('save'))){
			return $this->_save();
		}
		if(in_array($s1, array('remove'))){
			return $this->_remove();
		}
		if(in_array($s1, array('add'))){
			return $this->_editor();
		}
		if(preg_match("/^[a-zA-Z0-9-_\.]+$/",$s1)){
			if($s2 == 'edit')
				return $this->_editor($s1);
		}
		
		return $this->_show_404();
	}
	
	function _mapping_row($raw_row){
		$row = array();
		$row['id'] = $raw_row['id'];
		
		$row['owner_type'] = $raw_row['owner_type'];
		$row['owner_id'] = $raw_row['owner_id'];
		
		$row['slug'] = $raw_row['section'];
		$row['_mapping'] = $raw_row['_mapping'];
		$row['title'] = $raw_row['title'];

		if($this->ph->is_localized){
			$row['loc_title'] = $raw_row['loc_title'];
			if(isset($raw_row['loc_description']))
				$row['loc_description'] = $raw_row['loc_description'];
			if(isset($raw_row['loc_content']))
				$row['loc_content'] = $raw_row['loc_content'];
		}


		$row['slug'] = $raw_row['slug'];
		$row['status'] = $raw_row['status'];
		$row['status_str'] = lang('status_'.$raw_row['status']);
		$row['is_pushed'] = $raw_row['is_pushed'];
		$row['is_pushed_str'] = lang('is_pushed_'.$raw_row['is_pushed']);
		$row['last_pushed'] = $raw_row['last_pushed'];

		$row['num_posts'] = $this->ph->post_model->get_total(array('_with_tag'=> $raw_row['id'],'is_live'=>'0'));
		
		$row['published'] = $raw_row['publish_date'];
		$row['created'] = $raw_row['create_date'];
		$row['modified'] = !$raw_row['modify_date'] || substr($raw_row['modify_date'],0,4) == '0000'?$raw_row['create_date']:$raw_row['modify_date'];
		return $row;
	}
	
	function _list(){
		
		if( $this->_restrict()){
			return;
		}
		$vals = array();
		$section_prefix = $this->ph->is_default ? '/':$this->ph->section.'/';
		$vals['preview_url'] = base_url('preview/'.$section_prefix.'/tag');
		$vals['live_url'] = web_url($section_prefix.'/tag');
		$this->_render('ph/tag_index',$vals);
	}
	
	function _editor($record_id=false){
		
		if( $this->_restrict()){
			return;
		}
		
		$record = NULL;
		$vals = array();
		
		$vals['data'] = $this->ph->tag_model->new_default_values();
		$vals['data']['default_locale'] = $this->lang->locale();
		$vals['record'] = NULL;
		$vals['id'] = $record_id;
		if(!empty($record_id)){
			$record = $this->ph->tag_model->read(array('id'=>$record_id));

			$vals['data'] = $record;
			$vals['record'] = $this->_mapping_row($record);
		}

		$vals['parameter_fields'] = $this->ph->config('tag_parameters');
		$vals['parameter_view'] = $this->ph->config('tag_parameter_admin_view');

/*
		if($this->ph->is_localized){
			$vals['loc'] = array();

			if(!empty($record)){
				$this->load->model('text_locale_model');
				$vals['loc'] = $this->text_locale_model->find(array('ref_table'=>$this->ph->tag_model->table,'ref_id'=>$record_id,'is_live'=>'0','_field_based'=>'locale'));
			}
		}
//*/

		if($this->uri->is_extension('js'))
			return $this->_render('ph/tag_editor.js',$vals);
		
		if($this->uri->is_extension(array('','htm','html')))
			return $this->_render('ph/tag_editor',$vals);
		return $this->_show_404();
	}
	
	function _all(){
		if($this->_restrict())
			return;
		$vals = array();
		
		$tags = $this->ph->tag_model->find(array('is_live'=>'0',  '_order_by'=>array('parent_id'=>'asc','create_date'=>'asc',)));
		$vals['data'] = $tags;
		
		return $this->_api($vals);
	}
	
	function _search(){
		if($this->_restrict(null,false)){
			return $this->_error(ERROR_INVALID_SESSION, 'Valid session required.');
		}
		
		if(!$this->_is_ext('data')){ 
			return $this->_show_404('extension_not_matched');
		}
		$vals = array();
		
		$store_tags = array();
		$have_query = false;
		$success = true;
		
		$opts = array(
			'_keyword_fields'=>array('title'),
			'_order_by'=>array('create_date'=>'asc')
		);
		
		if($this->input->get('term')!=NULL){
			if($this->input->get('term') == ''){
			}else{
				$have_query = true;
				$opts['_keyword'] = $this->input->get('term');
			}
		}
		
		if($this->input->get('q')!=NULL){
			if($this->input->get('q') == ''){
			}else{
				$have_query = true;
				$opts['_keyword'] = $this->input->get('q');
			}
		}
		
		if($this->input->get('id') != NULL){
			if($this->input->get('ids') == ''){
			}else{
				$have_query = true;
				$opts['id'] = $this->input->get('id');
			}
		}
		if($this->input->get('ids') != NULL){
			if($this->input->get('ids') == ''){
			}else{
				$have_query = true;
				$opts['id'] = explode(",",trim($this->input->get('ids')));
			}
		}
		
		
		if($this->input->get('autocomplete') == 'yes'){
			if($have_query){
				$opts['is_live'] = '0';
				$store_tags = $this->ph->tag_model->find($opts);
			}
			$data = array();
			if(is_array($store_tags) && count($store_tags)>0){
				foreach($store_tags as $idx => $tag_row){
					$data[] = array(
						'value'=>$tag_row['id'],
						'label'=>$tag_row['title'],
					);
				}
			}
			return $this->_api($data);
		}
		
		$direction = 'desc';
		$sort = 'create_date';
		$start = 0;
		$limit = 50;
		
		if($this->input->get('direction')!==false){
			$direction = $this->input->get('direction');
		}
		if($this->input->get('sort')!==false){
			$sort = $this->input->get('sort');
		}
		
		if($this->input->get('offset')!==false){
			$start = $this->input->get('offset');
		}

		if($this->input->get('limit')!==false){
			$limit = $this->input->get('limit');
		}
			
		if($this->input->get('page')!==FALSE){
			$start = ($this->input->get('page') - 1 ) * $limit;
		}
		
		if($this->input->get('q')!=false && $this->input->get('q')!=''){
			$options['_keyword'] = $this->input->get('q');
			$options['_keyword_fields'] = array('id','slug','title');
		}
		
		if($start<0) $start = 0;
		if($limit < 5) $limit = 10;
		elseif($limit%5 != 0) $limit = 10; 
		
		if(!in_array($sort,array('id','slug','title','create_date','modify_date'))) $sort = 'create_date';
		if(strtolower($direction) != 'desc') $direction = 'asc';
		
		$options['is_live'] = '0';
		$options['_order_by'] = array($sort=>$direction,'create_date'=>$direction);
		/*
		if($this->ph->is_localized)
			$options['_with_locale'] = $this->lang->locale();
		//*/

		$result = $this->ph->tag_model->find_paged($start,$limit,$options,false);
		

		$vals['paging']['offset']     = $start;
		$vals['paging']['total']      = 0;
		$vals['paging']['limit']      = $limit;
		$vals['paging']['page']       = 0;
		$vals['paging']['total_page'] = 0;
		$vals['data']                 = array();
		if(isset($result['data'])){
			$section_prefix = $this->ph->is_default ? '/' : $this->ph->section.'/';
			
			$data = array();
			foreach($result['data'] as $idx => $row){
				$new_row = $this->_mapping_row($row);
				$new_row['_index'] = $result['index_from']  + $idx;
				$new_row['_edit_url'] = site_url('s/'.$this->ph->section.'/tag/'.$row['id'].'/edit');
				$new_row['_preview_url'] = base_url('preview/'.$section_prefix.'tag/'.$row['_mapping'].'');
				$new_row['_live_url'] = web_url($section_prefix.'tag/'.$row['_mapping'].'');
				$data[] = $new_row;
			}
			
			$vals['paging']['offset'] = $result['index_from'];
			$vals['paging']['total'] = $result['total_record'];
			$vals['paging']['limit'] = $result['limit'];
			$vals['paging']['page'] = $result['page'];
			$vals['paging']['total_page'] = $result['total_page'];
			$vals['data'] = $data;
		}
		
		
		return $this->_api($vals);
	}
	
	function _publish($id=false,$return=false){
		if($this->_restrict(null,false)){
			return $this->_handle_error(true,ERROR_INVALID_SESSION, 'Valid session required.');
		}
		
		if(empty($id)){
			return $this->_handle_error(true,ERROR_INVALID_DATA, 'Passed invalid value.');
		}
		
		$old_row = $this->ph->tag_model->read(array('id'=>$id,'is_live'=>1));
		if(isset($old_row['id'])){
			cache_remove('ph/tag/'.$old_row['id']);
			cache_remove('ph/tag/'.$old_row['id'].'/*');
			
			cache_remove('ph/tag/'.$old_row['_mapping']);
			cache_remove('ph/tag/'.$old_row['_mapping'].'/*');

			$this->ph->tag_model->delete(array('id'=>$id,'is_live'=>1));
		}
		
		$new_row = $this->ph->tag_model->read(array('id'=>$id,'is_live'=>0));
		if($this->_is_debug())
			log_message('debug','Ph/tag/publish, new row:'.print_r($new_row,true));
		
		$new_row['is_live'] = 1;
		$result = $this->ph->tag_model->save($new_row);
		
		$new_state = array('is_pushed'=>1, 'last_pushed'=>time_to_date());
		$this->ph->tag_model->save($new_state,array('id'=>$new_row['id']));

		/*
		if($this->ph->is_localized){
			$all_curr_loc_rows = $this->text_locale_model->find(array('is_live'=>'0','ref_table'=>$this->ph->tag_model->table, 'ref_id'=>$id,'_field_based'=>'locale'));

			$this->text_locale_model->delete(array('is_live'=>'1','ref_table'=>$this->ph->tag_model->table, 'ref_id'=>$id));
			foreach($all_curr_loc_rows as $loc_code => $loc_data){
				$loc_data['is_live'] = '1';
				$this->text_locale_model->save($loc_data);
			}
		}
		//*/
		
		$output = array('id'=>$id,'last_pushed'=>$new_state['last_pushed']);
		
		if(isset($result['id'])){
			if($result['id'] == $id){
				if($return)
					return $output;
				return $this->_api($output);
			}
			return $this->_handle_error($return, ERROR_INVALID_DATA, 'Invalid id after save live content.');
		}
		return $this->_handle_error($return, ERROR_RECORD_SAVE_ERROR, 'Cannot save record in database.');
	}
	

	function _batch($action=''){
		if($this->_restrict(null,false)){
			$this->_error(ERROR_INVALID_SESSION, 'Valid session required.');
			return;
		}
		
		$ids = $this->input->post('ids');
		$ids = explode(",", trim($ids));
		if(!is_array($ids)){
			$this->_error(ERROR_INVALID_DATA, 'Passed invalid value.');
			return;
		}
		
		$records = $this->ph->tag_model->find(array('id'=>$ids,'is_live'=>'0'));
		
		$data = array();
		if(is_array($records) && count($records)>0){
			foreach($records as $idx => $row){

				if($action == 'enable'){
					$this->ph->tag_model->save(array('status'=>1), array('id'=>$row['id'],'is_live'=>'0'));
					$data[ $row['id'] ] = TRUE;
				}elseif($action == 'disable'){
					$this->ph->tag_model->save(array('status'=>0), array('id'=>$row['id'],'is_live'=>'0'));
					$data[ $row['id'] ] = TRUE;
				}elseif($action == 'publish'){
					$data[ $row['id'] ] = $this->_publish($row['id'],true);
				}
				
			}
			
			return $this->_api(array('action'=>$action,'data'=>$data,'records'=>$records));
		}else{
			return $this->_error(ERROR_NO_RECORD_LOADED,'No record has been loaded.');
		}
	}

	function _validate_slug($str){
		if(empty($str)) return TRUE;
		if(strlen($str) < 6) {

			$this->form_validation->set_message('_validate_slug', '%s is not a valid shorten name.');
			return FALSE;
		}
		$row = $this->ph->tag_model->read(array('slug'=>$str,'_select'=>'id'));
		if(!empty($row['id'])){
			$this->form_validation->set_message('_validate_slug', '%s is exist.');
			return FALSE;
		}
		return TRUE;
	}
	
	function _save($id=false){
		
		$vals = array();
		if($this->_restrict())return;
		
		$vals = array();
		$success = true;
		
		if(!$id) $id = $this->input->get('id');
		if(!$id) $id = $this->input->post('id');
		
		$record = $id ? $this->ph->tag_model->read(array('id'=>$id,'is_live'=>'0')) : NULL;
		if($id && (!isset($record['id']) || $record['id'] != $id)){
			return $this->_show_404('record_not_found');
		}
		$def_vals = $this->ph->tag_model->new_default_values ();
		foreach($def_vals as $key=>$defVal){
			$data[$key] = $defVal;
			if(isset($record[$key])) 
				$data[$key] = $record[$key];
			if($this->input->post($key)!==false) 
				$data[$key] = $this->input->post($key);
		}
		
		$success = true;

		$this->load->library('form_validation');
		$this->form_validation->set_rules('slug', ('lang:slug'), 'trim|_validate_slug');
		$this->form_validation->set_rules('title', ('lang:title'), 'trim|required|min_length[1]|max_length[40]');
		
		$success = $this->form_validation->run() != FALSE;

		if(!$success){
			$validate = array();
			foreach($data as $field => $val){
				$error = $this->form_validation->error($field,NULL,NULL);
				if(!empty($error))
					$validate['fields'][$field] = $error;
			}
			return $this->_error(ERROR_INVALID_DATA, '', 200, $validate);
		}
			
		if($success){

			if(empty($data['slug']))  $data['slug'] = $data['title'];
			$data['slug'] = seo_string($data['slug']);


			// locale data
			/*
			$all_loc_data = $this->input->post('loc');
			$default_locale = $this->input->post('default_locale');
			$locale = $this->lang->locale();
			$locale_fields = array('title','parameters','status');
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
			//*/

			$edit_info =$this->_get_editor_info();
			if(!$id){
				$exist_row = $this->ph->tag_model->find(array(
					'section'=>$this->ph->section,
					'slug'=>$data['slug'], 
					));
				if(!empty($exist_row['id'])){
					$data['slug'] = random_string('alnum', 16);
				}


				$exist_row = $this->ph->tag_model->find(array(
					'section'=>$this->ph->section,
					'_keyword'=>$data['title'], 
					'_keyword_fields'=>array('title'), 
					'_with_locale'=>$this->lang->locale(),
					));

				if(!empty($exist_row['id'])){
					$result['id'] = $exist_row['id'];
					$result['action'] = 'fetch';
					$result['is_insert'] = FALSE;
				}else{


					$data['owner_type'] = $edit_info['type'];
					$data['owner_id'] = $edit_info['id'];
					$data['section'] = $this->ph->section;
					$data['is_live'] = '0';
					$result = $this->ph->tag_model->save($data,NULL, $edit_info);
					$result['action'] = 'add';
				}
			}else{
				$exist_row = $this->ph->tag_model->find(array(
					'section'=>$this->ph->section,
					'slug'=>$data['slug'], 
					'id !'=>$id));

				if(!empty($exist_row['id'])){
					$data['slug'] = random_string('alnum', 16);
				}

				unset($data['id']);
				if($record['is_pushed'] > 0) $data['is_pushed'] = '2';
				$result = $this->ph->tag_model->save($data,array('id'=>$id,'is_live'=>'0'), $edit_info);
				$result['action'] = 'edit';
			}
			//$messages['sqls'][]= $this->db->last_query();
			
			/*
			if($this->ph->is_localized){
				// required model
				$this->load->model('text_locale_model');

				// data
				$all_loc_data = $this->input->post('loc');
				$all_curr_loc_rows = $this->text_locale_model->find(array(
					'ref_table'=>$this->ph->tag_model->table,
					'ref_id'=>$result['id'], 
					'is_live'=>0,
					'_field_based'=>'locale'));

				foreach($this->lang->get_available_locale_keys() as $loc_code){
					$loc_data = isset($all_loc_data[$loc_code]) ? $all_loc_data[$loc_code] : NULL;

					// skip it if no data for this locale
					if(empty($loc_data)) continue;
					$curr_loc_row = isset($all_curr_loc_rows[$loc_code]) ? $all_curr_loc_rows[$loc_code] : NULL;

					$sql_loc_data = array();
					foreach($locale_fields as $idx => $field_name){
						if(isset($loc_data[$field_name]))
							$sql_loc_data [$field_name] = $loc_data[$field_name];
					}

					if(empty($curr_loc_row['id'])){
						$sql_loc_data['ref_table'] = $this->ph->tag_model->table;
						$sql_loc_data['ref_id'] = $result['id'];
						$sql_loc_data['is_live'] = '0';
						$sql_loc_data['locale'] = $loc_code;
						$this->text_locale_model->save($sql_loc_data,NULL, $edit_info);

					}else{
						$this->text_locale_model->save($sql_loc_data, array('id'=>$curr_loc_row['id'],'is_live'=>'0','locale'=>$loc_code), $edit_info);
					}
				}
			}
			//*/
			cache_remove('ph/tag/'.$result['id']);
			cache_remove('ph/tag/'.$result['id'].'/*');
			
			$vals['id'] = $result['id'];
			$vals['method'] = $result['action'];

		}
		
		if($this->uri->is_extension('')){
			redirect('s/'.$this->ph->section.'/tag/'.$id);
			return;
		}
		return $this->_api($vals);
	}
	
	function _remove(){
		
		if($this->_restrict(null,false)){
			$this->_error(ERROR_INVALID_SESSION, 'Valid session required.');
			return;
		}
		
		$ids = $this->input->post('ids');
		$ids = explode(",", trim($ids));
		if(!is_array($ids)){
			$this->_error(ERROR_INVALID_DATA, 'Passed invalid value.');
			return;
		}
		
		$records = $this->ph->tag_model->find(array('id'=>$ids));
		if(is_array($records) && count($records)>0){
			foreach($records as $idx => $row){
				
				cache_remove('ph/tag/'.$row['id']);
				cache_remove('ph/tag/'.$row['id'].'/*');
				
				cache_remove('ph/tag/'.$row['_mapping']);
				cache_remove('ph/tag/'.$row['_mapping'].'/*');
			}

			$this->text_locale_model->delete(array('ref_table'=>$this->ph->tag_model->table, 'ref_id'=>$ids));

			$this->ph->post_model->remove_tags($ids);

			$this->ph->tag_model->remove(array('id'=> $ids));
			
			return $this->_api(array('data'=>$ids));
		}else{
			return $this->_error(ERROR_NO_RECORD_LOADED,'No record has been loaded.');
		}
		
	}
}
