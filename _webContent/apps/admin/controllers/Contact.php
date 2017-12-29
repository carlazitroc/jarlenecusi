<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use Dynamotor\Controllers\Admin\CRUDController;
use Dynamotor\Helpers\PostHelper;
use Dynamotor\Modules\PostModule;

class Contact extends CRUDController
{
	var $model = 'Contact_model';
	var $view_scope = 'contact';
	var $view_type = 'post';
	var $tree = array('contact');
	// var $cache_prefix = 'menu';
	var $page_header = 'contact_header';
	// var $localized = true;
	var $endpoint_path_prefix = 'contact';
	var $sorting_fields = array('id', 'contact_name', 'email', 'mobile');
	var $keyword_fields = array('id', 'contact_name', 'email', 'mobile');
	var $add_enabled = true;
	
	protected function _search_options($options=false){
        $options = parent::_search_options($options);

        return $options;
    }
	
    public function _render($view, $vals = false, $layout = false, $theme = false){

        return parent::_render($view, $vals, $layout, $theme);
    }
}