<?php

/** 
 * Simple Auth library for CodeIgniter
 * @author      leman 
 * @copyright   Copyright (c) 2015, LMSWork. 
 * @link        http://lmswork.com 
 * @since       Version 1.0 
 *  
 */

namespace Dynamotor\Modules\Auth{


class SimpleAuth extends \Dynamotor\Core\HC_Module
{
	var $table = 'users';
	var $field = array(
		'uid'=>'id',
		'loginkey'=>'username',
		'password'=>'password',
		);
	var $path = array(
		'login'=>'auth/signin'
		);
	var $views = array(
		'restrict'=>'auth/restrict'
		);
	var $default_params = array('required'=>'true');
	var $encrypt_config_key = NULL;
	var $_user = NULL;
	var $_user_id = NULL;
	var $CI = NULL;
	var $_session_keys = array(
		'id'=>'auth_id',
		'before_login'=>'before_login',
		);
		
	function __construct($config=false){

		parent::__construct();
		
		if(isset($config['encrypt_config_key'])){
			$this->encrypt_config_key = $config['encrypt_config_key'];
		}
		
		if(isset($config['table']))
			$this->table = $config['table'];
		
		if(isset($config['field']))
			$this->field = array_merge($this->field,$config['field']);
		
		if(isset($config['path']))
			$this->path = array_merge($this->path,$config['path']);
			
		if(isset($config['views']))
			$this->views = array_merge($this->views,$config['views']);
			
		if(isset($config['default_params']))
			$this->default_params = array_merge($this->default_params,$config['default_params']);
			
		if(isset($config['activiate']))
			$this->activiate($config['activiate']);
	}
	
	function activiate($key='auth'){
		
			if(!isset($this->session)){
				$this->load->library('session');
			}
			if(!isset($this->encrypt)){
				$this->load->library('encrypt');
			}

			$this->load->helper('url');
			$this->load->database();
		
		
		$this->_session_keys['id'] = $key.'_id';
		
		$this->set_id($this->get_session_user_id());
		
	}
	
	function set_id($val){
		$this->_user_id = $val;

		// erase previous data
		$this->_user = NULL;
		if(!empty($this->_user_id))
			$this->_user = $this->get_user_by_id($this->_user_id);
		return $this;
	}
	
	function get_id(){
		return $this->_user_id;
	}
	
	function get_user_data(){
		return $this->_user;
	}
	
	function set_session_user_id($val, $reload_data = TRUE){
		$this->session->set_userdata($this->_session_keys['id'],$val);
		if($reload_data)
			return $this->set_id($val);
		return $this;
	}
	
	function get_session_user_id(){
		return $this->session->userdata($this->_session_keys['id']);
	}
	
	function deactiviate()
	{
		$this->_user_id = NULL;
		$this->_user = NULL;
	}
	
	function encrypt($text= ''){
		$val = NULL;
		$this->load->library('encrypt');
		if(!empty($this->encrypt_config_key)){
			$val = $this->encrypt->encode($text,$this->config->item($this->encrypt_config_key));
		}else{
			$val = $this->encrypt->encode($text);
		}
		
		//log_message('debug','LMS_Auth/encrypt, text='.$text.', key='.$this->config->item($this->encrypt_config_key).', val='.$val);
		return $val;
	}
	
	function decrypt($text= ''){
		$val = NULL;
		$this->load->library('encrypt');

		if(!empty($this->encrypt_config_key)){
			$val = $this->encrypt->decode($text,$this->config->item($this->encrypt_config_key));
		}else{
			$val = $this->encrypt->decode($text);
		}
		
		//log_message('debug','LMS_Auth/decrypt, text='.$text.', key='.$this->config->item($this->encrypt_config_key).', val='.$val);
		return $val;
	}
	
	function restrict(){
		if(!$this->is_login()){
			
			$forward = site_url($this->uri->uri_string);
			
			if(isset($_SERVER['QUERY_STRING'])){
				if(strlen($_SERVER['QUERY_STRING'])>0)
					$forward.= '?'.$_SERVER['QUERY_STRING'];
			}
				
			$required_params = array('forward'=>rawurlencode($forward));
			$params = array_merge($this->default_params,$required_params);
			
			$url = $this->path['login'];
			if($params && count($params)>0) $url.='?'.http_build_query($params);
			
			// modified on 4 DEC 2011
			// by leman
			
			if($this->uri->extension() != ''){
				$this->load->view($this->views['restrict'].'.'.$this->uri->extension().EXT,array('login_uri'=>$url));
				return true;
			}
			
			// if other request, redirect to login page
			//redirect($url);
			$this->load->view($this->views['restrict'],array('login_uri'=>$url));
			return true;
		}
		return false;
	}
	
	function is_login(){
		$id = $this->get_id();
		/*
		if(!$id || empty($id))
			return false;
			//*/
		if(!$this->_user && !empty($id))
			$this->_user = $this->get_user_by_id($id);
		if(!$this->_user || !isset($this->_user[$this->field['uid']]))
			return false;
		return true;
	}
	
	
	function check_password($row,$pass){
		$decrypted_str = $this->decrypt($row[$this->field['password']]);
		return $decrypted_str == $pass;
	}
	
	function check_access($params=false,$pass=''){
		
		$row = $this->get_user($params);
		$validPass = $this->check_password($row,$pass);
		if(!isset($row) || empty($row) || empty($pass)){
			return 2;
		}elseif(!$validPass){
			return 3;
		}elseif($validPass){
			return 1;
		}
		return -1;
	}
	
	function login($params)
	{
		$user = $this->get_user($params);
		
		if(!empty($user[$this->field['uid']])){
			
			$this->_user_id = $user[$this->field['uid']];
			$this->_user = $user; 	
			$this->_after_login();
			
			return TRUE;	
		}
		return FALSE;
	}
	
	function logout(){
		$this->session->unset_userdata($this->_session_keys['id']);
		
		$this->_user_id = NULL;
		$this->_user = NULL;
		return TRUE;
	}
	
	function _after_login()
	{
		$this->set_session_user_id($this->_user_id, FALSE);
	}
	
	function get_user($params){
		if(!$this) return NULL;
		
		if(!is_array($params)){
			if(is_array($this->field['loginkey'])){
				foreach ($this->field['loginkey'] as $key => $field_name) {
					
					$this->db->where_or($field_name, $params);
				}
			}else{
				$this->db->where($this->field['loginkey'], $params);
			}
		}else{
			$this->db->where($params);
		}
		
		
		// we turn off cache always
		$this->db->cache_off();
		$query = $this->db->get($this->table);

		if(!$query)return NULL;
		if($query->num_rows() == 1){
			$row = $query->row_array();
			return $row;
		}
		return NULL;
	}
	
	function get_user_by_id($id){
		if(!$this) return NULL;
		
		$this->db->where($this->field['uid'],$id);
		$this->db->cache_off();
		$query = $this->db->get($this->table);

		if(!$query)return NULL;
		if($query->num_rows() == 1){
			return $query->row_array();
		}
		return NULL;
	}
}
}