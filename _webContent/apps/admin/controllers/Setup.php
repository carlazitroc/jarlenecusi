<?php 

use Dynamotor\Controllers\Admin\CoreController;

class Setup extends CoreController
{

	protected function _init_system_setting(){
		//override all system setting for Setup class
	}
	protected function init_auth(){
		//override all system setting for Setup class
	}

	public function index(){

		if(!defined('PROJECT_DEBUG_KEY') || PROJECT_DEBUG_KEY == ''){
			return $this->_error(-1, 'You are not allow to use this tool.');
		}


		$data = array('login_name'=>'admin','name'=>'Administrator');
		$errors = array();
		$this->_render('setup/index', compact('data','errors') ,'blank');
	}

	public function form(){

		if(!defined('PROJECT_DEBUG_KEY') || PROJECT_DEBUG_KEY == ''){
			return $this->_error(-1, 'You are not allow to use this tool.');
		}


		$data = array('login_name'=>'admin','name'=>'Administrator');
		$errors = array();
		$this->_render('setup/form', compact('data','errors') ,'blank');
	}

	public function completed(){

		$this->load->model('pref_model');
		if($this->pref_model->item('setup') === NULL){
			redirect('setup');
			return;
		}
		$this->_render('setup/completed',array(),'blank');
	}


	public function process(){

		if(!defined('PROJECT_DEBUG_KEY') || PROJECT_DEBUG_KEY == ''){
			return $this->_error(-1, 'You are not allow to use this tool.');
		}


		$installation_key = $this->input->post('installation_key');

		$data = $this->input->post();
		$errors = array();

		if(!defined('PROJECT_DEBUG_KEY') || $installation_key != PROJECT_DEBUG_KEY){
			$errors['installation_key_invalid'] = true;
		}

		if(empty($data['name']) || strlen($data['name'])< 5){
			$errors['name_invalid'] = true;
		}

		if(empty($data['login_name']) || strlen($data['login_name'])< 5){
			$errors['login_name_invalid'] = true;
		}

		if(isset($data['login_pass_random']) || data('login_pass_random',$data) == 'yes'){


			$this->load->helper('string');
			$data['login_pass'] = random_string('alnum',16);

		}elseif(empty($data['login_pass']) || strlen($data['login_pass'])< 8){
			$errors['login_pass_invalid'] = true;
		}

		if(empty($data['email']) || strlen($data['email'])< 5){
			$errors['email_invalid'] = true;
		}

		$this->load->model('pref_model');
		if($this->pref_model->item('setup') !== NULL){
			$errors['setup_already'] = $this->pref_model->item('setup');
		}

		if(!empty($errors)){
			if($this->_is_ext('data'))
				return $this->_api(array('errors'=>$errors));
			return $this->_render('setup/index', array('data'=>$data,  'errors'=>$errors) ,'blank');
		}

		parent::init_auth();

		$this->load->model('admin_permission_model');
		$this->load->model('admin_account_model');
		$this->load->model('admin_account_role_model');
		$this->load->model('admin_account_permission_model');
		$this->load->model('admin_role_model');
		$this->load->model('admin_role_permission_model');

		$this->load->library('encryption');

		// Create user account
		$admin_row = $this->admin_account_model->read(array('login_name'=>$data['login_name']));
		if(empty($admin_row['id'])){
			$vals = array(
				'login_name'=>$data['login_name'],
				'login_pass'=>$this->admin_auth->encrypt($data['login_pass']),
				'name' => $data['name'],
				'email'=>$data['email'],
				'status'=>'1',
			);
			$result = $this->admin_account_model->save($vals);
			if(!isset($result['id'])){
				$errors['admin_account_not_created'] = true;
				if($this->_is_ext('data'))
					return $this->_api(array('errors'=>$errors));
				return $this->_render('setup/index', array('data'=>$data,  'errors'=>$errors) ,'blank');
			}
			$admin_row = $this->admin_account_model->read(array('id'=>$result['id']));
		}

		// Create root-level permission
		$permission_row = $this->admin_permission_model->read(array('key'=>'FULL_MANAGE'));
		if(empty($permission_row['id'])){
			$permission_result = $this->admin_permission_model->save(array('name'=>'Full Manage', 'description'=>'User granted this permission can access all area from this system.', 'key'=>'FULL_MANAGE'));
			$permission_row = $this->admin_permission_model->read(array('key'=>'FULL_MANAGE'));
		}
		if(empty($permission_row['id'])){
			return $this->_error('-1','Permission cannot create.');
		}

		// Create root-level user roles
		$role_row = $this->admin_role_model->read(array('sys_name'=>'administrators'));
		if(empty($role_row['id'])){
			$role_result = $this->admin_role_model->save(array('name'=>'System Administrators', 'sys_name'=>'administrators'));
			$role_row = $this->admin_role_model->read(array('sys_name'=>'administrators'));

		}
		if(empty($role_row['id'])){
			return $this->_error('-1','Role cannot create.');
		}

		// Grant new user into root-level user role
		$account_role_array = $this->admin_account_role_model->find(array('_field_based'=>'role_id','admin_id'=>$admin_row['id']));
		if(empty($account_role_array) || !in_array($role_row['id'], array_keys($account_role_array))){
			$this->admin_account_role_model->save(array('admin_id'=>$admin_row['id'], 'role_id'=>$role_row['id'], 'status'=>'1'));
		}

		// Grant root-level user role with root-level permission
		$role_perms_array = $this->admin_role_permission_model->find(array('_field_based'=>'permission_id','role_id'=>$role_row['id']));
		if(empty($role_perms_array) || !in_array($permission_row['id'], array_keys($role_perms_array))){
			$this->admin_role_permission_model->save(array('role_id'=> $role_row['id'], 'permission_id'=>$permission_row['id'],'status'=>'1'));
		}

		// remark system preference in database
		$this->pref_model->set_item('setup', time());


		if($this->_is_ext('data'))
			return $this->_api(array('done'=>TRUE,'data'=>$data));
		if($this->_is_ext('html'))
			return $this->_render('setup/completed', compact('data'),'blank');
		return $this->_show_404();
	}


	public function encryption(){
		$this->load->helper('form');

		if($this->_is_ext('data')){

			parent::init_auth();

			$val = $this->input->post('source');
			if(!empty($val)){
				if($this->input->post('action') == 'encode'){

					$this->_api(array('source'=>$val ,'answer'=>$this->admin_auth->encrypt($val)));
					return;
				}
				if($this->input->post('action') == 'decode'){

					$this->_api(array('source'=>$val ,'answer'=>$this->admin_auth->decrypt($val)));
					return;
				}
			}
			return;
		}



		if($this->_is_ext('html'))
			return $this->_render('setup/encryption', compact('data'),'blank');
		return $this->_show_404();
	}
	
	protected $check_logs = array();
	public function checker(){
		
		
		$this->check_logs[]= "Checker Tools";
		$this->check_logs[]= str_repeat("=",50)."";
		
		$done = true;
		
		// run checker tools, return TRUE equal to passed.
		if(!$this->checker_base())$done = false;
		if(!$this->checker_file_permision()) $done = false;
		if(!$this->checker_db()) $done = false;
		
		$this->check_logs[]= "";
		$this->check_logs[]= "All Complete? ";
		$this->check_logs[]= $done ? "YES":"NO";
		$this->check_logs[]= "";
		$this->check_logs[]= str_repeat("=",50)."";
		$this->check_logs[]= "ENDED"."";

		if($this->input->is_cli_request()){
			print implode("\r\n",$this->check_logs);
			return;
		}

		$vals = array('done'=>$done,  'message'=>$this->check_logs);
		if($this->input->get('check_setup_already')=='yes'){
			$vals['setup_enabled'] = $this->checker_setup_already();
		}
		if($this->_is_ext('data')){
			return $this->_api($vals);
		}

		header("Content-type: text/plain");
		print implode("\r\n",$this->check_logs);
	}

	protected function checker_setup_already(){
		$this->load->model('pref_model');
		$this->pref_model->rebuild_cache();

		$val = $this->pref_model->item('setup') ;

		if(!empty($val)){
			$this->check_logs[]= "> Setup already? Yes (".date("Y-m-d H:i:s",$val).").";
			return FALSE;
		}else{
			$this->check_logs[]= "> Setup already? No.";
			return TRUE;
		}
	}


	
	protected function checker_base(){
	
		$apps = $this->get_list_apps();
		
		if($apps === NULL){
			$this->check_logs[]= "ERROR: No application found under '". dirname(APP_DIR)."'.";
			$this->check_logs[]= str_repeat("-",50)."";
		}
		
		return count($apps)> 0;
	}
	

	
	protected function get_list_apps(){
			
		$apps = array();		
		$apps_path =  dirname(APP_DIR);
		
		if(!is_dir($apps_path) ) return NULL;
		
		$op = opendir($apps_path);
		while($file = readdir($op)){
			if(substr($file,0,1) =='.' || substr($file,0,1) =='_' || !is_dir($apps_path.DIRECTORY_SEPARATOR.$file)) continue;

			$apps[] = $file;
		}
		closedir($op);

		return $apps;
	}
	
	protected function checker_db(){
		$this->check_logs[]= "> Database Connection";
		$this->check_logs[] = "";

		
		$done = true;
		
		$root_path = SITE_DIR;
		$apps_path =  dirname(APP_DIR);
		
		$apps = $this->get_list_apps();
		
		$no_checking = false;
		
		if(is_array($apps)){
		foreach($apps as $app_name){
		
			
			$config_file_name = 'database.php';
			
			// Searching for the correct config file
			$config_file_dir = $apps_path.DS.$app_name.DS.'config';
			if(defined('ENVIRONMENT') && file_exists($config_file_dir.DS.ENVIRONMENT.DS.$config_file_name)){
				$config_file_dir .= DS. ENVIRONMENT;
			}
			$config_file_full_path = $config_file_dir.DS.$config_file_name;
			$config_file_path = str_replace($root_path, "", $config_file_full_path);
			
			//print $config_file_full_path."";
			
			if(!file_exists($config_file_full_path)){
				$this->check_logs[]= "WARNING: No database connection under application '$app_name'.";
				$no_checking = true;
				continue;
			}
			$db = array();
			include $config_file_full_path;
			
			if(empty($db)){
				$no_checking = true;
				continue;
			}
			
		
			foreach($db as $config_name => $db_config){
			
				$log_prefix= 'Connection \''.$app_name.'/'. $config_name .'\' Correct: ';
			
				$db_config['autoinit'] = false;
				$db_config['db_debug'] = true;
				
				$db_obj = $this->load->database($db_config,TRUE);
				$connected = $db_obj->initialize();
				
				
				if($connected){
					$this->check_logs[]= $log_prefix."YES.";
				}else{
					$this->check_logs[]= $log_prefix."NO.\r\n\tPlease check the setting at '$config_file_path'.";
				}
			}
		}
		}else{
			$no_checking = true;
		
		}
		
		if($no_checking){
			$this->check_logs[]= "WARNING: No database connection checked.";
		}
		
		$this->check_logs[]= "";
		$this->check_logs[]= "Complete? ";
		$this->check_logs[]= $done ? "YES":"NO";
		$this->check_logs[]= "";
		$this->check_logs[]= str_repeat("-",50)."";
		
		return $done;
	}
	
	protected function checker_file_permision(){
		$this->check_logs[]= "> File Permission Setting";
		$this->check_logs[] = "";
		$folders  = array(
			TMP_DIR=>FALSE,
			SESSION_DIR=>TRUE,
			CACHE_DIR=>TRUE,
			dirname(LOG_DIR)=>FALSE,
			realpath(PRV_DATA_DIR)=>FALSE,
			PUB_DIR=>FALSE,
		);
		$done = true;
		
		$root_path = SITE_DIR;
		
		//echo "Root Directory: '$root_path'\r\n";
		
		foreach($folders as $full_path => $skippable){
			$output_path = str_replace($root_path, "", $full_path);
			$log_prefix= "Path '$output_path' Correct: ";
			if(!file_exists($full_path) && !$skippable){
				$this->check_logs[]= $log_prefix."NO.\r\n\tSuggestion: Folder does not exist. Please create this folder with write permission for Web Server Software.";
			}elseif(is_file($full_path)){
				$this->check_logs[]= $log_prefix."NO.\r\n\tThis should be a folder (directory), not a file.";
			}elseif(!is_writable($full_path)){
				$this->check_logs[]= $log_prefix."NO.\r\n\tThis folder is not writable. Please grant enough permission to this folder for Web Server Software.";
			}else{
				$this->check_logs[]= $log_prefix."YES.";
			}
			
		}
		
		$this->check_logs[]= "";
		$this->check_logs[]= "Complete? ";
		$this->check_logs[]= $done ? "YES":"NO";
		$this->check_logs[]= "";
		$this->check_logs[]= str_repeat("-",50)."";
		
		return $done;
	}
	
	protected function checker_fail(){
		
	}
	
}