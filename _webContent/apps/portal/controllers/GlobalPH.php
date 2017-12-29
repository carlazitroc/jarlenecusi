<?php defined('BASEPATH') OR exit('No direct script access allowed');

use \Dynamotor\Controllers\Portal\CorePHController;
use \Dynamotor\Helpers\PostHelper;
use \Dynamotor\Modules\PostModule;

class GlobalPH extends CorePHController 
{

	public function __construct(){
		
		parent::__construct();

		$offset = 1;
		//if(defined('PREVIEW_MODE')) $offset ++;

		$s1 = $this->uri->segment($offset);
		$s2 = $this->uri->segment($offset+1);
		$s3 = $this->uri->segment($offset+2);
		$s4 = $this->uri->segment($offset+3);

		$is_undefined_section = FALSE;
		
		$ph = NULL;
		if(preg_match("/^[\w\.\-\_]+$/",$s1) ){
			$section = $s1;
			$ph = PostHelper::get_section($section);
		}


		//  lookup staticpage(or default section)
		if(empty($ph)){
			$this->is_undefined_section = TRUE;
			$section = $this->config->item('ph_section_default');
			if(!empty($section)){
				$ph = PostHelper::get_section($section);
				$this->ph_segment_offset = 0;
			}
		}

		// we assume it
		$this->ph = $ph;
	}

	public function _remap($method, $params = array()){

		if($this->pref_model->item('site_enabled') == '0'){
			return $this->_show_404('site_disabled');
		}
		
		$offset = $this->ph_segment_offset;
		//if(defined('PREVIEW_MODE')) $offset ++;

		$ary = NULL;
		$cur_path = uri_string();
		$_ext = EXT;
		if($this->request->is_support_format('html')){
			$ary = array(
				'themes/'.$this->config->item('theme').'/static/'.$cur_path.$_ext,
				'static/'.$cur_path.$_ext,
			);
		}
		if($this->request->is_support_format('data')){
			$_ext = '.'.$this->uri->extension().EXT;
			$ary = array(
				'themes/'.$this->config->item('theme').'/static/'.$cur_path.$_ext,
				'static/'.$cur_path.$_ext,
			);
		}

		if(!empty($ary)){
			foreach($ary as $path){
				if(file_exists(VIEWPATH.$path)){
					return $this->_render($path);
				}
			}
		}

		return parent::_remap($method, $params);
	}

	public function index(){
		return $this->_remap('index');
	}
}
