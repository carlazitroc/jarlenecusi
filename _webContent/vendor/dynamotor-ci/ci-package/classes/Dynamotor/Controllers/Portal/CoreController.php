<?php
namespace Dynamotor\Controllers\Portal;

use \Dynamotor\Core\HC_Controller;
use \CI_Controller;
use \CI_Model;
use \CI_Exceptions;

class CoreController extends HC_Controller {

	var $record_status_code       = 1;
	var $record_is_live			  = 1;
	

	var $is_refresh               = false;

	public function __construct() {
		parent::__construct();

		
		// pass 'refresh=yes' to rebuild all material.
		$this->is_refresh = $this->input->get('refresh') == 'yes' || $this->input->get('refresh') == 'true';

		$this->config->set_item('is_refresh', $this->is_refresh);
		$this->config->set_item('is_live', $this->record_is_live);

		$this->config->set_item('is_debug', $this->_is_debug());


		$this->config->set_item('preview_mode', false);
		if (defined('PREVIEW_MODE') && PREVIEW_MODE) {
			$this->config->set_item('preview_mode', true);

			// Feature: Authentication
			$this->load->config('admin');
			\Dynamotor\Helpers\AdminHelper::init(array(
				'auth_config'=>$this->config->item('admin_auth_config'),
				'acl_config'=>$this->config->item('admin_acl_config'),
			));

			if (!$this->admin_auth->is_login()) {
				return $this->_preview_denied();
			}

			$this->record_status_code = '1';
			$this->record_is_live = '0';
			$this->config->set_item('is_live', $this->record_is_live);
		}

	}

	protected function _preview_denied(){
		return $this->_permission_denied();;
	}

	protected function _init_meta($vals){
		// load db pref into sys config
		$locale = $this->lang->locale();
		$pref_fields = array('site_name','site_keywords','site_description','site_cover_id','sharing_title','sharing_description');
			
		foreach($pref_fields as $field){
			$val = $this->pref_model->locale_item($locale, $field);

			$this->config->set_item($field, $val);
		}

		if($this->config->item( 'site_name') != NULL){
			$this->asset->set_meta_property('og:site_name', $this->config->item( 'site_name'));
			$this->asset->set_meta_content('twitter:site_name', $this->config->item('site_name'));
		}

		if($this->config->item( 'site_keywords') != NULL){
			$this->asset->set_meta_content('keywords', $this->config->item( 'site_keywords'));
		}

		if($this->config->item( 'site_description') != NULL){
			$this->asset->set_meta_property('og:description', $this->config->item( 'site_description'));
			$this->asset->set_meta_content('twitter:description', $this->config->item( 'site_description'));
			$this->asset->set_meta_content('description', $this->config->item( 'site_description'));
		}

		if($this->config->item( 'site_cover_id') != NULL){
			$file_id = $this->config->item('site_cover_id');

			if(!empty($file_id)){
				$file = $this->file_model->read( array('id'=> $file_id) );
				if(is_array($file) && !empty($file['id'])){
					$this->config->set_item('site_cover',$file);
				}
			}

		}

		// Custom values
		$file = $this->config->item('site_cover');
		if(is_array($file) && !empty($file['id'])){
			$picture = $this->_picture_mapping($file,'file','source');

			if(!empty($picture['url'])){

				$image_url = $picture['url'];

				$this->asset->add_meta_property('og:image', $image_url);
				$this->asset->add_meta_property('og:image:width', $picture['width']);
				$this->asset->add_meta_property('og:image:height', $picture['height']);
				$this->asset->add_meta_content('twitter:image', $image_url);
			}
		}

		$this->asset->set_meta_property('og:url', base_url());
	}

	public function _render($view, $vals = false, $layout = false, $theme = false) {

		$this->_init_meta($vals);
		

		return parent::_render($view, $vals, $layout, $theme);
	}

	public function _picture_mapping($file_row,$group='file',$size='thumb',$subpath = false,$options = false,&$image_info=false){
		return $this->resource->picture_mapping($file_row, $group, $size, $subpath, $options, $image_info);
	}
}


