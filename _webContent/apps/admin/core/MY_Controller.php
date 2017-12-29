<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use Dynamotor\Controllers\Admin\CoreController;

class MY_Controller extends CoreController
{
	// to be extended
	

	public function __construct(){
		parent::__construct();

		$this->load->helper('data');

		if(!is_cli()){
			if($this->config->item('require_https')){
				if(!is_https() ){
					$secure_url = str_replace("http://","https://",site_url(uri_string()));
					redirect($secure_url.uri_query());
					die();
				}else{
					foreach(array('base_url','site_url') as $url_name){
						$url_val = $this->config->item($url_name);
						$url_val = str_replace("http://","https://",$url_val);
						$this->config->set_item($url_name, $url_val);
					}
				}
			}
		}
	}
}
