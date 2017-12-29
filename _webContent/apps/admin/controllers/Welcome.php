<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends MY_Controller
{
	function index(){
		
		if($this->_restrict()) return;
		
		$vals = array();

		if($this->_is_ext('html'))
			return $this->_render('index', $vals);
		if($this->uri->is_extension('js')){
			$this->output->set_content_type('text/plain');
			return $this->load->view('index.js.php');
		}
		return $this->_show_404();
	}

	function page_not_found(){
		return $this->_show_404();
	}

}
 