<?php 

use Dynamotor\Controllers\Admin\CRUDController;

class Role extends CRUDController
{
	var $model = 'admin_role_model';
	var $view_scope = 'admin';
	var $view_type = 'role';
	var $tree = array('user','admin','role');
	var $cache_prefix = 'admin';
	var $page_header = 'admin_role_heading';
	var $localized = FALSE;
	var $endpoint_path_prefix = 'user/admin/role';
	var $sorting_fields = array('name','sys_name','id','create_date','modify_date');
	var $keyword_fields = array('name','sys_name');

	var $extra_fields = array(
		'permissions'=>array(
			'label'=>'Permissions',
			'control'=>'custom',
			'view'=>'user/admin/permission_relationship_editor',
			'section'=>'permission',
		),
	);

	var $staging_enabled = FALSE;

	protected function _init(){
		parent::_init();
		$this->load->model('admin_role_permission_model');
		$this->load->model('admin_permission_model');

		$this->load->language('admin_auth');
	}


	protected function _record_action($id, $action=false, $action_id=NULL, $subaction=NULL){


		if($action == 'permission' && $action_id == 'search'){
			$this->action = 'permission_search';
			$this->_permission_search($id);
			return TRUE;
		}
		return parent::_record_action($id, $action, $action_id, $subaction);
	}

	protected function _get_default_vals($action = 'index', $vals = array()){
		$_vals = parent::_get_default_vals($action, $vals);

		if($action == 'edit' && isset($vals['record'])){
			$_vals['data'] = $vals['record'];

			$_result = $this->_permission_search($vals['record_id'],true);
			$_vals['data']['permissions'] = json_encode($_result['data']);
		}

		return $_vals;
	}

	public function _permission_search($record_id=false, $return =false){
		if($this->_restrict()){
			if(!$return)
				return;
			return array('data'=>array());
		}
		
		if(!$this->_is_ext('data')){ 
			if(!$return)
				return $this->_show_404();
			return array('data'=>array());
		}
		
		$record = NULL;
		if(!empty($record_id)){
			$record = $this->_target_model->read(array('id'=>$record_id));
		}
		
		
		if(empty($record['id'])){ 
			if(!$return)
				return $this->_show_404();
			return array('data'=>array());
		}


		$vals = array();

		
		$options['_order_by'] = array('create_date'=>'asc');
		//$options['_with_text'] = $this->lang->locale();
		$options['role_id'] = $record_id;

		$result = $this->admin_role_permission_model->find( $options);
		
		$data = array();
		//$this->params['paginator'] = $paginator;
		if(isset($result) && is_array($result)){
			
			foreach($result as $idx => $row){
				$permission_row = $this->admin_permission_model->read(array('id'=> $row['permission_id']));

				$row['permission_name'] = $permission_row['name'];
				$row['sys_name'] = $permission_row['key'];
				$data[] = $row;
			}
			
		}
		$vals['data'] = $data;

		if($return)
			return $vals;
		return $this->_api($vals);
	}

	protected function _after_save($action, $id, $old_record, $data, $loc_data, &$vals = false){
		parent::_after_save($action, $id, $old_record, $data, $loc_data, $vals);
		// remove old record
		// $this->menu_item_model->delete(array('list_id'=>$id,'is_live'=>'0'));

		// Permission
		$item_list = $this->_permission_search($id,true);
		$counter = 0;
		$last_sequence = -1;

		$items_payload = $this->input->post('permissions');
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

					$new_item_data = array(
						'permission_id'=>data('permission_id',$item),
						'value'=>data('value',$item,'0'),
						'start_date'=>data('start_date',$item),
						'end_date'=>data('end_date',$item),
					);

					$old_item_id = data('id', $item);

						//update old item
					if(in_array($old_item_id, $old_item_ids)){
						$new_item_data['role_id'] = $id;
						$this->admin_role_permission_model->save($new_item_data,array('id'=> $old_item_id, 'is_live'=> '0'));


						$vals['item_ids'][] = $old_item_id;
						$new_item_data['_type'] = 'update';
						$vals['item_data'][$old_item_id] = $new_item_data;

						if(!empty($_obj_id))
							$vals['item_objects'][ $_obj_id] = $old_item_id;

						//insert new item
					}else{
						$new_item_data['role_id'] = $id;
						$item_result = $this->admin_role_permission_model->save($new_item_data);
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
				$this->admin_role_permission_model->delete(array('role_id'=> $id, 'id'=>$remove_ids));
		}catch(Exception $exp){

		}
	}
}