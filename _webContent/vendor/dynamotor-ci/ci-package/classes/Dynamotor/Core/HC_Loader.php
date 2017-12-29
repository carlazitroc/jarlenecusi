<?php
namespace Dynamotor\Core;
use \CI_Loader;

class HC_Loader extends CI_Loader
{

	public function initialize(){
		parent::initialize();
		
		$this->_autoload_singleton_classes();
	}

	protected function _autoload_singleton_classes(){

		if (file_exists(APPPATH.'config/autoload.php'))
		{
			include(APPPATH.'config/autoload.php');
		}

		if (file_exists(APPPATH.'config/'.ENVIRONMENT.'/autoload.php'))
		{
			include(APPPATH.'config/'.ENVIRONMENT.'/autoload.php');
		}

		if ( ! isset($autoload))
		{
			return;
		}


		if(isset($autoload['singleton'])){
			foreach($autoload['singleton'] as $singleton_info){
				if(is_array($singleton_info) && isset($singleton_info['class'] )){
					$this->singleton($singleton_info);
				}
			}
		}

	}

	protected $_loaded_singleton_instances;
	protected $_loaded_singleton_alias;

	/**
	 * Get singleton class.
	 * @param  mixed   $classpath     Pass the string of fully qualified class path. Accepted to pass key-value based array contains prorperty "class", "parameter", "shared", "alias"
	 * @param  mixed   $parameter     Constructor parameter
	 * @param  boolean $shared        If true, load the class into shared in memory and reused when calling same class path. Otherwise, create instance each time requested. 
	 * @param  string  $alias         Name of the created instance. Nullable.
	 * @return mixed                  Instance of created class.
	 */
	public function singleton($classpath, $parameter = null, $is_shared = true, $alias = null){

		//log_message('debug',get_class($this).'/singleton['.__LINE__.'] ('.print_r(compact('classpath','parameter','is_shared','alias'), true).') ');

		if(is_array($classpath) ){
			$singleton_info = $classpath;
			
			$classpath = isset($singleton_info['class']) ? $singleton_info['class'] : null;
			$parameter = isset($singleton_info['parameter']) ? $singleton_info['parameter'] : null;
			$is_shared = isset($singleton_info['shared']) ? $singleton_info['shared'] != false : true;
			$alias = isset($singleton_info['alias']) ? $singleton_info['alias'] : null;

		}

		// Translate dot into slash form.
		$classpath = str_replace('.','\\',$classpath);

		//log_message('debug',get_class($this).'/singleton['.__LINE__.'] ('.print_r(compact('classpath','parameter','is_shared','alias'), true).') ');

		if(!empty($alias)){
			$instance = isset($this->_loaded_singleton_alias[$alias]) ? $this->_loaded_singleton_alias[$alias] : null;
		}else{

			$instance = isset($this->_loaded_singleton_alias[$classpath]) ? $this->_loaded_singleton_alias[$classpath] : null;
			if(empty($instance))
				$instance = isset($this->_loaded_singleton_instances[$classpath]) ? $this->_loaded_singleton_instances[$classpath] : null;
		}

		if(empty($instance) && !class_exists($classpath)){
			log_message('error', 'No classpath found for '.$classpath);
			throw new HC_Exception(-1, 'Unable to lookup class '.$classpath.'.');
		}


		if((!$is_shared || empty($instance)) && class_exists($classpath) ){

			log_message('debug','Loader is creating instance of singleton class '.$classpath);
			$instance = new $classpath($parameter);

			if($is_shared){
				$this->_loaded_singleton_instances[$classpath] = $instance;
				if(!empty($alias))
					$this->_loaded_singleton_alias[$alias] = $instance;

			}
		}

		if(empty($instance)){
			throw new HC_Exception(-1, 'Unable to find instance of class '.$classpath);
		}

		return $instance;
	}

	/**
	 * [widget description]
	 * @param  [type]  $view   [description]
	 * @param  [type]  $vals   [description]
	 * @param  boolean $return [description]
	 * @return [type]          [description]
	 */
	public function widget($view, $vals=NULL,$return = FALSE){
		if(!is_array($vals)) $vals = array();

		$_ci_CI =& get_instance();
		
		$theme = $this->config->item('theme');

		$req_ext = EXT;

		// explode file extension
		if(substr($view,- strlen(EXT)) == EXT) {
			$segs = explode('/', $view);
			$file = $segs [ count($segs) - 1];
			$_info = explode('.',2);
			$req_ext = $_info[1];
		}

		$view_path = NULL;
		if (empty($view_path)) {
			$widget_path = 'themes/' . $theme . '/widgets/';
			$view_path = $widget_path . $view . $req_ext;
			if (!file_exists(APPPATH . 'views/' . $view_path)) {
				$view_path = NULL;
			}
		}
		if (empty($view_path)) {
			$widget_path ='widgets/';
			$view_path = $widget_path . $view . $req_ext;
			if (!file_exists(APPPATH . 'views/' . $view_path)) {
				$view_path = NULL;
			}
		}
		if(empty($view_path)){
			throw new HC_Exception(-1, 'Widget "'.$view.'" does not exist.');
		}

		$this->load->helper('string');
		$_hash = $element_id = random_string('alnum',16);

		if(!empty($vals) && is_array($vals))
			extract($vals);

		/*
		 * Buffer the output
		 *
		 * We buffer the output for two reasons:
		 * 1. Speed. You get a significant speed boost.
		 * 2. So that the final rendered template can be
		 * post-processed by the output class.  Why do we
		 * need post processing?  For one thing, in order to
		 * show the elapsed page load time.  Unless we
		 * can intercept the content right before it's sent to
		 * the browser and then stop the timer it won't be accurate.
		 */
		ob_start();

		//print '<!-- WIDGET: '. $view.', element:'.$element_id.' widget_path:'. $widget_path .' -->'."\r\n";
		include APPPATH . 'views/' .$view_path;
		//print "\r\n<!-- /WIDGET -->\r\n";
		// Return the file data if requested
		if ($return === TRUE)
		{
			$buffer = ob_get_contents();
			@ob_end_clean();
			return $buffer;
		}

		/*
		 * Flush the buffer... or buff the flusher?
		 *
		 * In order to permit views to be nested within
		 * other views, we need to flush the content back out whenever
		 * we are beyond the first level of output buffering so that
		 * it can be seen and included properly by the first included
		 * template and any subsequent ones. Oy!
		 *
		 */
		if (ob_get_level() > $this->_ci_ob_level + 1)
		{
			ob_end_flush();
		}
		else
		{
			$_ci_CI->output->append_output(ob_get_contents());
			@ob_end_clean();
		}

	}
}
