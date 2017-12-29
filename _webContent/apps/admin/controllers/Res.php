<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Res extends MY_Controller
{
	public function widgets(){
		
		if( $this->_restrict()){
			return;
		}

		if(!$this->_is_ext('data') && !$this->_is_ext('asset')){
			print_r($this->uri->raw_extension());die();
			return $this->_show_404('extension_not_matched');
		}

		$path = implode("/", array_slice($this->uri->segments,1));
		
		return $this->_load_view($path);
	}


	protected function _load_view($path){

		$vals = array();

		$theme = $this->config->item('theme');
		
		if(!$this->uri->is_extension(array('','html'))){
			$req_ext = $this->uri->extension();
			$view_path = NULL;
			
			if(empty($view_path)){
				$view_path = 'themes/'.$theme.'/'.$path.'.'.$req_ext.EXT;
				if(!file_exists(APPPATH.'views/'.$view_path)){
					$view_path = NULL;
				}
			}
			if(empty($view_path)){
				$view_path = $path.'.'.$req_ext.EXT;
				if(!file_exists(APPPATH.'views/'.$view_path)){
					$view_path = NULL;
				}
			}
			if(empty($view_path)){
				$view_path = 'themes/'.$theme.'/'.$path.EXT;
				if(!file_exists(APPPATH.'views/'.$view_path)){
					$view_path = NULL;
				}
			}
			if(empty($view_path)){
				$view_path = $path.EXT;
				if(!file_exists(APPPATH.'views/'.$view_path)){
					$view_path = NULL;
				}
			}
			if(!empty($view_path)){
				if($this->uri->is_extension('js')){
					$str = $this->load->view($view_path,$vals, TRUE);

					$this->output->set_content_type('text/javascript');
					print JSMinPlus::minify($str);
					return;
				}
				if($this->uri->is_extension('css')){
					$this->output->set_content_type('text/stylesheet');
					$str = $this->load->view($view_path,$vals, TRUE);

					print $str;
					return;
				}
				elseif($this->uri->is_extension('xml','plist'))
					$this->output->set_content_type('text/xml');
				else
					$this->output->set_content_type('text/plain');
				return $this->load->view($view_path,$vals);
			}
		}


		if(empty($view_path)){
			$view_path = 'themes/'.$theme.'/'.$path.EXT;
			if(!file_exists(APPPATH.'views/'.$view_path)){
				$view_path = NULL;
			}
		}
		if(empty($view_path)){
			$view_path = $path.EXT;
			if(!file_exists(APPPATH.'views/'.$view_path)){
				$view_path = NULL;
			}
		}
		if(!empty($view_path)){
			$this->load->helper('render');
			return $this->load->view($view_path,$vals);
		}


		return $this->_show_404('file_not_found');
	}
}
