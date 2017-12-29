<?php
namespace Dynamotor\Controllers\Portal;

use \MY_PH_Controller;
use \Dynamotor\Helpers\PostHelper;

// Default PH Controller with Remap Function
class CorePHController extends MY_PH_Controller
{
	
	public function _remap($method, $params = array()){

		$offset = $this->ph_segment_offset;
		//if(defined('PREVIEW_MODE')) $offset ++;

		$s1 = $this->uri->segment($offset);
		$s2 = $this->uri->segment($offset+1);
		$s3 = $this->uri->segment($offset+2);
		$s4 = $this->uri->segment($offset+3);

		$ph = $this->ph;
		$section = NULL;

		// if no PostHelper's section pre-configured from child controller, we detect it
		if(empty($ph)){
			// Filter unsupported extension
			if(!$this->_is_ext('data') && !$this->_is_ext('html')){
				return $this->_show_404('unmatched_extension');
			}
			
			if(preg_match("/^[\w\.\-\_]+$/",$s1) ){
				$section = $s1;
				$ph = PostHelper::get_section($section);
			}

			if(empty($ph)){
				log_message('error','CorePHController/_remap, Undefined section '.$section);
				if($this->_is_debug())
					return $this->_error(-1, 'Undefined section '. $section.' - '.print_r($this->uri->segments,true));
				return $this->_show_404('undefined_section');
			}
			$this->ph = $ph;
		}else{
			$section = $ph->section;
		}

		$req_ext = $this->uri->extension();

		if($this->_is_debug()){
			log_message('debug','CorePHController/_remap, Lookup for action: '.print_r(compact('s2','s3','req_ext'),true));
		}

		if((empty($s2) || $s2 == 'index')){
			if($this->_is_ext('html')){
				if(!$this->ph_is_undefined_section){
					return $this->_post_list($section,$s3,$s4);
				}
			}
		}elseif(($s2 == 'search') ){
			if($this->_is_ext('data')){
				if(!$this->ph_is_undefined_section){
					return $this->_post_list($section,$s3,$s4);
				}
			}
		}elseif($s2 == 'category'){
			if($s3 == 'tree' && $this->_is_ext('data')){
				return $this->_post_category_tree($section, $offset+3);
			}else{
				return $this->_post_category($section,$offset+2);
			}
		}elseif($s2 == 'tag'){
			return $this->_post_tag($section,$s3,$s4);
		}else{
			return $this->_post_view($section,$s2);
		}

		return $this->_show_404('ph_routes_unmatched');
	}

	protected function _before_remap(){
		return FALSE;
	}
}
