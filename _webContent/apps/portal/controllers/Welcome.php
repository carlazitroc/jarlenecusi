<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends MY_Controller
{
	public function index(){

		if($this->pref_model->item('site_enabled') == '0'){
			return $this->_show_404('site_disabled');
		}

		if($this->_restrict()){
			return;
		}

		$vals = array();
			
		if($this->_is_ext('html'))
			return $this->_render('index',$vals);
		return $this->_show_404('extension_not_matched');
	}
}