<?php 

use Dynamotor\Controllers\Admin\CRUDController;

class Account extends CRUDController
{
	var $model = 'admin_account_model';
	var $view_scope = 'admin';
	var $view_type = 'account';
	var $tree = array('user','admin','account');
	var $cache_prefix = 'admin';
	var $page_header = 'admin_account_heading';
	var $localized = FALSE;
	var $endpoint_path_prefix = 'user/admin/account';
	var $sorting_fields = array('name','login_name','email','id','create_date','modify_date');
	var $keyword_fields = array('login_name','name','email');

	var $extra_fields = array(
		'roles'=>array(
			'label'=>'Roles',
			'control'=>'custom',
			'view'=>'user/admin/role_relationship_editor',
			'section'=>'role',
		),
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
		$this->load->model('admin_account_role_model');
		$this->load->model('admin_account_permission_model');
		$this->load->model('admin_permission_model');

		if(!	$this->_restrict(array('USER_ADMIN_ACCOUNT_RESET_PASSWORD'), FALSE)){
			$this->batch_actions['reset_password'] = 'reset_password';
		}

		$this->load->language('admin_auth');
	}

	protected function _record_action($id, $action=false, $action_id=NULL, $subaction=NULL){
		if($action == 'role' && $action_id == 'search'){
			$this->action = 'role_search';
			$this->_role_search($id);
			return TRUE;
		}

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

			$_result = $this->_role_search($vals['record_id'],true);
			$_vals['data']['roles'] = json_encode($_result['data']);;
		}

		return $_vals;
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
						$new_item_data['admin_id'] = $id;
						$this->admin_account_permission_model->save($new_item_data,array('id'=> $old_item_id, 'is_live'=> '0'));


						$vals['item_ids'][] = $old_item_id;
						$new_item_data['_type'] = 'update';
						$vals['item_data'][$old_item_id] = $new_item_data;

						if(!empty($_obj_id))
							$vals['item_objects'][ $_obj_id] = $old_item_id;

						//insert new item
					}else{
						$new_item_data['admin_id'] = $id;
						$item_result = $this->admin_account_permission_model->save($new_item_data);
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
				$this->admin_account_permission_model->delete(array('role_id'=> $id, 'id'=>$remove_ids));
		}catch(Exception $exp){

		}
		// Role
		$item_list = $this->_role_search($id,true);
		$counter = 0;
		$last_sequence = -1;

		$items_payload = $this->input->post('roles');
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
						'role_id'=>data('role_id',$item),
						'status'=>data('status',$item,'0'),
						'start_date'=>data('start_date',$item),
						'end_date'=>data('end_date',$item),
					);

					$old_item_id = data('id', $item);

						//update old item
					if(in_array($old_item_id, $old_item_ids)){
						$new_item_data['admin_id'] = $id;
						$this->admin_account_role_model->save($new_item_data,array('id'=> $old_item_id, 'is_live'=> '0'));


						$vals['item_ids'][] = $old_item_id;
						$new_item_data['_type'] = 'update';
						$vals['item_data'][$old_item_id] = $new_item_data;

						if(!empty($_obj_id))
							$vals['item_objects'][ $_obj_id] = $old_item_id;

						//insert new item
					}else{
						$new_item_data['admin_id'] = $id;
						$item_result = $this->admin_account_role_model->save($new_item_data);
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
				$this->admin_account_role_model->delete(array('role_id'=> $id, 'id'=>$remove_ids));
		}catch(Exception $exp){

		}
	}

	protected function _after_delete($id, $record){
		cache_remove('admin/*');
		$this->admin_account_model->delete(array('list_id'=> $id));
	}

	/* Batch Action: reset_password */
	public function reset_password($user){
		if($this->_restrict(array('USER_ADMIN_ACCOUNT_RESET_PASSWORD'), FALSE)){
			return FALSE;
		}


		$this->load->helper('string');
		$new_password = random_string('alnum',16);

		$this->load->model('admin_account_model');
		$this->admin_account_model->save( array(
			'login_pass'=> $this->admin_auth->encrypt($new_password),
		), array(
			'id'=> $user['id']
		));



		$email_vals = array(
			'login_name'=>$user['login_name'],
			'new_password'=>$new_password,
			'login_url'=>site_url(),
		);

		$subject = lang('admin_reset_password_subject');
		$message = $this->load->view('auth/reset_password_email', $email_vals, TRUE);
		$message_alt = strip_tags($message);

		$this->load->config('email');
		$this->load->library('email');

		$this->email->from($this->config->item('sender_from'), $this->config->item('sender_from_name'));
		$this->email->to($user['email']);
		$this->email->subject($subject);
		$this->email->message($message);
		$this->email->set_alt_message($message_alt);

		if(!$this->email->send()){
			return $this->_error(1204, 'cannot_send_reset_password_email');
		}

		return TRUE;
	}

	/* Relationship methods */
	protected function _permission_search($record_id=false, $return =false){
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

		
		$options['admin_id'] = $record_id;
		$options['_order_by'] = array('create_date'=>'asc');
		//$options['_with_text'] = $this->lang->locale();
		
		$result = $this->admin_account_permission_model->find($options);
		
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

	protected function _role_search($record_id=false, $return =false){
		if(!$return){
			if($this->_restrict()){
				return;
			}
			
			if(!$this->_is_ext('data')){ 
				return $this->_show_404();
			}
		}
		
		$record = NULL;
		if(!empty($record_id)){
			$record = $this->_target_model->read(array('id'=>$record_id));
		}
		
		
		if(empty($record['id'])){ 
			if($return) return NULL;
			return $this->_show_404();
		}


		$vals = array();

		
		$options['admin_id'] = $record_id;
		$options['_order_by'] = array('create_date'=>'asc');
		$options['_with_role'] = TRUE;
		//$options['_with_text'] = $this->lang->locale();
		
		$result = $this->admin_account_role_model->find($options);
		
		$data = array();
		//$this->params['paginator'] = $paginator;
		if(isset($result) && is_array($result)){
			
			foreach($result as $idx => $row){
				$data[] = $row;
			}
			
		}
		$vals['data'] = $data;

		if($return)
			return $vals;
		return $this->_api($vals);
	}
}