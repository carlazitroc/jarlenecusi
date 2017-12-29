<?php

class Welcome extends MY_Controller
{
	var $section = 'page';
	var $seg_offset = 2;
	var $ph = NULL;

	function __construct(){
		parent::__construct();
		
		$this->load->helper('post');
		$this->seg_offset = 3;
		$this->section = $this->uri->segment($this->seg_offset-1);
		if(preg_match('/^[a-zA-Z]+$/', $this->section)){
			$this->ph = PostHelper::get_section($this->section);
		}else{
			$this->seg_offset = 4;
			$this->section = $this->uri->segment($this->seg_offset-1);
			if(preg_match('/^[a-zA-Z]+$/', $this->section)){
				$this->ph = PostHelper::get_section($this->section);
			}
		}

	}
	
	function _render($view, $vals, $layout=false, $theme=false){
		$vals['section'] = $this->section;
		$vals['ph'] = $this->ph;
		return parent::_render($view,$vals, $layout, $theme);
	}

	function _remap(){

		if(empty($this->ph))
			return $this->_error(ERROR_INVALID_DATA, 'Instance of PostHelper "'.$this->section.'" does not exist.');


		redirect('s/'.$this->ph->section.'/post');
	}
}
