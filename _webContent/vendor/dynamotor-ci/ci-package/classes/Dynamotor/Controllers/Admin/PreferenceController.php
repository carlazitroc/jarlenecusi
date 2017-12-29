<?php 
namespace Dynamotor\Controllers\Admin;

use \MY_Controller;

class PreferenceController extends MY_Controller
{

	var $scope = 'default';
	var $sections = NULL; // You can override this setting to get 
	var $config_key = NULL;
	var $config_filename = NULL;
	var $main_menu_selected = array('preference');
	var $page_header = 'preference_heading';
	var $cache_key_prefix = 'prefs';
	var $view_path_prefix = 'preference';
	var $view_paths = array(
		'index'=>'preference/index',
	);

	public function __construct(){
		parent::__construct();
		
		
		$this->load->model('pref_model');

		// initial sections data
		$this->get_editor_sections();

		// Preference_model is supporting `scope` for different application / scope
		$items = $this->pref_model->set_scope($this->scope);

		//*/
		if(!empty($this->main_menu_selected))
			$this->config->set_item('main_menu_selected',$this->main_menu_selected);
	}

	protected function get_editor_sections(){

		if(!empty($this->config_key)){
			if(!empty($this->config_filename)){
				$this->load->config($this->config_filename);
			}
			$this->sections = $this->config->item($this->config_key);
		}
		return $this->sections;
	}
	
	public function index(){
		
		if($this->_restrict('PREFERENCE_CHANGE')) return;
		
		$vals = array();
		
		$data = array();
		
		$sections = $this->sections;

		$loc_data = array();
		
		if(is_array( $sections)){
			foreach( $sections as $section => $section_info){
				$fields = $section_info['fields'];

				foreach($fields as $idx => $info){
					if(!isset($info['name'])) continue;
					$key = $info['name'];
					$value = NULL;

					$is_locaized = isset($info['localized']) && $info['localized'];

					// if that is mulitple language, for-loop each locale codes
					if($is_locaized){
						$value = array();

						$loc = $this->pref_model->item($key);

						foreach($this->lang->get_available_locale_keys() as $loc_code){
							
							if(!empty($info['default_value'])){
								$loc_data[$loc_code][$key] = $info['default_value'];
							}

							if(isset($loc[ $loc_code])){
								$loc_data[$loc_code][$key] = $loc[ $loc_code];
							}
						}
					}else{
						$value = $this->pref_model->item($key);

						if(empty($value) && !empty($info['default_value'])){
							$value = $info['default_value'];
						}
						$data[$key] = $value;
					}
				}
			}
		}

		$vals['editor_sections'] = $this->sections;
		$vals['loc'] = $loc_data;
		$vals['data'] = $data;
		
		if($this->_is_ext('html')){
			$vals['page_header'] =  lang($this->page_header);
			$vals['view_path_prefix'] =  $this->view_path_prefix;
			return $this->_render($this->view_paths['index'], $vals);
		}
		if($this->_is_ext('data'))
			return $this->_api($vals);
		return $this->_show_404();
	}
	
	public function save(){
		
		if($this->_restrict('PREFERENCE_CHANGE')) return;
		
		$vals = array();

		$results = array();
		
		$data = array();

		$post_data = array();
		
		$sections = $this->sections;
		$loc_post_value = $this->input->post('loc');
		
		if(is_array( $sections)){
			foreach( $sections as $section => $section_info){
				$fields = $section_info['fields'];
				
				foreach($fields as $idx => $info){
					if(!isset($info['name'])) continue;

					$key = $info['name'];
					$value = NULL;

					$is_locaized = isset($info['localized']) && $info['localized'];

					// If permission is required for a property, check it
					if(!empty($info['perms'])){
						if(!$this->acl->has_permission($info['perms'])){
							continue;
						}
					}


					if($is_locaized){
						$value = array();


						$loc = $this->pref_model->item($key);

						foreach($this->lang->get_available_locale_keys() as $loc_code){

							$value[$loc_code] = NULL;
							if(!empty($info['default_value']))
								$value[$loc_code] = $info['default_value'];

							if(isset($loc[ $loc_code])){
								$value[$loc_code] = $loc[ $loc_code];
							}
							//print_r(compact('loc_post_value','loc_code','key'));
							if(is_array($loc_post_value[$loc_code]) && isset($loc_post_value[$loc_code][$key])){
								$value[ $loc_code] = $loc_post_value[$loc_code][$key];
							}
						}
					}else{
						$post_value = $this->input->post($key);
						$post_data[$key] = $post_value;

						if(!empty($info['default_value']))
							$value = $info['default_value'];

						$_value = $this->pref_model->item($key);
						if($_value !== NULL){
							$value = $_value;
						}

						if($post_value !== NULL){
							$value = $post_value;
						}
					}

					$this->pref_model->set_item($key, $value);
					$this->after_save_item($key, $value);

					$data[$key] = $value;

					$results[$key] = array('field'=>$key, 'localized'=>$is_locaized,'value'=>$value,'post_value'=>$post_value);

				}
			}
		}
		
		$this->pref_model->rebuild_cache();
		
		$vals['data'] = $data;
		$vals['results'] = $results;

		// Clear cache
		cache_remove($this->cache_key_prefix.'/'.$this->scope.'/*');
		cache_remove($this->cache_key_prefix.'/'.$this->scope);

		$this->after_save();

		if($this->_is_ext('data'))
			return $this->_api($vals);
		
		$this->session->set_flashdata('save_result',$vals);
		redirect($this->view_path_prefix);
	}

	protected function after_save_item($key, $value){
	}

	protected function after_save(){
	}
}
