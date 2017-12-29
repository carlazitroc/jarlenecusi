<?php 
namespace Dynamotor\Controllers\Admin;

use \MY_Controller;

use \Box\Spout\Reader\ReaderFactory;
use \Box\Spout\Writer\WriterFactory;
use \Box\Spout\Common\Type;

class CRUDController extends MY_Controller
{
	// string
	var $model = 'target_model';
	var $tree = 'target';
	var $view_prefix = '';
	var $view_scope = 'target';
	var $view_type = 'post';
	var $page_header = 'target_heading';
	var $deep = -1;
	var $localized = FALSE;
	var $listing_fields = NULL;
	var $locale_fields = array('title','description','content','parameters','status');
	var $sorting_fields = array('id');
	var $export_fields = NULL;
	var $mapping_fields = NULL;
	var $editable_fields = NULL; // default all fields could be edited.
	var $editable_fields_details = array(); // default all fields could be edited.

	var $extra_fields = NULL;
	var $sorting_direction = 'desc';
	var $keyword_fields = array('id');
	var $cache_prefix = NULL;
	var $action = 'index';
	var $has_record_view = FALSE;

	var $listing_column_actions = NULL;
	var $listing_row_actions = NULL;
	
	var $add_enabled = TRUE;
	var $edit_enabled = TRUE;
	var $remove_enabled = TRUE;

	var $view_path_prefix = NULL;

	// view for default action
	var $view_paths = array(
		'index'=>'core/post_index',
		'add'=>'core/post_editor',
		'edit'=>'core/post_editor',
		'priority'=>'core/post_position',
	);

	/**
	 * Endpoint prefix for accessing this controller. Assign NULL to generate path by Router class
	 * @var null
	 */
	var $endpoint_path_prefix = NULL;

	/**
	 * $batch_actions Assign NULL to create default batch actions (remove, status_enable, status_disable, publish), or 
	 * @var array properties of batch actions
	 */
	var $batch_actions = NULL;

	/**
	 * Enabling staging control for this controller
	 * @var boolean
	 */
	var $staging_enabled = FALSE;

	/**
	 * Enabling export control for this controller
	 * @var boolean
	 */
	var $export_enabled = TRUE;

	/**
	 * Enalbing clone control for this controller
	 * @var boolean
	 */
	var $clone_enabled = FALSE;

	/**
	 * Enabling priority change control for this controller
	 * @var boolean
	 */
	var $priority_enabled = TRUE;

	/**
	 * Name the priority field name for updating number for this controller
	 * @var string
	 */
	var $priority_field = 'priority';


	// The permission key prefix for CRUD permission:
	// {PREFIX}RECORD_LISTING
	// {PREFIX}RECORD_VIEW
	// {PREFIX}RECORD_EDITOR
	// {PREFIX}RECORD_PROPERTY_CHANGE
	// {PREFIX}RECORD_SAVE
	// {PREFIX}RECORD_REMOVE
	// {PREFIX}RECORD_EXPORT
	// {PREFIX}RECORD_PUBLISH
	var $permission_key_prefix = NULL;

	var $default_page_limit = 50;

	var $_target_model = NULL;

	public function __construct(){
		parent::__construct();

		$this->_init();
	}

	protected function _init(){

		if($this->permission_key_prefix === NULL){
			$this->permission_key_prefix = strtoupper(str_replace('/','_',$this->router->fetch_directory().$this->router->fetch_class())).'_';
		}

		$this->_prepare_model();
		
		$this->_prepare_batch_actions();

		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('', '');


		if($this->priority_enabled && !isset($this->_target_model->fields_details[$this->priority_field]))
			$this->priority_enabled = false;
		

		$this->config->set_item('main_menu_selected', $this->tree);

		// if controller does not contain path prefix, combined by configured version
		if(empty($this->view_path_prefix)) 
			$this->view_path_prefix = $this->view_prefix.$this->view_scope.'/'.$this->view_type;

		// if controller does not contain path prefix, combined by configured version
		if(empty($this->endpoint_path_prefix)) 
			$this->endpoint_path_prefix = $this->view_prefix.$this->view_scope.'/'.$this->view_type;

		
					
		// Prepare mapping fields
		
		if(empty($this->mapping_fields) || is_array($this->listing_fields)){
			$this->mapping_fields = array();
			if(!empty($this->extra_fields) && is_array($this->extra_fields)){
				foreach($this->extra_fields as $field_name => $field_info){
					if(isset($this->extra_fields[$field_name]['listing']) && $this->extra_fields[$field_name]['listing']){
						$this->mapping_fields[] = $field_name;
					}
				}
			}
			if(is_array($this->_target_model->fields_details)){
				foreach($this->_target_model->fields_details as $field_name => $field_info){
					if(isset($field_info['listing']) && $field_info['listing']){
						$this->mapping_fields[] = $field_name;
					}
				}
			}
		}

		// Prepare listing fields
		if(empty($this->listing_fields))
			$this->listing_fields = $this->mapping_fields;
					
		// Prepare listing fields
		if(empty($this->export_fields))
			$this->export_fields = $this->mapping_fields;

		$this->editable_fields = array();
		if(!empty($this->_target_model->fields_details) && is_array($this->_target_model->fields_details)){
			foreach($this->_target_model->fields_details as $field_name => $field_info){
				if(!empty($field_info['control'])){
					$this->editable_fields[] = $field_name;
					$this->editable_fields_details[$field_name] = $field_info;
				}
			}
		}

		if(!empty($this->extra_fields) && is_array($this->extra_fields)){
			foreach($this->extra_fields as $field_name => $field_info){
				if(!empty($field_info['control'])){
					$this->editable_fields[] = $field_name;
					$this->editable_fields_details[$field_name] = $field_info;
				}
			}
		}

		if($this->listing_row_actions == NULL){
			if($this->edit_enabled){
				$this->listing_row_actions = array();
				$this->listing_row_actions [] = array(
					'action'=>'link',
					'href'=>site_url($this->endpoint_path_prefix).'/{id}/edit',
					'label'=>lang('button_edit'), 
				);
			}
		}
	}

	protected function _prepare_model(){


		// If no target model assigned, load model by name ($this->model)
		if(empty($this->_target_model)){

			if(is_string($this->model)){
				$model_name = $this->model;
				
				$this->load->model($model_name);
				$this->_target_model = $this->$model_name;
			}elseif(is_object($this->model)){
				$this->_target_model = $this->model;
			}
		}
		if($this->localized){
			$this->load->model('text_locale_model');
		}
	}

	protected function _prepare_batch_actions(){


		if($this->batch_actions == NULL){
			if($this->remove_enabled && $this->acl->has_permission($this->_get_permission_scopes('RECORD_REMOVE'))){
				$this->batch_actions[ 'remove'] = 'remove';
			}
			if($this->staging_enabled && $this->acl->has_permission($this->_get_permission_scopes('RECORD_PUBLISH'))){
				$this->batch_actions[ 'publish'] = 'publish';
			}
			if( is_array($this->_target_model->fields) && in_array( 'status', $this->_target_model->fields ) && $this->acl->has_permission($this->_get_permission_scopes('RECORD_PROPERTY_CHANGE'))){
				$this->batch_actions[ 'status-enable'] = 'status_enable';
				$this->batch_actions[ 'status-disable'] = 'status_disable';
			}
		}
	}

	protected function _get_default_vals($action = 'index', $vals = array()){
		$vals = parent::_get_default_vals($action, $vals);
		if($action == 'search'){

			$vals['paging'] = array();
			$vals['paging']['offset'] = 0;
			$vals['paging']['total'] = 0;
			$vals['paging']['limit'] = 0;
			$vals['paging']['page'] = 0;
			$vals['paging']['total_page'] = 0;
			$vals['data'] = array();
		}
		return $vals;
	}

	protected function _shorten_text($val, $length=200, $tail = '...', $encoding = 'UTF-8'){
		$size = mb_strlen($val, $encoding);
		if($size > $length){
			return mb_substr($val, 0, $length - strlen($tail), $encoding);
		}
		return $val;
	}

	protected function _available_locales(){
		return $this->lang->get_available_locale_keys();
	}
	
	protected function _mapping_row($raw_row, $action = 'default'){
		$row = array();
		if(empty($raw_row[$this->_target_model->pk_field])) return NULL;

		if(!empty($this->mapping_fields) && is_array($this->mapping_fields)){
			foreach($this->mapping_fields as $field_name){
					$row[$field_name] = data($field_name, $raw_row);
			}
		}

		if(isset($raw_row[$this->_target_model->pk_field]))
			$row['id'] = $raw_row[$this->_target_model->pk_field];

		if(isset($raw_row['slug'])){
			$row['slug'] = $raw_row['slug'];
		}

		if(isset($raw_row[$this->priority_field])){
			$row[$this->priority_field] = intval($raw_row[$this->priority_field]);
		}


		if(is_array($this->_target_model->fields_details)){
			foreach($this->_target_model->fields_details as $field_name => $field_info){
				if(!isset($row[ $field_name ]) && isset($raw_row[$field_name]) && (isset($field_info['listing']) && $field_info['listing'] || isset($field_info['export']) && $field_info['export'])){
					$row[ $field_name ] = $raw_row[$field_name];

					if(isset($field_info['control']) && $field_info['control'] == 'select' && isset($field_info['control_type']) && $field_info['control_type'] == 'file' && isset($field_info['is_image']) && $field_info['is_image']){
						$row['cover'] = $this->resource->picture_mapping( $raw_row['cover_id'],'file','thumbnail' );
					}
				}
			}
		}



		// get localized content
		if($this->localized){
			$loc_options = array(
				'_field_based'=>'locale',
				'_select'=>'id,is_live,cover_id,locale,title,content,description,parameters,status',
				'ref_table'=>$this->_target_model->table,
				'ref_id'=>$raw_row[$this->_target_model->pk_field],
				'locale'=>$this->_available_locales(),
			);
			if(isset($raw_row['is_live'])) $loc_options['is_live'] = $raw_row['is_live'];

			$row['loc'] = $this->text_locale_model->find($loc_options);
			$cur_locale = $this->lang->locale();

			if(isset($row['loc'][$cur_locale])){
				$loc_data = $row['loc'][$cur_locale];

				if(isset($row['title'])){
					$row['raw_title'] = $row['title'];
				}
				if(isset($loc_data['title'])){
					$row['loc_title'] = $loc_data['title'];
				}
				if(isset($row['description'])){
					$row['raw_description'] = $row['description'];
				}
				if(isset($loc_data['description'])){
					$row['loc_description'] = $loc_data['description'];
				}
				if(isset($row['content'])){
					$row['raw_content'] = $row['content'];
				}
				if(isset($loc_data['content'])){
					$row['loc_content'] = $loc_data['content'];
				}
				if(isset($row['parameters'])){
					$row['raw_parameters'] = $row['parameters'];
				}
				if(isset($loc_data['parameters'])){
					$row['loc_parameters'] = $loc_data['parameters'];
				}
			}
		}

		if(in_array('title',$this->_target_model->fields) || in_array('title',$this->_target_model->fields_details)){
			$row['title'] = isset($raw_row['title']) ? $raw_row['title'] : '';
			
			if(isset($raw_row['loc_title'])){
				$row['title'] = $raw_row['loc_title'];
			}
		}
		
		if(in_array('description',$this->_target_model->fields) || in_array('description',$this->_target_model->fields_details)){
			$row['description'] = isset($raw_row['description']) ? $raw_row['description'] : '';

			if(isset($raw_row['loc_description'])){
				$row['description'] = $raw_row['loc_description'];
			}
			$row['description_short'] = $this->_shorten_text($row['description']);
		}

		if(in_array('content',$this->_target_model->fields) || in_array('content',$this->_target_model->fields_details)){
			$row['content'] = isset($raw_row['content']) ? $raw_row['content'] : '';

			if(isset($raw_row['loc_content'])){
				$row['content'] = $raw_row['loc_content'];
			}

			$row['content_short'] = $this->_shorten_text(strip_tags($row['content']));
		}

		if($this->staging_enabled){

			$row['is_live'] = isset($raw_row['is_live']) ? $raw_row['is_live'] : '';
			$row['is_live_str'] = lang('is_live_'.$row['is_live']);
			$row['is_pushed'] = isset($raw_row['is_pushed']) ? $raw_row['is_pushed'] : '';
			$row['is_pushed_str'] = lang('is_pushed_'.$row['is_pushed']);
			$row['last_pushed'] = isset($raw_row['last_pushed']) ? $raw_row['last_pushed'] : '';
			$row['last_pushed_ts'] = isset($raw_row['last_pushed']) ? strtotime($raw_row['last_pushed']) : '';
		}
		if(isset($raw_row['_mapping']))
			$row['_mapping'] = $raw_row['_mapping'];
		
		if(isset($this->_target_model->fields_details['status'])){
			$row['status'] = isset($raw_row['status']) ? $raw_row['status'] : '';
		}
		
		if(!empty($row['status'])){
			$row['status_str'] = lang('status_'.$raw_row['status']);
		}
		if(in_array('publish_date',$this->_target_model->fields) || isset($this->_target_model->fields_details['publish_date'])){
			//$row['published'] = '';
			if(!empty($raw_row['publish_date'])){
				$row['published_ts'] = strtotime($raw_row['publish_date']);
			}
		}
		if(in_array('create_date',$this->_target_model->fields)){
			//$row['created'] = '';
			if(!empty($raw_row['create_date'])){
				$row['create_ts'] = strtotime($raw_row['create_date']);
			}
		}
		if(in_array('modify_date',$this->_target_model->fields)){
			//$row['modified'] = '';
			if(!empty($raw_row['modify_date'])){
				$row['modify_ts'] = strtotime($raw_row['modify_date']);
			}
		}

		return $row;
	}

	protected function _clear_cache($raw_row){
		if(!empty($this->cache_prefix) && !empty($raw_row[$this->_target_model->pk_field])){
			$cache_prefix = $this->cache_prefix;
			cache_remove($cache_prefix.'/'.$raw_row[$this->_target_model->pk_field].'/*');
			cache_remove($cache_prefix.'/'.$raw_row[$this->_target_model->pk_field]);
			if(!empty($raw_row['_mapping'])){
				cache_remove($cache_prefix.'/'.$raw_row['_mapping'].'/*');
				cache_remove($cache_prefix.'/'.$raw_row['_mapping']);
			}
		}
	}

	protected function _segment_at($offset=0){

		$deep = $this->deep;
		if($deep < 0){
			$deep = count(explode('/',$this->router->fetch_directory())) - 1;
			$this->deep = $deep;
		}

		$offset = $deep + 2 + $offset;
		return $this->uri->segment($offset);
	}

	protected function _prepare_routes(){


		$this->routes[''] = array('index');
		$this->routes['(index|selector)'] = array('index');
		$this->routes['(all|search|save|export)'] = array('$1');
		$this->routes['batch\/([^\/]+)'] = array('batch','$1');
		$this->routes['(remote|delete)'] = array('delete');
		$this->routes['add'] = array('editor');
		$this->routes['priority'] = array('priority_change');


		$id_pattern = $this->get_record_id_pattern();

		if(!isset($this->routes['('.$id_pattern.')\/edit']))
			$this->routes['('.$id_pattern.')\/edit'] = array('editor', '$1');

		if(!isset($this->routes['('.$id_pattern.')\/clone']))
			$this->routes['('.$id_pattern.')\/clone'] = array('clone_record', '$1');

		if(!isset($this->routes['('.$id_pattern.')\/view']))
			$this->routes['('.$id_pattern.')\/view'] = array('view', '$1');

		if(!isset($this->routes['('.$id_pattern.')\/?']))
			$this->routes['('.$id_pattern.')\/?'] = array('view', '$1');
	}
	
	public function _remap(){

		$this->_prepare_routes();

		if($this->perform_custom_route()){
			return;
		}

		// support previously version
		$s1 = $this->_segment_at(0);
		$s2 = $this->_segment_at(1);
		$s3 = $this->_segment_at(2);
		$s4 = $this->_segment_at(3);

		if( $this->_mapping_action($s1,$s2,$s3,$s4) ){
			return ;
		}

		if( $this->_is_record_id($s1) ){
			if( $this->_record_action($s1, $s2,$s3, $s4) ){
				return;
			}
		}

		log_message('error',__METHOD__.'@'.__LINE__.', cannot find the matched routes or actions:'.print_r(array('routes'=>$this->routes), true));
		return $this->_show_404('route_not_matched');
	}

	protected function _mapping_action($s1,$s2=NULL,$s3=NULL,$s4=NULL){
		
		return FALSE;
	}

	protected function get_record_id_pattern()
	{
		$id_pattern = '[0-9]+';
		if($this->_target_model->use_guid){
			$id_pattern = '[a-zA-Z0-9-]+';
		}
		return $id_pattern;
	}

	protected function _is_record_id($str){
		$id_pattern = $this->get_record_id_pattern();
		return preg_match('/^'.$id_pattern.'$/',$str);
	}

	protected function _record_action($id, $action=false, $action_id=NULL, $subaction=NULL){
		if(!$action || empty($action) ){
			$action = 'view';
		}

		if( $action == 'view'){
			$this->action = $action;
			$this->view($id);
			return TRUE;
		}

		return FALSE;
	}

	protected function _get_permission_scopes($keys = NULL){
		if($keys === NULL) return NULL;
		$_keys = array();

		if(is_array($keys)){
			foreach($keys as $key)
				$_keys[] = $this->permission_key_prefix.$key;
		}else{
				$_keys[] = $this->permission_key_prefix.$keys;
		}
		return $_keys;
	}
	
	public function index(){
		if( $this->_restrict($this->_get_permission_scopes('RECORD_LISTING'))){
			return;
		}

		$vals = $this->_get_default_vals('index');

		$view_path = $this->_get_render_view('index');
		
		if($this->uri->is_extension('js'))
			return $this->_render($view_path.'.js',$vals);
		
		$this->_render($view_path,$vals);
	}
	
	// default record's view
	public function view($record_id=false){
		if( $this->_restrict($this->_get_permission_scopes('RECORD_VIEW'))){
			return;
		}


		$method = $this->input->request_method();

		$record = NULL;
		if(!empty($record_id)){
			$query_opts = $this->_select_options('editor', array($this->_target_model->pk_field=>$record_id));
			$record = $this->_target_model->read($query_opts);
			if(empty($record[$this->_target_model->pk_field])){
				return $this->_show_404('record_not_found');
			}
			if($this->_is_ext('data')){
				return $this->_api( $this->_mapping_row($record, 'view') );
			}
		}

		if($this->has_record_view){

			$vals = $this->_get_default_vals('view');
			$vals['record'] = $record;
			$vals['record_id'] = $record_id;

			$view_path = $this->_get_render_view('view');

			if($this->uri->is_extension('js'))
				return $this->_render($view_path.'.js',$vals);
			
			return $this->_render($view_path,$vals);

		}else{
			return redirect($this->endpoint_path_prefix.'/'.$record_id.'/edit');
		}

		return $this->_show_404('no_view');
	}

	public function clone_record($record_id=false){
		if( $this->_restrict($this->_get_permission_scopes('RECORD_EDITOR'))){
			return;
		}

		if(!$this->clone_enabled){
			return $this->_show_404('clone_feature_not_enabled');
		}
		
		$options = array($this->_target_model->pk_field=>$record_id);

		// We copy un-published content only.
		if($this->staging_enabled){
			$options['is_live'] = '0';
		}

		$record = $this->_target_model->read($options);

		if(empty($record[$this->_target_model->pk_field])){
			return $this->_show_404();
		}

		$data = $record;
		unset($data[$this->_target_model->pk_field]);
		unset($data[$this->_target_model->create_date_field]);
		unset($data[$this->_target_model->create_by_field]);
		unset($data[$this->_target_model->create_by_id_field]);
		unset($data['status']);

		if(isset($record['status']) && $record['status'] == '1'){
			$record['status'] = '0';
		}

		$save_result = $this->_target_model->save($data);

		$new_record_id = $save_result['id'];
		$new_record = $this->_target_model->read(array($this->_target_model->pk_field=> $new_record_id));

		$vals = array();

		$this->_after_clone($record, $record_id, $new_record, $new_record_id, $vals );
		$this->_after_save('clone', $new_record_id, NULL, $data, NULL, $vals);

		if($this->_is_ext('data')){
			return $this->_api(array('id'=>$save_result['id']));
		}
		return redirect($this->endpoint_path_prefix.'/'.$save_result['id'].'/edit');
	}

	protected function _after_clone($record, $record_id, $new_record, $new_record_id, &$vals = NULL){


		if($this->localized){
			$loc_records = $this->text_locale_model->find(array('ref_table'=>$this->_target_model->table, 'is_live'=>0, 'ref_id'=>$record_id));
			foreach($loc_records as $loc_record){

				$new_loc_record = $loc_record;
				$new_loc_record['ref_id'] = $new_record_id;

				unset($new_loc_record[$this->text_locale_model->pk_field]);
				unset($new_loc_record[$this->text_locale_model->create_date_field]);
				unset($new_loc_record[$this->text_locale_model->create_by_field]);
				unset($new_loc_record[$this->text_locale_model->create_by_id_field]);
				unset($new_loc_record['status']);

				$this->text_locale_model->save($new_loc_record); 
			}
		}

	}
		
	public function editor($record_id=false){
		if( $this->_restrict($this->_get_permission_scopes('RECORD_EDITOR'))){
			return;
		}
		
		$record = NULL;

		$vals = NULL;

		$action = 'add';
		
		if(!empty($record_id)){
			$query_opts = $this->_select_options('editor', array($this->_target_model->pk_field=>$record_id));
			$record = $this->_target_model->read($query_opts);
			if(empty($record[$this->_target_model->pk_field])){
				return $this->_show_404('record_not_found');
			}
			$vals = $this->_get_default_vals('edit', compact('record','record_id'));
			$vals['id'] = $record_id;
			$vals['record_id'] = $record_id;
			$vals['record'] = $this->_mapping_row($record, 'editor');
			$action = 'edit';

			if(!isset($vals['data'])){
				$vals['data'] = $record;
			}
		}else{
			$vals = $this->_get_default_vals('add');
			$vals['id'] = NULL;
			$vals['record_id'] = NULL;
			$vals['record'] = NULL;
			if(!isset($vals['data']))
				$vals['data'] = $this->_target_model->new_default_values();
		}

		if($action == 'add' && !$this->add_enabled){
			return $this->_error(ERROR_MISSING_PERMISSION, ERROR_MISSING_PERMISSION_MSG);
		}

		if($action == 'edit' && !$this->edit_enabled){
			return $this->_error(ERROR_MISSING_PERMISSION, ERROR_MISSING_PERMISSION_MSG);
		}
		
		// localized content
		$vals['loc'] = array();

		if($this->localized){
			if(!empty($record)){
				
				$vals['loc'] = $this->text_locale_model->find(array('ref_table'=>$this->_target_model->table,'ref_id'=>$record_id,'is_live'=>'0','_field_based'=>'locale'));

				$loc_fields = $this->locale_fields;

				foreach($this->lang->get_available_locale_keys() as $loc_key ){
					if(!isset($vals['loc'][$loc_key])){
						$vals['loc'][$loc_key]=array();
						$vals['loc'][$loc_key]['locale'] = $loc_key;

						foreach($loc_fields as $loc_field){
							if(isset($record[ $loc_field ])){
								$vals['loc'][$loc_key][$loc_field] = $record[$loc_field];
							}
						}
					}
				}
			}
		}

		$view_path = $this->_get_render_view($action);
		
		if($this->uri->is_extension('js')){
			$this->output->set_content_type('text/javascript');
			return $this->_render($view_path.'.js',$vals);
		}
		
		$this->_render($view_path,$vals);
	}

	public function publish($id=false,$return=false){
		if( $this->_restrict($this->_get_permission_scopes('RECORD_PUBLISH'), !$return )){
			log_message('error','Cannot publish content: Invalid permission or session.');
			if(!$return) 
				return $this->_error(ERROR_INVALID_SESSION, 'Valid session required.');
			return array('error'=>array('code'=>ERROR_INVALID_SESSION, 'message'=>'Valid session required.'));;
		}

		if(is_array($id) && isset($id[ $this->_target_model->pk_field ])){
			$id = $id[ $this->_target_model->pk_field ];
		}
		
		if(empty($id)){
			if(!$return) 
				return $this->_error(ERROR_INVALID_DATA, 'Passed invalid value.');
			return array('error'=>array('code'=>ERROR_INVALID_DATA, 'message'=>'Passed invalid value.'));;
		}
		
		$old_row = $this->_target_model->read(array($this->_target_model->pk_field=>$id,'is_live'=>1));
		if(isset($old_row[$this->_target_model->pk_field])){

			$this->_clear_cache($old_row);
			$this->_target_model->delete(array($this->_target_model->pk_field=>$id,'is_live'=>1));
		}
		$new_row = $this->_target_model->read(array($this->_target_model->pk_field=>$id,'is_live'=>0));

		//log_message('debug','publish, new row:'.print_r($new_row,true));
		$new_row['is_live'] = 1;

		$result = $this->_target_model->save($new_row);

		if($this->localized){
			$all_curr_loc_rows = $this->text_locale_model->find(array(
				'is_live'=>'0',
				'ref_table'=>$this->_target_model->table, 
				'ref_id'=>$id,
				'_field_based'=>'locale'
				));

			$this->text_locale_model->delete(array(
				'is_live'=>'1',
				'ref_table'=>$this->_target_model->table, 
				'ref_id'=>$id
				));

			foreach($all_curr_loc_rows as $loc_code => $loc_data){
				$loc_data['is_live'] = '1';
				$this->text_locale_model->save($loc_data);
			}
		}

		if(isset($result['id'])){
			$this->_after_publish($result['id']);
		}
		
		if($return){
			
			if(isset($result['id'])){
				if($result['id'] == $id){
					return array('id'=>$id);
				}
				return array('id'=>$id,'error'=>array('code'=>ERROR_INVALID_DATA, 'message'=>'Invalid id after save live content.'));
			}
			return array('id'=>$id,'error'=>array('code'=>ERROR_RECORD_SAVE_ERROR, 'message'=>'Cannot save record in database.'));
		}
		
		if(isset($result['id'])){
			if($result['id'] == $id){
				return $this->_api(array('id'=>$id));
			}
			return $this->_error(ERROR_INVALID_DATA, 'Invalid id after save live content.');
		}
		return $this->_error(ERROR_RECORD_SAVE_ERROR, 'Cannot save record in database.');
	}

	protected function _after_publish($record_id){

		
		// update attributes
		$this->_target_model->save(array('is_pushed'=>1, 'last_pushed'=>time_to_date()),array($this->_target_model->pk_field=>$record_id));
	}


	protected function _search_options($options=false){

		if(!$options) $options = array();

		$direction = $this->sorting_direction;
		$sort = $this->sorting_fields[0];

		$start = (isset($options['_paging_offset'])) ? $options['_paging_offset'] : 0 ;
		$limit = (isset($options['_paging_limit'])) ? $options['_paging_limit'] : $this->default_page_limit;
		
		if($this->input->get_post('direction')!==NULL){
			$direction = $this->input->get_post('direction');
			if(strtolower($direction) != 'desc') $direction = 'asc';
		}
		if($this->input->get_post('sort')!==NULL){
			$sort = $this->input->get_post('sort');

			if(!in_array($sort,$this->sorting_fields)) $sort = $this->sorting_fields[0];
		}
		
		if($this->input->get_post('offset')!==NULL){
			$start = $this->input->get_post('offset');
		}
		if($this->input->get_post('limit')!==NULL){
			$limit = $this->input->get_post('limit');
		}
			
		if($this->input->get_post('page')!==NULL){
			$start = ($this->input->get_post('page') - 1 ) * $limit;
		}
		
		if($this->input->get_post('q')!=false && $this->input->get_post('q')!=''){
			$options['_keyword'] = $this->input->get_post('q');
			$options['_keyword_fields'] = $this->keyword_fields;
		}
		
		if($start<0) $start = 0;
		if($limit < 5) $limit = 10;

			
		if($this->input->get_post('id')!==NULL){
			$options[$this->_target_model->pk_field] = $this->input->get_post('id');
		}
		
		
		$options['_order_by'] = array();


		$sorts = $this->input->get_post('sorts');
		if(!empty($sorts) && is_array($sorts)){
			foreach($sorts as $key => $_direction){
				if(in_array($key, $this->sorting_fields))
					$options['_order_by'][ $key ] = $_direction;
			}
		}

		if(empty($options['_order_by'][$sort]))
			$options['_order_by'][$sort] = $direction;

		if( in_array('create_date',$this->_target_model->fields))
			$options['_order_by'][ $this->_target_model->table.'.create_date' ] = $direction;

		if($this->localized){
			$options['_with_text'] = $this->lang->locale();
		}

		if($this->staging_enabled){
			$options['is_live'] = '0';
		}

		$options['_paging_start'] = $start;
		$options['_paging_limit'] = $limit;

		return $options;
	}

	protected function _select_options($action='default',$options=array()){

		if($this->staging_enabled && $action != 'delete'){
			$options['is_live'] = '0';
		}

		return $options;
	}
	
	public function all(){
		if( $this->_restrict($this->_get_permission_scopes('RECORD_LISTING'))){
			return;
		}
		
		if(!$this->_is_ext('data')){ 
			return $this->_show_404('extension_not_allowed');
		}
		
		$options = $this->_search_options();
		$req_limit = $this->input->get_post('limit');

		$options['_paging_limit'] =  $this->_target_model->get_total($options,false);
		
		$result = $this->_target_model->find_paged($options['_paging_start'],$options['_paging_limit'],$options,false);
		
		$vals = $this->_get_default_vals('search' );
		
		$vals['data'] = array();

		if($this->input->get_post('na') == 'yes'){
			$vals['data'][] = array(
				data('na-value',$_REQUEST,'id')=>'',
				data('na-label',$_REQUEST,'title')=>lang('none'),
			);
		}
	
		if(isset($result['data'])){
			
			foreach($result['data'] as $idx => $row){
				$new_row = $this->_mapping_row($row, 'search');
				$new_row['_index'] = $result['index_from']  + $idx;
				$vals['data'][] = $new_row;
			}
			
		}

		if($this->_is_debug())
			$vals['queries'] = $this->db->queries;

		return $this->_api($vals);
	}
	
	public function search(){
		if( $this->_restrict($this->_get_permission_scopes('RECORD_LISTING'))){
			return;
		}
		
		if(!$this->_is_ext('data')){ 
			return $this->_show_404('extension_not_allowed');
		}
		
		$options = $this->_search_options();
		$req_limit = $this->input->get_post('limit');

		if(!empty($req_limit) && intval($req_limit) < 0){
			$options['_paging_limit'] =  $this->_target_model->get_total($options,false);
		}
		
		$result = $this->_target_model->find_paged($options['_paging_start'],$options['_paging_limit'],$options,false);
		
		$vals = $this->_get_default_vals('search' );
		
		$vals['paging']['offset'] = 0;
		$vals['paging']['total'] = 0;
		$vals['paging']['limit'] = 0;
		$vals['paging']['page'] = 0;
		$vals['paging']['total_page'] = 0;
		$vals['data'] = array();
		
		if(isset($result['data'])){
			
			foreach($result['data'] as $idx => $row){
				$new_row = $this->_mapping_row($row, 'search');
				$new_row['_index'] = $result['index_from']  + $idx;
				$vals['data'][] = $new_row;
			}
			
			$vals['paging']['offset'] = $result['index_from'];
			$vals['paging']['total'] = $result['total_record'];
			$vals['paging']['limit'] = $result['limit'];
			$vals['paging']['page'] = $result['page'];
			$vals['paging']['total_page'] = $result['total_page'];
		}

		if($this->_is_debug())
			$vals['queries'] = $this->db->queries;

		return $this->_api($vals);
	}

	public function export(){

		if(!$this->input->is_cli_request()){
			if( $this->_restrict($this->_get_permission_scopes('RECORD_LISTING'))){
				return;
			}
			if( $this->_restrict($this->_get_permission_scopes('RECORD_EXPORT'))){
				return;
			}
		}

		if( !$this->export_enabled){
			return $this->_show_404('action_not_enabled');
		}

		// Prevent stop unexceptedly
		set_time_limit(0);


		$vals = $this->_get_default_vals('export');

		$options = $this->_select_options('export');

		// Prepare fields
		$fields = $this->export_fields;

		$req_limit = $this->input->get_post('limit');

		// get the search result
		$options = $this->_search_options(array('_paging_limit' => $req_limit));

		if(empty($req_limit)){
			$options['_paging_limit'] =  $this->_target_model->get_total($options,false);
		}


		log_message('debug',__METHOD__.'@'.__LINE__.'# querying data');
		
		// Search result
		$result = $this->_target_model->find_paged($options['_paging_start'],$options['_paging_limit'],$options,false);
		
		$data = array();

		log_message('debug',__METHOD__.'@'.__LINE__.'# queried data');

		if(isset($result['data']) && is_array($result['data'])){
			foreach($result['data'] as $idx => $row){
				$new_row = $this->_mapping_row($row, 'export');
				$data[ ] = $new_row;
				if(empty($fields)) $fields = array_keys($new_row);
			}
		}

		log_message('debug',__METHOD__.'@'.__LINE__.'# prepared data');

		$cache_dir = $this->config->item('cache_path');
		$file_name = $this->view_scope . '_'.$this->view_type . '-'.date('YmdHis');



		$format = $this->input->get_post('format');

		if(in_array($format, array( NULL, '', 'xls','xlsx'))){



			$file_fullname = $file_name.'.xlsx';
			$writer = WriterFactory::create(Type::XLSX);


			$file_path = $cache_dir.$file_fullname;
			$writer->openToFile($file_path);

			$worksheet = $writer	->getCurrentSheet();

			$cells = [];

			log_message('debug',__METHOD__.'@'.__LINE__.'# prepared sheet');

			// Listing with column header
			foreach ($fields as $idx => $field_name) {

				// get the column id from column index
				$column_id = $this->_export_column_name( $idx );

				$label = NULL;
				if(empty($label) && !empty($this->_target_model->fields_details[ $field_name]['label']))
					$label = $this->_target_model->fields_details[ $field_name]['label'];

				if(empty($label) && !empty($this->extra_details[ $field_name]['label']))
					$label = $this->extra_details[ $field_name]['label'];

				if(empty($label))
					$label = lang('field_'. $field_name);

				if(isset($this->extra_fields[ $field_name]['label']))
					$label = $this->extra_fields[ $field_name]['label'];

				$cells[] = $label;
				//$sheet->setCellValue( $column_id . '1', $label);

			    //$sheet->getColumnDimension($column_id) ->setAutoSize(true);
			}

			$writer->addRow($cells);
			log_message('debug',__METHOD__.'@'.__LINE__.'# prepared header row');


			// Format data into cell
			$num_total = count($data);
			for($i = 0; $i< $num_total; $i++){
				$row = $data[$i];
				$cells = [];

				foreach ($fields as $idx => $field_name) {
					// get the column id from column index
					$column_id = $this->_export_column_name( $idx );

					$val = data($field_name,$row );

					$control = NULL;
					$control_type = NULL;
					$select_options = array();

					if(empty($control) && !empty($this->extra_details[ $field_name]['control'])){
						$control = $this->extra_details[ $field_name]['control'];
						if(!empty($this->extra_details[ $field_name]['control_type']))
							$control_type = $this->extra_details[ $field_name]['control_type'];
						if(!empty($this->extra_details[ $field_name]['options']))
							$select_options = $this->extra_details[ $field_name]['options'];
					}
					elseif(empty($control) && !empty($this->_target_model->fields_details[ $field_name]['control'])){
						$control = $this->_target_model->fields_details[ $field_name]['control'];
						if(!empty($this->_target_model->fields_details[ $field_name]['control_type']))
							$control_type = $this->_target_model->fields_details[ $field_name]['control_type'];
						if(!empty($this->_target_model->fields_details[ $field_name]['options']))
							$select_options = $this->_target_model->fields_details[ $field_name]['options'];
					}

					$val_str = $val;
					if(strtolower($control) == 'select' && !empty($select_options[ $val ])){
						$val_str = $select_options[ $val ];
					}
					if(strtolower($control) == 'bool'){
						if($val == '1') $val_str = lang('Yes');
						if($val == '0') $val_str = lang('No');
					}

					$cells [] = $val_str;
					//$sheet->setCellValue($column_id .($i+2), $val_str );
	
				}
				$writer->addRow($cells);
			}

			log_message('debug',__METHOD__.'@'.__LINE__.'# prepared data rows');



			/*
			if($format == 'xlsx'){
				$objWriter = new \PHPExcel_Writer_Excel2007($excel);
				$file_fullname = $file_name.'.xlsx';
			}else{
				$objWriter = new \PHPExcel_Writer_Excel5($excel);
				$file_fullname = $file_name.'.xls';
			}
			//*/
			
			$writer->close();


			log_message('debug',__METHOD__.'@'.__LINE__.'# excel file closed');

			if(!file_exists($file_path)){
				return $this->_error(ERROR_NO_RECORD_LOADED, lang(ERROR_NO_RECORD_LOADED_MSG));
			}

			if($this->input->is_cli_request()){

			}else{
				$this->load->helper('download');
				force_download($file_fullname, file_get_contents($file_path));
			}

			return;
		}

		if(in_array($format, array('','json'))){
			$this->_api(compact('data'));
			return;
		}
		return $this->_show_404();
	}

	public function batch($action=''){
		if($this->_restrict()){
			return ;
		}
		
		$ids = $this->input->get_post('ids');
		$ids = explode(",", trim($ids));
		if(!is_array($ids)){
			return $this->_error(ERROR_INVALID_DATA, lang(ERROR_INVALID_DATA_MSG));
		}
		
		$query_opts = $this->_select_options('batch', array($this->_target_model->pk_field=>$ids));

		$records = $this->_target_model->find($query_opts);
		if(is_array($records) && count($records)>0){
			foreach($records as $idx => $record){
				
				$result[ $record[$this->_target_model->pk_field] ] = $this->_batch_action($action, $record);
			}
			
			return $this->_api(array('result'=>$result,'data'=>$ids));
		}else{
			return $this->_error(ERROR_NO_RECORD_LOADED,lang(ERROR_NO_RECORD_LOADED_MSG));
		}
	}

	protected function _export_column_name($n)
	{
	    for($r = ""; $n >= 0; $n = intval($n / 26) - 1)
	        $r = chr($n%26 + 0x41) . $r;
	    return $r;
	}

	protected function _batch_action($action, $record){
		if(isset($this->batch_actions[ $action])){
			$method = $this->batch_actions[ $action];
			if(method_exists($this, $method)){
				$this->_clear_cache($record);
				return $this->$method($record, TRUE); // second parameter should be return for TRUE
			}else{
				return array('error'=>array('exception'=>'method_not_found'));
			}
		}else{
			return array('error'=>array('exception'=>'method_not_for_batch','available_methods'=> $this->batch_actions));
		}
		return FALSE;
	}

	public function status_enable($record){
		if( $this->_restrict($this->_get_permission_scopes('RECORD_PROPERTY_CHANGE'), FALSE)){
			return FALSE;
		}
		
		$query_opts = $this->_select_options('status', array($this->_target_model->pk_field=>$record[$this->_target_model->pk_field]));
		
		if($this->staging_enabled){
			if ($record['is_pushed'] == '1'){
				$this->_target_model->save(array('status'=>1,'is_pushed'=>'2'), $query_opts);
			}else{
				$this->_target_model->save(array('status'=>1), $query_opts);
			}
		}else{
			$this->_target_model->save(array('status'=>1), $query_opts);
		}

		return TRUE;
	}

	public function status_disable($record){
		if( $this->_restrict($this->_get_permission_scopes('RECORD_PROPERTY_CHANGE'), FALSE)){
			return FALSE;
		}
		
		$query_opts = $this->_select_options('status', array($this->_target_model->pk_field=>$record[$this->_target_model->pk_field]));
		if($this->staging_enabled){
			if ($record['is_pushed'] == '1'){
				$this->_target_model->save(array('status'=>0,'is_pushed'=>'2'), $query_opts);
			}else{
				$this->_target_model->save(array('status'=>0), $query_opts);
			}
		}else{
			$this->_target_model->save(array('status'=>0), $query_opts);
		}

		return TRUE;
	}
	
	public function save($id=false){
		
		if( $this->_restrict($this->_get_permission_scopes('RECORD_SAVE'))){
			return FALSE;
		}

		$editor_info =$this->_get_editor_info();
		
		$vals = $this->_get_default_vals('save');
		$success = true;
		
		if(!$id) $id = $this->input->get_post('id');
		
		$query_opts = $this->_select_options('save', array( $this->_target_model->pk_field =>$id));

		$record = $old_record = NULL;
		if(!empty($id)){
			$old_record = $record =$this->_target_model->read($query_opts) ;
			if(!isset($record[$this->_target_model->pk_field]) || $record[$this->_target_model->pk_field] != $id){
				return $this->_show_404('record_id_not_matched');
			}
		}

		$action = !$id ? 'add' : 'edit';
		$data = array();
		$loc_data = array();

		if($action == 'add' && !$this->add_enabled){
			return $this->_error(ERROR_MISSING_PERMISSION, ERROR_MISSING_PERMISSION_MSG);
		}

		if($action == 'edit' && !$this->edit_enabled){
			return $this->_error(ERROR_MISSING_PERMISSION, ERROR_MISSING_PERMISSION_MSG);
		}

		$success = $this->_before_save($action, $record, $data, $loc_data);
		
		if($success != FALSE)
			$success = TRUE;

		if($success){


			//$locale = $this->lang->locale();


			$validate = array();
			if(!$this->_validate_save($query_opts, $old_record, $data, $validate)) {
				log_message('error', __METHOD__.'@'.__LINE__.', validation error: '.print_r(compact('query_opts','validate','data'), true));


				return $this->_error(ERROR_RECORD_VALIDATION, ERROR_RECORD_VALIDATION_MSG,'200', compact('validate'));
			}

			if(!$id){
				if($this->staging_enabled)
					$data['is_live'] = '0';
				$result = $this->_target_model->save($data,NULL, $editor_info);
				$query_opts = $this->_select_options('save', array($this->_target_model->pk_field=>$result['id']));
				$id = $result['id'];
			}else{
				unset($data['is_live']);
				unset($data[$this->_target_model->pk_field]);
				unset($data[$this->_target_model->create_date_field]);
				unset($data[$this->_target_model->create_by_field]);
				unset($data[$this->_target_model->create_by_id_field]);
				$result = $this->_target_model->save($data,$query_opts, $editor_info);
			}

			$record = $this->_target_model->read($query_opts);


			$vals['id'] = $id;
			$vals['method'] =$action;
			$vals['data'] = array();
			foreach($this->editable_fields as $idx => $field_name)
				$vals['data'] [ $field_name ] = data($field_name, $record);

			$this->_after_save($action, $id, $old_record, $data, $loc_data, $vals);

			

			if(isset($this->db) && $this->_is_debug())
				$vals['queries'] = $this->db->queries;

			$this->_clear_cache($record);
		}
		
		if($this->uri->is_extension('')){
			redirect($this->view_prefix.$this->view_scope.'/'.$this->view_type.'/'.$id);
			return;
		}
		if($this->_is_ext('data')){
			return $this->_api($vals);
		}
		return $this->_show_404();
	}

	protected function _before_save($action, $record, &$data=false, &$loc_data=false ){

		$defvals = $this->_target_model->new_default_values();

		foreach($defvals as $field=>$val){

			// Ignore fields if the default values does not allowed for save action
			if(!empty($this->editable_fields)){
				if(!in_array($field, $this->editable_fields))
					continue;
			}
			$post_value = $this->input->post($field);

			if(!isset($data[$field]))
				$data[$field] = $val;
			if(isset($record[$field])) 
				$data[$field] = $record[$field];
			if($post_value !== NULL && $post_value !== FALSE) 
				$data[$field] = $post_value ;
		}

		$this->_before_save_localized($action, $record, $data, $loc_data);

		return TRUE;
	}

	protected function _before_save_localized($action, $record, &$data=false, &$loc_data=false )
	{
		if($this->localized){
			// prepare data for localized content
			$loc_data = $this->input->post('loc');
			$default_locale = $this->input->post('default_locale');
			$locale = $this->lang->locale();

			// only this fields will be handled for localized
			$locale_fields = $this->locale_fields;

			if(empty($default_locale)) $data['default_locale'] = $default_locale = $this->lang->locale();

			$_loc_data = isset($loc_data[$default_locale]) ? $loc_data[$default_locale] : NULL;

			$sql_loc_data = array();
			foreach($locale_fields as $idx => $field_name){
				if(isset($_loc_data[$field_name]))
					$data [$field_name] = $_loc_data[$field_name];
			}

			if(empty($loc_data)){
				foreach($this->lang->get_available_locale_keys() as $loc_code){
					$_loc_data_row = array();
					foreach($locale_fields as $idx => $field_name){
						if(isset( $data[$field_name]))
						$_loc_data_row[$field_name] = $data[$field_name];
					}
					$loc_data[$loc_code] = $_loc_data_row;
				}
			}
		}
	}

	protected function _validate_setup($action, $query_options, $old_record, $data )
	{

		$validate_fields = array();
		if(!empty($this->editable_fields_details) && is_array($this->editable_fields_details)){
			foreach($this->editable_fields_details as $field_name => $field_info){

				if(!empty($field_info['validate'])){
					$validate_fields [] = $field_name;
					$this->form_validation->set_rules($field_name, ('lang:field_'.$field_name), $field_info['validate']);
				}
			}	
		}

		return $validate_fields;
	}

	protected function _validate_save($query_options, $old_record, $data, &$validate = NULL){
		$success = TRUE;

		$validate_fields = $this->_validate_setup('save', $query_options, $old_record, $data);

		if(empty($validate_fields)) return TRUE;
		
		$this->form_validation->set_data($data);
		$success = $this->form_validation->run() != FALSE;

		foreach($validate_fields as $field_name){
			$validate['data'][ $field_name] = data($field_name, $data);

			$msg = $this->form_validation->error($field_name);
			if(!empty($msg)){
				$validate['fields'][ $field_name] = $msg;
			}
		}

		return $success;
	}

	protected function _after_save($action, $id, $old_record, $data, $loc_data, &$vals = false){
		$editor_info = $this->_get_editor_info();
		
		// localized part
		if($this->localized){

			// required helper and models
			$this->load->helper('localized');

			foreach($this->lang->get_available_locale_keys() as $loc_code){
				$_loc_data = isset($loc_data[$loc_code]) ? $loc_data[$loc_code] : NULL;

				// skip it if no data for this locale
				if(empty($_loc_data)) continue;
				localized_save($this->_target_model->table, $id, $loc_code, $_loc_data,'0',$editor_info);
			}
		}
		
		if($this->staging_enabled){
			if ($old_record['is_pushed'] == '1'){
				$this->_target_model->save(array('is_pushed'=>'2'), array($this->_target_model->pk_field=>$id));
			}
		}

	}

	public function remove($record){
		if( $this->_restrict($this->_get_permission_scopes('RECORD_REMOVE'), FALSE )){
			return ERROR_MISSING_PERMISSION;
		}

		if(!$this->remove_enabled){
			return ERROR_MISSING_PERMISSION_MSG;
		}
		

				
		$query_opts = $this->_select_options('delete', array($this->_target_model->pk_field=>$record[$this->_target_model->pk_field]));
		$this->_target_model->delete($query_opts);

		$this->_after_delete($record[$this->_target_model->pk_field], $record);

		$this->_clear_cache($record);

		return TRUE;
	}

	public function delete(){
		if( $this->_restrict($this->_get_permission_scopes('RECORD_REMOVE'))){
			return ;
		}

		if(!$this->remove_enabled){
			return $this->_error(ERROR_MISSING_PERMISSION, ERROR_MISSING_PERMISSION_MSG);
		}
		
		$ids = $this->input->get_post('ids');
		$ids = explode(",", trim($ids));
		if(!is_array($ids)){
			return $this->_error(ERROR_INVALID_DATA, lang(ERROR_INVALID_DATA_MSG));
		}
		
		$query_opts = $this->_select_options('delete', array($this->_target_model->pk_field=>$ids));

		$records = $this->_target_model->find($query_opts);
		if(is_array($records) && count($records)>0){
			foreach($records as $idx => $row){
				
				$query_opts = $this->_select_options('delete', array($this->_target_model->pk_field=>$row[$this->_target_model->pk_field]));
				$this->_target_model->delete($query_opts);

				$this->_after_delete($row[$this->_target_model->pk_field], $row);

				$this->_clear_cache($row);
			}
			
			
			return $this->_api(array('data'=>$ids));
		}else{
			return $this->_error(ERROR_NO_RECORD_LOADED, lang(ERROR_NO_RECORD_LOADED_MSG));
		}
	}

	public function priority_change() {
		if( $this->_restrict($this->_get_permission_scopes('RECORD_SAVE'))){
			return ;
		}

		if(strtoupper($this->input->request_method()) == 'POST'){

			$items = $this->input->post('items');

			// Dictionary, key-value based
			if(is_array($items)){
				foreach($items as $post_id=>$post_priority){
					$query_opts =  array($this->_target_model->pk_field=> $post_id);
					if($this->staging_enabled){
						$query_opts['is_live'] = '0';
					}
					$post_row = $this->_target_model->save( array($this->priority_field=>''.$post_priority), $query_opts);
				}
			}
			return $this->_api($post_row);
		}
		$this->_render('core/post_position');
	}

	protected function _after_delete($id, $record){

		$ref_queries = array('ref_scope'=>$this->_target_model->table,'ref_id'=>$record[$this->_target_model->pk_field]);
		if($this->localized){
			// remove multiple language record
			$this->text_locale_model-> delete($ref_queries);
		}
	}

	protected function _get_render_view($action = 'index'){

		// if controller does not contain path prefix, combined by configured version
		if(is_array($this->view_paths) && !empty($this->view_paths[$action])) 
			return $this->view_paths[$action];
		if($action == 'add' || $action == 'edit')
			return $this->view_path_prefix .'_editor';
		return $this->view_path_prefix .'_'.$action;
	}

	public function _render($view, $vals = false, $layout = false, $theme = false){

		if(!$vals) $vals = array();

		$this->form_validation->set_error_delimiters('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><strong>', '</strong></div>');

                   
                    
                    

		$vals['target_model'] = $this->_target_model;

		$vals['add_enabled'] = $this->add_enabled;
		$vals['edit_enabled'] = $this->edit_enabled;
		$vals['remove_enabled'] = $this->remove_enabled;
		$vals['staging_enabled'] = $this->staging_enabled;
		$vals['clone_enabled'] = $this->clone_enabled;
		$vals['batch_actions'] = $this->batch_actions;

		$vals['export_enabled'] = $this->export_enabled;

		$vals['editable_fields'] = $this->editable_fields;
		$vals['editable_fields_details'] = $this->editable_fields_details;
		$vals['mapping_fields'] = $this->mapping_fields;
		$vals['extra_fields'] = $this->extra_fields;
		$vals['export_fields'] = $this->export_fields;

		$vals['listing_fields'] = $this->listing_fields;
		$vals['sorting_fields'] = $this->sorting_fields;
		$vals['sorting_direction'] = $this->sorting_direction;
		$vals['keyword_fields'] = $this->keyword_fields;

		$vals['priority_enabled'] = $this->priority_enabled;
		$vals['priority_field'] = $this->priority_field;

		$vals['permission_key_prefix'] = $this->permission_key_prefix;
		$vals['listing_column_actions'] = $this->listing_column_actions;
		$vals['listing_row_actions'] = $this->listing_row_actions;

		// if passed data does not contain path prefix...
		if(empty($vals['view_path_prefix'])) 
			$vals['view_path_prefix'] = $this->view_path_prefix;

		// if controller does not contain path prefix, combined by configured version
		if(empty($vals['view_path_prefix']) || !is_string($vals['view_path_prefix'])) 
			$vals['view_path_prefix'] = $this->view_prefix.$this->view_scope.'/'.$this->view_type;

		// if passed data does not contain path prefix...
		if(empty($vals['endpoint_path_prefix'])) 
			$vals['endpoint_path_prefix'] = $this->endpoint_path_prefix;
                    
		// if controller does not contain path prefix, combined by configured version
		if(empty($vals['endpoint_path_prefix'])) 
			$vals['endpoint_path_prefix'] = $this->view_prefix.$this->view_scope.'/'.$this->view_type;
		$vals['endpoint_url_prefix'] = site_url($vals['endpoint_path_prefix']);
		
		$vals['view_prefix'] = $this->view_prefix;
		$vals['view_scope'] = $this->view_scope;
		$vals['view_type'] = $this->view_type;

		if(!isset($vals['page_header']))
			$vals['page_header'] = lang($this->page_header);

		//echo '<!-- '.$this->permission_key_prefix.'-->'."\r\n";
		return parent::_render($view, $vals, $layout, $theme);
	}
}
