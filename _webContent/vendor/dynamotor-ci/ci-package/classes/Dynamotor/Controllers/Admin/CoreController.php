<?php
namespace Dynamotor\Controllers\Admin{

	class CoreController extends \Dynamotor\Core\HC_Controller
	{

		var $user_type = 'admin';

		protected function _get_editor_info(){
			if(isset($this->admin_auth))
				return array('_user_type'=>'admin', '_user_id'=>$this->admin_auth->get_id());
			return array('_user_type'=>'unknown','_user_id'=>NULL);
		}
		
		function __construct(){
			parent::__construct();
			
			// Feature: Language
			$sys_lang = $this->config->item('language');

			if( isset($this->session) && $this->session->userdata('language')!=NULL){
				$sys_lang =  $this->session->userdata('language');
			}
			
			$new_sys_lang = NULL;
			if($this->input->get('sys-lang')!=NULL){
				$new_sys_lang = $this->input->get('sys-lang');
			}
			
			if(!empty($new_sys_lang) && $new_sys_lang != $sys_lang){
				if($this->lang->is_supported_locale($new_sys_lang)){
					$sys_lang = $new_sys_lang;
					$this->config->set_item('language', $new_sys_lang);
					$this->session->set_userdata('language', $new_sys_lang);
				}
			}else{
				$this->config->set_item('language', $sys_lang);
			}
			
			$this -> load -> helper('language');

			$this->lang->load('common',$sys_lang);
			$this->lang->load('admin',$sys_lang);
			
			$this->init_auth();

			$this->init_menu();

		}

		public function _show_404($message = 'Un-specified 404 error.') {

			// reset render view's extension
			$this->uri->extension('');

			log_message('error','404 ERROR at '.uri_string().'. Message returned: '.$message.'');

			if (isset($this->admin_auth) && $this->admin_auth->is_login()) {
				return $this->_render('404');
			}

			return $this->_render('404', NULL, 'blank');
		}

		protected function init_auth(){



			$this->load->model('admin_account_model','auth_user_model');
			$this->load->helper('acl');

			\Dynamotor\Helpers\AdminHelper::init(array(
				'auth_config'=>$this->config->item('admin_auth_config'),
				'acl_config'=>$this->config->item('admin_acl_config'),
			));

			if ($this->admin_auth->is_login()) {
				
				$account = $this->auth_user_model->read(array('id' => $this->admin_auth->get_id()));

				if (isset($account['id'])) {

					$this->config->set_item('account_type', $this->user_type);
					$this->config->set_item('account', $account);
					$this->config->set_item('account_id', $account['id']);

					$this->acl->set_user_id($account['id']);

					if (isset($account['login_name'])) {
						$this->config->set_item('account_name', $account['login_name']);
					}

					if (isset($account['email'])) {
						$this->config->set_item('account_email', $account['email']);
					}
				}
			}
		}

		protected function init_menu(){

			$this->load->config('ph');
			$sections = $this->config->item('ph_sections');

			if (is_array($sections)) {
				$layout_menu = $this->config->item('layout_menu');
				foreach ($sections as $section_name => $section_detail) {

					if(isset($section_detail['admin_menu_enabled']) && $section_detail['admin_menu_enabled'] == false) continue;

					$counter = 0;

					$_category_enabled = isset($section_detail['category_enabled']) && $section_detail['category_enabled'] && (!isset($section_detail['category_admin_menu']) || $section_detail['category_admin_menu']);
					$_tag_enabled = isset($section_detail['tag_enabled']) && $section_detail['tag_enabled'] && (!isset($section_detail['tag_admin_menu']) || $section_detail['tag_admin_menu']);
					$_has_relation = $_category_enabled || $_tag_enabled;

					if($_has_relation){
						$subitems   = array();
						$subitems[] = array(
							'tree' => array($section_name, 'post'),
							'url'      => 's/' . $section_name . '/post',
							'text'     => 'post_heading',
							'icon'     => isset($section_detail['post_icon']) ? $section_detail['post_icon'] : 'fa fa-file',
							/*
							'subitems' => array(
								array(
									'tree' => array($section_name, 'post'),
									'url'  => 's/' . $section_name . '/post',
									'text'=>'button_catalog',
									'icon' => 'fa fa-filter',
								),
								array(
									'tree' => array($section_name, 'post'),
									'url'  => 's/' . $section_name . '/post/add',
									'text' => 'button_add',
									'icon' => 'fa fa-plus',
								),
							)
							//*/
						);
						if ($_category_enabled) {
							$counter++;
							$subitems[] = array(
								'tree' => array($section_name, 'category'),
								'url'      => 's/' . $section_name . '/category',
								'text'     => 'category_heading',
								'icon'     => isset($section_detail['category_icon']) ? $section_detail['category_icon'] : 'fa fa-chain',

							/*
								'subitems' => array(
									array(
										'url'  => 's/' . $section_name . '/category',
									'text'=>'button_catalog',
										'icon' => 'fa fa-filter',
									),
									array(
										'url'  => 's/' . $section_name . '/category/add',
										'text' => 'button_add',
										'icon' => 'fa fa-plus',
									),
								)
								//*/
							);
						}
						if ($_tag_enabled) {
							$counter++;
							$subitems[] = array(
								'tree' => array($section_name, 'tag'),
								'url'      => 's/' . $section_name . '/tag',
								'text'     => 'tag_heading',
								'icon'     => isset($section_detail['tag_icon']) ? $section_detail['tag_icon'] : 'fa fa-tags',
							/*
								'subitems' => array(
									array(
										'tree' => array($section_name, 'tag'),
										'url'  => 's/' . $section_name . '/tag',
									'text'=>'button_catalog',
										'icon' => 'fa fa-filter',
									),
									array(
										'tree' => array($section_name, 'tag'),
										'url'  => 's/' . $section_name . '/tag/add',
										'text' => 'button_add',
										'icon' => 'fa fa-plus',
									),
								)
							//*/
							);

						}
						$cfg = array(
							'tree' => array($section_name),
							'url'      => 's/' . $section_name,
							'text'     => $section_name . '_heading',
							'icon'     => isset($section_detail['icon']) ? $section_detail['icon'] : 'fa fa-file-text',
							'subitems' => $subitems,
						);
					}else{
						$cfg = array(
							'tree' => array($section_name),
							'url'      => 's/' . $section_name . '/post',
							'text'     => $section_name . '_heading',
							'icon'     => isset($section_detail['icon']) ? $section_detail['icon'] : 'fa fa-file-text',
							/*
							'subitems' => array(
								array(
									'tree' => array($section_name, 'post'),
									'url'  => 's/' . $section_name . '/post',
									'text'=>'button_catalog',
									'icon' => 'fa fa-filter',
								),
								array(
									'tree' => array($section_name, 'post'),
									'url'  => 's/' . $section_name . '/post/add',
									'text' => 'button_add',
									'icon' => 'fa fa-plus',
								),
							)
						//*/
						);
					}

					if (isset($section_detail['perms'])) {
						$cfg['perms'] = $section_detail['perms'];
					}
					$layout_menu[] = $cfg;
				}
				$this->config->set_item('layout_menu', $layout_menu);
			}
		}
		
		/*
		@method public
		@description Check the user
		@param Array List of permission key
		@param BOOL Should redirect to log-in page
		@return BOOL Return TRUE represent this session does have correct / enough permission to access
		*/
		public function _restrict($scope = NULL,$redirect=true){
			
			if($redirect && $this->admin_auth->restrict())
				return TRUE;
			if(!$redirect && !$this->admin_auth->is_login())
				return TRUE;
			
			if($this->admin_auth->is_login()){
				$this->acl->set_user_id($this->admin_auth->get_id());
			}
			
			if(!empty($scope)){
				if(!$this->acl->has_permission($scope)){
					if($redirect){
						$this->_permission_denied($scope);
					}
					return TRUE;
				}
			}
			return false;
		}
	
		public function _permission_denied($scopes=NULL){

			$this->load->helper('string');
			$code = '#ER-'.random_string('alnum',32);
			log_message('error',$code.' - permission denied at '.uri_string().' (scopes = '.print_r($scopes,true).')');

			if($this->_is_ext('data')){
				$data = NULL;
				if($this->_is_debug())
					$data = compact('scopes');
				return $this->_error(ERROR_MISSING_PERMISSION, ERROR_MISSING_PERMISSION_MSG, 401, $data);
			}

			$this->output->set_status_header(401);
			return $this->_render('auth/denied',compact('scopes'),'blank');
		}
		
		public function _render($view, $vals=false, $layout=false, $theme =false){


			if ($this->input->get('dialog') == 'yes' || $this->input->get('dialog') == 'true' || $this->input->get('dialog') == '1') {
				$vals['is_dialog'] = TRUE;
				$this->asset->add_data('body_css_class', 'dialog');
				if (!$layout) {
					$layout = 'dialog';
				}
			}

			return parent::_render($view, $vals, $layout, $theme);
		}

	}
}