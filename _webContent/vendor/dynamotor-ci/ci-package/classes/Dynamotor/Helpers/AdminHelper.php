<?php 

namespace Dynamotor\Helpers;

class AdminHelper{

	static function init($config){

		$CI = &get_instance();

		// Feature: Authentication
		$CI->admin_auth = new \Dynamotor\Modules\Auth\SimpleAuth($config['auth_config']);

		// Feature: Access Control List
		$CI->acl = new \Dynamotor\Modules\Auth\Acl($config['acl_config']);
	}
}