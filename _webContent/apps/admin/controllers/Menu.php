<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use Dynamotor\Controllers\Admin\CRUDController;
use Dynamotor\Helpers\PostHelper;
use Dynamotor\Modules\PostModule;

class Menu extends CRUDController
{
	var $model = 'menu_model';
	var $view_scope = 'menu';
	var $view_type = 'post';
	var $tree = array('menu');
	var $cache_prefix = 'menu';
	var $page_header = 'menu_heading';
	var $localized = true;
	var $endpoint_path_prefix = 'menu';
	var $sorting_fields = array('sys_name','title','id','start_date','end_date','create_date','modify_date');

	var $source_types = array();

	var $staging_enabled = TRUE;


	// view for default action
	var $view_paths = array(
		'index'=>'menu/post_index',
		'add'=>'menu/post_editor',
		'edit'=>'menu/post_editor',
	);

	function __construct(){
		parent::__construct();
		
		$this->batch_actions['publish'] = 'publish';

		$this->load->model('menu_item_model');

		$this->load->config('portal');
		$this->source_types = $this->config->item('menu_source_types');

		if(!is_array($this->source_types)) $this->source_types = array();
	}

	protected function _record_action($id, $action=false, $action_id=NULL, $subaction=NULL){
		if($action == 'item' && $action_id == 'search'){
			$this->action = 'item_search';
			$this->_item_search($id);
			return TRUE;
		}
		if($action == 'clone'){
			$this->action = 'clone';
			$this->clone_record($id);
			return TRUE;
		}

		return parent::_record_action($id, $action, $action_id, $subaction);
	}
	
	
	protected function _mapping_row($raw_row){
		$row = parent::_mapping_row($raw_row);
		$row['sys_name'] = $raw_row['sys_name'];
		$row['status'] = $raw_row['status'];
		$row['status_str'] = lang('status_'.$raw_row['status']);
		$row['is_pushed'] = $raw_row['is_pushed'];
		$row['is_pushed_str'] = lang('is_pushed_'.$raw_row['is_pushed']);
		return $row;
	}
	
	protected function _item_mapping_row($raw_row){
		$row = array();
		$row['id'] = $raw_row['id'];

		$row['type'] = $raw_row['type'];
		//$row['parameters'] = $raw_row['parameters'];

		$row['ref_table'] = $raw_row['ref_table'];
		$row['ref_id'] = $raw_row['ref_id'];
/*
		$row['status'] = $raw_row['status'];
		$row['status_str'] = lang('status_'.$raw_row['status']);
		$row['is_pushed'] = $raw_row['is_pushed'];
		$row['is_pushed_str'] = lang('is_pushed_'.$raw_row['is_pushed']);
//*/
		if($raw_row['type'] == 'link'){
			$row['ref_table_str'] = 'Link';
			$row['title'] = data('title', $raw_row['parameters']);
			$row['content'] = data('content', $raw_row['parameters']);
			$row['target'] = data('target', $raw_row['parameters']);
			$row['href'] = data('href', $raw_row['parameters']);
			$row['description'] = data('description', $raw_row['parameters']);
			$row['class'] = data('class', $raw_row['parameters']);
			$row['cover_id'] = data('cover_id', $raw_row['parameters']);
		}
		if($raw_row['type'] == 'menu'){
			$row['ref_table_str'] = 'Link';
			$row['title'] = data('title', $raw_row['parameters']);
			$row['content'] = data('content', $raw_row['parameters']);
			$row['description'] = data('description', $raw_row['parameters']);
			$row['class'] = data('class', $raw_row['parameters']);
			$row['cover_id'] = data('cover_id', $raw_row['parameters']);
		}

		if($raw_row['type'] == 'db'){

			$row['ref_table_str'] = '';
			if(!empty($this->source_types[ $raw_row['ref_table']]['label']))
				$row['ref_table_str'] = lang($this->source_types[ $raw_row['ref_table']]['label']);

			if(substr($raw_row['ref_table'],0,3) == 'ph_'){
				$pairs = explode('.',substr($raw_row['ref_table'],3),3);
				if(count($pairs)>=2){
					$type = $pairs[0];
					$section = $pairs[1];

					$ph = PostHelper::get_section($section );
					if(!empty($ph)){
						$model_name = $type.'_model';

						$options = array('id'=> $raw_row['ref_id'],'is_live'=>'0');
						if($ph->is_localized) $options['_with_locale'] = $this->lang->locale();
						$ref_row = $ph->$model_name->read($options);

						if(isset($ref_row['id'])){
							$row['href'] = web_url($section.'/'.($type == 'post' ? '' : $type.'/').$ref_row['_mapping']);
							$row['ref_mapping'] = $ref_row['_mapping'];
							$row['title'] = ($ph->is_localized) ? $ref_row['loc_title'] : $ref_row['title'];
							$row['description'] = ($ph->is_localized) ? $ref_row['loc_description'] : $ref_row['description'];
							
							if(!empty($ref_row['cover_id'])){
								$row['cover']['url'] = site_url('file/'.$ref_row['cover_id'].'/picture?width=50');
							}
						}
					}
				}
			}
			$row['custom_title'] = data('custom_title', $raw_row['parameters']);
			$row['custom_cover_id'] = data('custom_cover_id', $raw_row['parameters']);
			$row['target'] = data('target', $raw_row['parameters']);
		}

		if(empty($row['description'])){
			$row['description']= '';
		}

		return $row;
	}
	

	function _item_search($record_id=false, $is_live = '0',$return =false){
		if($this->_restrict()){
			return;
		}
		
		if(!$this->_is_ext('data')){ 
			return $this->_show_404();
		}
		
		$record = NULL;
		if(!empty($record_id)){
			$record = $this->_target_model->read(array('id'=>$record_id,'is_live'=>$is_live));
		}
		
		
		if(empty($record['id'])){ 
			return $this->_show_404();
		}


		$vals = array();
		
		$direction = 'asc';
		$sort = 'sequence';
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
		
		
		if($start<0) $start = 0;
		if($limit < 5) $limit = 10;
		elseif($limit%5 != 0) $limit = 10; 
		
		if(!in_array($sort,array('id','ref_table','ref_id','create_date','modify_date'))) $sort = 'sequence';
		if(strtolower($direction) != 'desc') $direction = 'asc';
		
		$options['menu_id'] = $record['id'];
		$options['is_live'] = $record['is_live'];
		$options['_order_by'] = array($sort=>$direction,'create_date'=>$direction);
		//$options['_with_text'] = $this->lang->locale();
		
		$result = $this->menu_item_model->find($options,false);
		
		$data = array();
		//$this->params['paginator'] = $paginator;
		if(isset($result) && is_array($result)){
			
			foreach($result as $idx => $row){
				$data[] = $this->_item_mapping_row($row);
			}
			
		}
		$vals['data'] = $data;

		if($return)
			return $vals;
		return $this->_api($vals);
	}

	protected function _after_clone($record, $record_id, $new_record, $new_record_id, &$vals = NULL){

		parent::_after_clone($record, $record_id, $new_record, $new_record_id, $vals);

		$items = $this->menu_item_model->find(array('menu_id'=>$record['id'],'is_live'=>'0','_order_by'=>array('sequence'=>'asc')) );
		foreach($items as $item){
			$new_item = $item;
			$new_item['menu_id'] = $new_record_id;

			unset($new_item['id']);
			unset($new_item['create_date']);
			unset($new_item['create_by']);
			unset($new_item['create_by_id']);
			unset($new_item['status']);

			$save_result = $this->menu_item_model->save($new_item);
		}

	}

	protected function _after_delete($id, $record){
		cache_remove('menu/*');
		$this->menu_item_model->delete(array('list_id'=> $id));
		parent::_after_delete($id, $record);
		
	}

	protected function _after_publish($record_id){
		cache_remove('menu/*');

		$items = $this->menu_item_model->find(array('is_live'=>'0','menu_id'=>$record_id,'_order_by'=>array('sequence'=>'asc')));
		$this->menu_item_model->delete(array('is_live'=>'1','menu_id'=>$record_id));

		if(is_array($items) && count($items)>0){
			foreach($items as $idx => $item_row){
				$item_row['is_live'] = '1';
				$this->menu_item_model->save($item_row);

				// update attributes
				$this->menu_item_model->save(array('is_pushed'=>'1','last_pushed'=>time_to_date()),array('id'=>$item_row['id']));
			}
		}
		parent::_after_publish($record_id);
	}

	protected function _after_save($action, $id, $old_record, $data, $loc_data, &$vals = false){
		parent::_after_save($action, $id, $old_record, $data, $loc_data, $vals);
		// remove old record
		// $this->menu_item_model->delete(array('list_id'=>$id,'is_live'=>'0'));

		cache_remove('menu/*');

		// required model
		$this->load->model('text_locale_model');


		$item_list = $this->_item_search($id,'0',true);
		$counter = 0;
		$last_sequence = -1;

		$items_payload = $this->input->post('items_payload');
		try{
			$items = json_decode($items_payload,true);

			$old_item_ids = array();
			if(is_array($item_list['data'])){
				foreach($item_list['data'] as $old_item){
					$old_item_ids[] = $old_item['id'];
				}
			}

			$vals['item_ids'] = array();
			$vals['item_objects'] = array();
			$vals['item_data'] = array();

			//$this->menu_item_model->delete(array('list_id'=> $id ,'is_live'=>'0'));
			if(is_array($items)){
				foreach($items as $idx => $item){

					$parameters = array();

					$_obj_id = data('_obj_id',$item);

					if( data('type', $item) == 'link'){
						$parameters['cover_id'] = data('cover_id',$item);
						$parameters['title'] = data('title',$item);
						$parameters['content'] = data('content',$item);
						$parameters['href'] = data('href',$item);
						$parameters['target'] = data('target',$item);
					}

					if( data('type', $item) == 'db'){
						$parameters['custom_title'] = data('custom_title',$item);
						$parameters['custom_cover_id'] = data('custom_cover_id',$item);
						$parameters['custom_url'] = data('custom_url',$item);
						$parameters['target'] = data('target',$item);
					}



					$new_item_data = array(
						'status'=>'1',
						'sequence'=>$idx,
						'type'=>data('type',$item),
						'parameters'=>$parameters,
						'ref_table'=> data('ref_table',$item),
						'ref_id'=>data('ref_id',$item),
					);

					$old_item_id = data('id', $item);

						//update old item
					if(in_array($old_item_id, $old_item_ids)){
						$new_item_data['menu_id'] = $id;
						$this->menu_item_model->save($new_item_data,array('id'=> $old_item_id, 'is_live'=> '0'));

						// remark changes
						$this->menu_item_model->save(array('is_pushed'=>'2'),array('id'=> $old_item_id, 'is_pushed >'=>'0'));

						$vals['item_ids'][] = $old_item_id;
						$new_item_data['_type'] = 'update';
						$vals['item_data'][$old_item_id] = $new_item_data;

						if(!empty($_obj_id))
							$vals['item_objects'][ $_obj_id] = $old_item_id;

						//insert new item
					}else{
						$new_item_data['menu_id'] = $id;
						$new_item_data['is_pushed'] = '0';
						$new_item_data['is_live'] = '0';
						$item_result = $this->menu_item_model->save($new_item_data);
						$new_item_data['_type'] = 'insert';
						
						$vals['item_ids'][] = $item_result['id'];
						$vals['item_data'][$item_result['id']] = $new_item_data;
						if(!empty($_obj_id))
							$vals['item_objects'][ $_obj_id] = $item_result['id'];
					}
				}
			}

			$remove_ids = array();
			if(is_array($old_item_ids) && count($old_item_ids)>0){
				foreach($old_item_ids as $old_item_id){
					if(!in_array( $old_item_id, $vals['item_ids']))
						$remove_ids[] = $old_item_id;
				}
			}

			$vals['item_remove_ids'] = $remove_ids;

			if(!empty($remove_ids))
				$this->menu_item_model->delete(array('menu_id'=> $id ,'is_live'=>'0', 'id'=>$remove_ids));
		}catch(Exception $exp){

		}

	}

	protected function _render($view, $vals = array(), $layout=false, $theme = false){

		$vals['source_types'] = $this->source_types;
		return parent::_render($view, $vals, $layout, $theme);
	}
}