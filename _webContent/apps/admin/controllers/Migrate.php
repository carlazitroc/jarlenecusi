<?php 

class Migrate extends MY_Controller
{
	public function __construct(){
		parent::__construct();
	}

	public function _log($str){
		log_message('debug',$str);
		echo $str ."<br />\r\n";
	}

	public function index(){

		// Stop here when no valid permission in this account
		if(!is_cli() && $this->_restrict()) return;



		$this->load->library('migration');
		if($this->migration->current() === FALSE ){
			show_error($this->migration->error_string());
		}else{
			echo 'done';
		}
	}

}