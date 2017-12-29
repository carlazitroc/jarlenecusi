<?php 

use Dynamotor\Controllers\Admin\CRUDController;

class Permission extends CRUDController
{
	var $model = 'admin_permission_model';
	var $view_scope = 'admin';
	var $view_type = 'permission';
	var $tree = array('user','admin','permission');
	var $cache_prefix = 'admin';
	var $page_header = 'admin_permission_heading';
	var $localized = FALSE;
	var $endpoint_path_prefix = 'user/admin/permission';
	var $sorting_fields = array('name','key','id','create_date','modify_date');
	var $keyword_fields = array('name','key');

	var $staging_enabled = FALSE;

	protected function _init(){
		parent::_init();
		$this->load->model('admin_account_role_model');
		$this->load->model('admin_account_permission_model');
		$this->load->model('admin_permission_model');

		$this->load->language('admin_auth');
	}

}