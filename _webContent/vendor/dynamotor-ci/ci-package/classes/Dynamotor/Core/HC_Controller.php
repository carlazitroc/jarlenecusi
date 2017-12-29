<?php 
namespace Dynamotor\Core;
use \CI_Controller;
use \CI_Model;
use \CI_Exceptions;
use \Dynamotor\Core\HC_Exception;

class HC_Controller extends CI_Controller
{

	protected $routes = array(
	);

	public $request;
	public $response;
	public $resource;
	public $asset;
	public $file;

	public function __construct(){

		if(!isset($_SERVER['REMOTE_ADDR']))
			$_SERVER['REMOTE_ADDR'] = '0.0.0.0';

		parent::__construct();

		$this->load->helper('render');

		$this->_init_modules();

		$this->_init_session();

		$this->_init_system_setting();

		$this->_on_detected_locale();
	}

	protected function singleton($classpath, $parameters = null, $is_shared = true, $alias = null){

		$first = func_get_arg(0);
		if(is_array($first) && !empty($first['class'])){
			$classpath = $first['class'];
			$parameters = isset($first['parameters']) ? $first['parameters']: null;
			$is_shared =  isset($first['shared']) ? $first['shared']: true;
			$alias =  isset($first['alias']) ? $first['alias']: null;
		}

		return $this->load->singleton($classpath, $parameters, $is_shared, $alias);
	}

	protected function _init_modules()
	{

		// load RequestModule for HttpRequest
		$this->request = $this->load->singleton(array('class'=>'Dynamotor.Modules.RequestModule','shared'=> false,'alias'=> 'request'));

		// load ResponseModule for HttpResponse
		$this->response = $this->load->singleton(array('class'=>'Dynamotor.Modules.ResponseModule','shared'=> false,'alias'=> 'response'));


		// load FileModule for HttpRequest
		$this->file = $this->load->singleton(array('class'=>'Dynamotor.Modules.FileModule','shared'=> false,'alias'=> 'file'));

		// load ResourceModule for handling file resource for output
		$this->resource = $this->load->singleton(array('class'=>'Dynamotor.Modules.ResourceModule','shared'=> false,'alias'=> 'resource'));

		// load AssetModule for html output;
		$this->asset = $this->load->singleton(array('class'=>'Dynamotor.Modules.AssetModule','shared'=> false,'alias'=> 'asset'));
		
	}

	protected function _init_system_setting()
	{

		$this -> load -> model('pref_model');

		// grap the system upload max size
		$sys_upload_max_size = ini_get('upload_max_filesize');
		if(strtoupper(substr($sys_upload_max_size, -1,1))=='M'){
			$sys_upload_max_size = intval(substr( $sys_upload_max_size,0, strlen($sys_upload_max_size)-1 ));
		}
		$this->config->set_item('sys_upload_max_size', $sys_upload_max_size);

		// get time zone setting
		$pref_timezone = $this->pref_model->item('timezone');

		if(!empty($pref_timezone)){
			$this->config->set_item('timezone', $pref_timezone);

			if(function_exists('date_default_timezone_set')){
				date_default_timezone_set($pref_timezone);	
			}
		}else{
			$pref_timezone = date_default_timezone_get();	
			$this->config->set_item('timezone', $pref_timezone);
		}
	}

	protected function _on_detected_locale()
	{
		if(isset($this->lang)){
			if($this->lang->is_requseted_locale_supported() != true){
				$this->_show_404('locale_not_supported');
			}
		}
	}

	protected function _get_default_vals($action='index', $vals= array())
	{
		//$vals ['is_debug'] = $this->_is_debug();
		return $vals;
	}

	protected function _init_session()
	{
		$this->load->library('Session');
	}

	// Should be called in _remap function
	protected function do_custom_route()
	{
		if($this->perform_custom_route())
			return;

		$this->_show_404('unmatched_route');
	}

	protected function perform_custom_route()
	{

		// get the current uri_string
		$uri_string = urldecode(uri_string());
		$uri_string = preg_replace('#\.'.$this->uri->extension().'$#','', $uri_string);


		$directory = $this->router->directory;
		$prefix_pattern = !empty($directory) ? str_replace("/", "\\/", $directory): "";
		$prefix_pattern.= $this->router->class."\\/";

		// stop here if route successfully
		if($this->do_route($uri_string, $this->routes, $prefix_pattern ))
			return true; 

		//exit;
		return false;
	}

	protected function do_route($uri_string, $routes = NULL, $route_prefix = NULL)
	{

		if( !is_array($routes)) return FALSE;

		foreach($routes as $route_pattern => $route_setting){
			if(!empty($route_pattern) && substr($route_pattern,-1,1) !='/') $route_pattern = $route_pattern.'\/';
			$pattern = $route_prefix.$route_pattern;

			if(substr($pattern,-1,1) == '/')
				$pattern.='?';
			$pattern = '#^'.$pattern.'$#';

			// find it by regexp
			$matched = preg_match($pattern, $uri_string, $route_matches );
			$is_matched = $matched > 0;
			$is_success = false;
			if($is_matched ){
				$_method = array_shift($route_setting);



				$route_replace_callback = function( $arg_matches) use($route_matches){
					//print '$arg_matches='.print_r($route_matches[ $arg_matches[1] ],true)."\r\n\r\n";
					return $route_matches[ $arg_matches[1] ];
				};

				$_method = preg_replace_callback('#\\$([0-9]+)#',$route_replace_callback , $_method);

				// if this class has this method, do this
				if(method_exists($this, $_method)){
					$_args = array();

					$arguments = $route_setting;
					$_output = NULL;

					// prepare argument list
					if(is_array($arguments)){
						foreach($arguments as $index => $argument_info){
							if(is_string($argument_info)){

								$_output = preg_replace_callback('#\\$([0-9]+)#',$route_replace_callback , $argument_info);
							}elseif(is_array($argument_info)){

								$_output = array();
								foreach($argument_info as $arg_key => $arg_val){
									$_output[ $arg_key] = preg_replace_callback('#\\$([0-9]+)#', $route_replace_callback, $arg_val);
								}
							}
							$_args[] = $_output;
						}
					}

					$is_success = true;

					if($this->input->get('debug')!='test-route'){
						call_user_func_array(array($this, $_method), $_args );

						return true;
					}
				}
			}
			if($this->input->get('debug')=='test-route'){
				$LANG = $this->lang;
				var_dump(compact('pattern','is_matched','is_success','route_matches','route_setting', '_method','_args', '_output'));
			}
		}


		if($this->input->get('debug')=='test-route'){
			return true;
		}

		return false;
	}

	// return FALSE which is Allowed.
	public function _restrict($scope = NULL,$redirect=true){
		return FALSE;
	}
	
	public function _permission_denied($scope=NULL){
		return $this->_error(ERROR_MISSING_PERMISSION, ERROR_MISSING_PERMISSION_MSG, 401, compact('scope'));
	}
	
	public function _is_debug(){
		return $this->request->is_debug();
	}

	public function _is_ext($group='html'){
		return $this->request->is_support_format($group);
	}

	public function _is_extension($group='html'){
		return $this->request->is_support_format($group);
	}

	public function _system_error($code, $message = 'Unknown system error.', $status=500, $data = NULL){
		return $this->response->system_error($code, $message, $status, $data);
	}
	
	public function _api($vals, $default_format = 'json') {

		return $this->response->output($vals, $default_format); 
	}
	
	public function _error($code, $message = '', $status = 500, $data=NULL) {
		return $this->response->error($code, $message, $status, $data); 
	}

	public function _show_404($message = 'unknown') {
		return $this->response->error404($message);
	}

	public function _render($view, $vals = false, $layout = false, $theme = false) {
		return $this->response->view($view, $vals, $layout, $theme);
	}
}


