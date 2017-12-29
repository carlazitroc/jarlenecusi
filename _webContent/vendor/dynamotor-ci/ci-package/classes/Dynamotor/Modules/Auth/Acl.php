<?php

namespace Dynamotor\Modules\Auth{

	class Acl extends \Dynamotor\Core\HC_Module
	{
		var $user_id;			//Integer : Stores the ID of the current user
		var $user_perms = array();		//Array : Stores the permissions for the user
		var $user_roles = array();	//Array : Stores the roles of the current user
		var $user_role_ids = array();
		var $user_role_keys = array();
		var $user_has_global_permission = FALSE;
		
		var $tables_keys = array('users','roles','permissions','users_roles','uers_permissions','roles_permissions');
		var $fields = array(
			'user_id' => 'user_id',
			'permission_key'=>'key',
		);

		var $global_access_key = 'FULL_MANAGE';

		var $cache_key_prefix = 'default';
		var $cache_ttl = 14400 ; // 4 hour

		var $tables = array();
		/*
		var $tables = array(
			'users'=>'users',
			'roles'=>'roles',
			'users_roles'=>'users_roles',
			'users_permissions'=>'users_permissions',
			'permissions'=>'permissions',
			'roles_permissions'=>'roles_permissions',
		);
		*/
		public function __construct($config=array()) {
			
			if(isset($config['cache_key_prefix'])){
				$this->cache_key_prefix = $config['cache_key_prefix'];
			}
			
			if(isset($config['cache_ttl'])){
				$this->cache_ttl = $config['cache_ttl'];
			}
			
			if(isset($config['tables'])){

				if(is_array($config['tables'])){
					foreach($config['tables'] as $key => $name){
						$this->tables[ $key] = $name;
					}
				}
			}
			
			if(isset($config['fields'])){
				if(is_array($config['fields'])){
					foreach($config['fields'] as $key => $name){
						$this->fields[ $key] = $name;
					}
				}
			}
			
			log_message('debug','ACL Class initialized.');
			$this->load->helper('cache');
			
			if(isset( $config['user_id']) && !empty($config['user_id']))
			{
				$this->set_user_id( $config['user_id']);
			}
		}

		public function clear_cache($type = 'user', $record_id='*'){
			if(!empty($record_id)){
				cache_remove('acl/'.$this->cache_key_prefix.'/'.$type.'/'.$record_id.'/*');
				cache_remove('acl/'.$this->cache_key_prefix.'/'.$type.'/'.$record_id.'');
			}
		}
		
		public function set_user_id($new_user_id){
			$this->user_id = $new_user_id;

			$this->rebuild();
		}

		public function rebuild() {
			
			$this->user_roles = $this->get_user_roles($this->user_id);
			$this->user_role_ids = $this->get_user_role_ids($this->user_id);
			$this->user_role_keys = array();


			$cache_key = 'acl/'.$this->cache_key_prefix.'/user/'.$this->user_id.'/permissions';
			$this->user_perms = cache_get($cache_key);


			if(!empty($this->user_role_ids)){
				foreach($this->user_role_ids as $role_id){
					$this->user_role_keys[] = $this->get_role_sysname_from_roleid($role_id);
				}
			}

			if($this->user_perms == NULL){

				$this->user_perms = array();
				//first, get the rules for the user's role
				if (count($this->user_roles) > 0)
				{
					$_perms = $this->get_role_permissions($this->user_role_ids);
					if(!empty($_perms) && is_array($_perms))
						$this->user_perms = array_merge($this->user_perms,$_perms);
				}
				//then, get the individual user permissions

				$_perms = $this->get_user_permissions($this->user_id);
				if(!empty($_perms) && is_array($_perms))
					$this->user_perms = array_merge($this->user_perms,$_perms);

				cache_set($cache_key, $this->user_perms);
			}

			$this->user_has_global_permission = $this->_has_permission($this->global_access_key);
		}

		public function get_detail_from_permid($permission_id) {
			$this->db->select('id,name,key');
			$this->db->where('id',$permission_id);
			$sql = $this->db->get($this->table('permissions'),1);
			$data = $sql->row_array();
			
			return $data;
		}
		
		public function get_permkey_from_id($permission_id){
			
			$key_field = $this->field('permission_key');
			$this->db->select($key_field);
			$this->db->where('id',$permission_id);
			$sql = $this->db->get($this->table('permissions'));
			$data = $sql->row_array();
			
			if(isset($data['key']))
				return $data['key'];
			return null;
		}

		public function get_role_sysname_from_roleid($role_id) {
			$this->db->select('sys_name');
			$this->db->where('id',$role_id);
			$sql = $this->db->get($this->table('roles'));
			$data = $sql->row_array();
			
			if(isset($data['sys_name']))
				return $data['sys_name'];
			return null;
		}

		public function get_rolename_from_roleid($role_id) {
			$this->db->select('name');
			$this->db->where('id',$role_id);
			$sql = $this->db->get($this->table('roles'));
			$data = $sql->row_array();
			
			if(isset($data['name']))
				return $data['name'];
			return null;
		}

		public function get_user_roles($user_id) {
			//$strSQL = "SELECT * FROM `".DB_PREFIX."user_roles` WHERE `userID` = " . $this->user_id . " ORDER BY `created` ASC";
			$cache_key = 'acl/'.$this->cache_key_prefix.'/user/'.$user_id.'/roles';
			$resp = cache_get($cache_key);
			if($resp != NULL){
				return $resp;
			}

			$this->db->where(array(
				$this->field('user_id') => $user_id
			));
			
			$now = date('Y-m-d H:i:s');
			
			if($this->field('start_date')!=NULL){
				$this->db->where('('.$this->field('start_date').' <='.$this->db->escape($now).' OR '.$this->field('start_date') .' IS NULL OR '.$this->field('start_date') .' LIKE \'0000%\')');
			}
			
			if($this->field('end_date')!=NULL){
				$this->db->where('('.$this->field('end_date').' >'.$this->db->escape($now).' OR '.$this->field('end_date') .' IS NULL OR '.$this->field('end_date') .' LIKE \'0000%\')');
			}
			if($this->field('status')!=NULL){
				$this->db->where($this->field('status'),'1');
			}
			
			$this->db->order_by( $this->field('created') ,'asc');
			$query = $this->db->get($this->table('users_roles'));
			$data = $query->result_array();

			$resp = array();
			foreach( $data as $row )
			{
				$resp[] = $row;
			}
			cache_set($cache_key, $resp);
			return $resp;
		}

		public function get_user_role_ids($user_id) {
			//$strSQL = "SELECT * FROM `".DB_PREFIX."user_roles` WHERE `userID` = " . $this->user_id . " ORDER BY `created` ASC";
			$cache_key = 'acl/'.$this->cache_key_prefix.'/user/'.$user_id.'/role_ids';
			$resp = cache_get($cache_key);
			if($resp != NULL){
				return $resp;
			}

			$now = time();

			$this->db->where(array(
				$this->field('user_id') => $user_id
			));
			
			if($this->field('start_date')!=NULL){
				$this->db->where('('.$this->field('start_date').' <='.$this->db->escape($now).' OR '.$this->field('start_date') .' IS NULL OR '.$this->field('start_date') .' LIKE \'0000%\')');
			}
			
			if($this->field('end_date')!=NULL){
				$this->db->where('('.$this->field('end_date').' >'.$this->db->escape($now).' OR '.$this->field('end_date') .' IS NULL OR '.$this->field('end_date') .' LIKE \'0000%\')');
			}
			if($this->field('status')!=NULL){
				$this->db->where($this->field('status'),'1');
			}
			
			$this->db->order_by( $this->field('created') ,'asc');
			$query = $this->db->get($this->table('users_roles'));
			$data = $query->result_array();

			$resp = array();
			foreach( $data as $row )
			{
				$resp[] = $row[ $this->field('role_id')  ];
			}
			cache_set($cache_key, $resp);
			return $resp;
		}

		public function get_all_roles($format='ids') {
			$cache_key = 'acl/'.$this->cache_key_prefix.'/permission/all/'.$format;
			$resp = cache_get($cache_key);
			if($resp != NULL){
				return $resp;
			}
			$format = strtolower($format);
			//$strSQL = "SELECT * FROM `".DB_PREFIX."roles` ORDER BY `roleName` ASC";
			$this->db->order_by('name','asc');
			$sql = $this->db->get($this->table('roles'));
			$data = $sql->result_array();

			$resp = array();
			foreach( $data as $row )
			{
				if ($format == 'full')
				{
					$resp[] = array('id' => $row['id'], 'name' => $row['name'], );
				} else {
					$resp[] = $row['id'];
				}
			}
			cache_set($cache_key, $resp);
			return $resp;
		}

		public function get_all_permissions($format='ids') {
			$cache_key = 'acl/'.$this->cache_key_prefix.'/permission/all/'.$format;
			$resp = cache_get($cache_key);
			if($resp != NULL){
				return $resp;
			}

			$format = strtolower($format);
			//$strSQL = "SELECT * FROM `".DB_PREFIX."permissions` ORDER BY `permKey` ASC";

			$this->db->order_by($this->field('key'),'asc');
			$sql = $this->db->get($this->table('permissions'));
			$data = $sql->result_array();

			$resp = array();
			foreach( $data as $row )
			{
				if ($format == 'full')
				{
					$resp[$row['key']] = array('id' => $row['id'], 'name' => $row['name'], 'key' => $row['key']);
				} else {
					$resp[] = $row['id'];
				}
			}
			cache_set($cache_key, $resp);
			return $resp;
		}

		public function get_role_permissions($role) {
			$cache_key = 'acl/'.$this->cache_key_prefix.'/role/'.md5(json_encode($role)).'/permission';
			$perms = cache_get($cache_key);
			if($perms != NULL){
				return $perms;
			}
			
			$role_id_field = $this->field('role_id');
			if (is_array($role))
			{
				//$roleSQL = "SELECT * FROM `".DB_PREFIX."role_perms` WHERE `roleID` IN (" . implode(",",$role) . ") ORDER BY `ID` ASC";
				$this->db->where_in($role_id_field,$role);
			} else {
				//$roleSQL = "SELECT * FROM `".DB_PREFIX."role_perms` WHERE `roleID` = " . floatval($role) . " ORDER BY `ID` ASC";
				$this->db->where($role_id_field,$role);

			}
			
			$perm_id_field = $this->field('permission_id');
			$perm_key_field = $this->field('permission_key');
			$perm_name_field = $this->field('name');
			
			$this->db->order_by('id','asc');
			$sql = $this->db->get($this->table('roles_permissions')); //$this->db->select($roleSQL);
			$data = $sql->result_array();
			$perms = array();
			foreach( $data as $row )
			{
				$perm_key = $this->get_permkey_from_id($row[$perm_id_field]);
				if(empty($perm_key))
					continue;
				$perm_row = $this->get_detail_from_permid($row[$perm_id_field]);
				$perm_name = $perm_row[$perm_name_field];
				
				if ($row['value'] == '1') {
					$has_permission = true;
				} else {
					$has_permission = false;
				}
				
				$perms[$perm_key] = array(
					'perm' => $perm_key,
					'inheritted' => true,
					'value' => $has_permission,
					'name' =>$perm_name,
					'id' => $row[$perm_id_field]
					);
			}
			cache_set($cache_key, $perms);
			return $perms;
		}

		public function get_user_permissions($user_id) {
			$cache_key = 'acl/'.$this->cache_key_prefix.'/user/'.$user_id.'/permission';
			$perms = cache_get($cache_key);
			if($perms != NULL){
				return $perms;
			}
			$perm_id_field = $this->field('permission_id');
			$perm_key_field = $this->field('permission_key');
			$perm_name_field = $this->field('name');

			$this->db->where($this->field('user_id'),($user_id));
			$this->db->order_by($this->field('created'),'asc');
			$sql = $this->db->get($this->table('users_permissions'));
			$data = $sql->result_array();

			$perms = array();
			foreach( $data as $row )
			{
				$perm_key = $this->get_permkey_from_id($row[$perm_id_field]);
				if(empty($perm_key))
					continue;
				$perm_row = $this->get_detail_from_permid($row[$perm_id_field]);
				$perm_name = $perm_row[$perm_name_field];
				
				if ($row['value'] == '1') {
					$has_permission = true;
				} else {
					$has_permission = false;
				}
				$perms[$perm_key] = array(
					'perm' => $perm_key,
					'inheritted' => false,
					'value' => $has_permission,
					'name' => $perm_name,
					'id' => $row[$perm_id_field]
					);
			}

			cache_set($cache_key, $perms, $this->cache_ttl);
			return $perms;
		}

		public function has_role($role_id) {
			return in_array($role_id, $this->user_role_ids);
		}

		public function has_role_sysname($role_id) {
			return in_array($role_id, $this->user_role_keys);
		}

		public function has_global_permission()
		{
			return $this->user_has_global_permission;
		}

		/*
		@param String/Array<String> permssion key
		@return Boolean TRUE mean user has right of required permission
		 */
		public function has_permission($scope, $use_global_permission = TRUE){

			// if user has global access right, return TRUE directly
			if($this->has_global_permission() && $use_global_permission ) return TRUE;

			if(is_array($scope)){
				foreach($scope as $idx => $perm_key){
					if(!$this->_has_permission($perm_key)){
						return false;
					}
				}
				return true;
			}
			return $this->_has_permission($scope);
		}

		/*
		@param String permssion key
		@return Boolean TRUE mean user has right of required permission
		 */
		protected function _has_permission($scope) {
		
						
			$req_perm_key = strtoupper($scope);
			
			$keys = array_keys($this->user_perms);
			
			foreach($keys as $idx => $perm_key){
				if(strtoupper($perm_key) == $req_perm_key){
					if ($this->user_perms[$perm_key]['value'] == '1' || $this->user_perms[$perm_key]['value'] == true)
					{
						return true;
					} else {
						return false;
					}
				}
			}
			
			return false;
		}
		
		protected function table($name){
			if( isset($this->tables[$name]) && !empty($this->tables[$name]))
				return $this->tables[$name];
			return $name;
		}
		
		protected function field($name){
			if(isset($this->fields[$name]))
				return $this->fields[$name];
			return $name;
		}
	}
}