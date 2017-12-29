<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function acl_has_role ($role){
	$CI = &get_instance();
	if(!isset($CI->acl)){
		return FALSE;
	}
	return $CI->acl->has_role($role);
}

function acl_has_permission ($permission){
	$CI = &get_instance();
	if(!isset($CI->acl)){
		return FALSE;
	}
	return $CI->acl->has_permission($permission);
}

