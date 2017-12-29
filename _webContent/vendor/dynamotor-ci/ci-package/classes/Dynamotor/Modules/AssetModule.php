<?php
/** 
 * Asset library for CodeIgniter
 * @author      leman 
 * @copyright   Copyright (c) 2015, LMSWork. 
 * @link        http://lmswork.com 
 * @since       Version 1.0 
 *  
 */

namespace Dynamotor\Modules{

class AssetModule extends \Dynamotor\Core\HC_Module
{

	var $_meta_property = array();
	
	var $_meta = array();
	
	var $_css = array();
	
	var $_scripts = array();
	
	var $_linked_paths = array();
	var $_assigned_paths = array();
	
	var $_links = array();

	var $_data = array();

	
	public function print_tags($type = 'head')
	{
		if( $this->_is_debug()){
			print "<!-- Asset[$type]// -->\r\n";
		}

		$counter = 0;
		foreach($this->_meta as $idx =>$row)
		{
			$row_type = !isset($row['position']) || !$row['position'] ? 'head' : $row['position'];
			if($row_type == $type){
				if($counter < 1 && $this->_is_debug()){
					print "<!-- Meta -->\r\n";
				}
				$counter++;

				// print multiple meta content
				if(!empty($row['attrs'])){
					print $this->meta_tag($row['attrs'])."\r\n";
				}elseif(is_array($row)){
					foreach($row as $_row)
						print $this->meta_tag($_row['attrs'])."\r\n";
				}
			}
		}


		if($type == 'head'){
			$counter = 0;
			foreach($this->_meta_property as $name => $content)
			{
				if($counter < 1 && $this->_is_debug()){
					print "<!-- Meta Property -->\r\n";
				}
				$counter++;
				if(is_array($content)){
					foreach($content as $_content)
						print $this->meta_tag(array('property'=>$name,'content'=>$_content))."\r\n";
				}elseif($content !== NULL){
					print $this->meta_tag(array('property'=>$name,'content'=>$content))."\r\n";
				}
			
			}
		}

		$counter = 0;
		foreach($this->_links as $idx =>$row)
		{
			if(empty($row['attrs'])) continue;
				
			$row_type = !isset($row['position']) ? 'head' : $row['position'];
			if($row_type == $type){
				if($counter < 1 && $this->_is_debug()){
					print "<!-- Links -->\r\n";
				}
				print $this->link_tag($row['attrs'], !empty($row['options']) ? $row['options'] : NULL)."\n";
				$counter ++;
			}
		}

		$counter = 0;
		foreach($this->_css as $idx =>$row)
		{
			$row_type = !isset($row['position']) || !$row['position'] ? 'head' : $row['position'];
			if($row_type == $type){
				if($counter < 1 && $this->_is_debug()){
					print "<!-- Stylesheet -->\r\n";
				}
				$counter++;
				print $this->css_tag($row['attrs'],isset($row['code']) ? $row['code'] : '', !empty($row['options']) ? $row['options'] : NULL)."\r\n";
			}
		}
		$counter = 0;
		foreach($this->_scripts as $idx =>$row)
		{
			$row_type = !isset($row['position']) || !$row['position'] ? 'head' : $row['position'];
			if($row_type == $type){
				if($counter < 1 && $this->_is_debug()){
					print "<!-- JS -->\r\n";
				}
				$counter++;
				print $this->script_tag($row['attrs'],isset($row['code']) ? $row['code'] : '', !empty($row['options']) ? $row['options'] : NULL)."\r\n";
			}
		}
		
		if($this->_is_debug()){
			print "<!-- //Asset[$type] -->\r\n";
		}

		return $this;
	}

	/* Set Temporary Data */

	public function reset_data($name, $content = ''){
		$this->_data[$name] = NULL;
		return $this;
	}

	public function get_data($name, $type = 'raw',$separator = ' '){
		if(isset($this->_data[$name])){
			if(!is_array($this->_data[$name]) && $type == 'array') return array($this->_data[$name]);
			if(is_array($this->_data[$name]) && $type == 'string') return implode($separator, $this->_data[$name]);
			return $this->_data[$name];
		}
		return NULL;
	}

	public function set_data($name, $content = ''){
		$this->_data[$name] = $content;
		return $this;
	}

	public function add_data($name, $content = ''){
		$raw = NULL;
		if(isset($this->_data[$name])){
			$raw = $this->_data[$name];
			if(!is_array($raw)){
				$this->_data[$name] = array( $raw );
			}
		}else{
			$this->_data[$name] = array();
		}

		$this->_data[$name][] = $content;
		return $this;
	}


	/*****/
	// Meta Tags


	public function reset_meta_property($name, $content = ''){
		$this->_meta_property[$name] = NULL;
		return $this;
	}

	public function set_meta_property($name, $content = ''){
		$this->_meta_property[$name] = $content;
		return $this;
	}

	public function get_meta_property($name){
		return isset($this->_meta_property[$name]) ? $this->_meta_property[$name] : NULL;
	}

	public function add_meta_property($name, $content = ''){
		$raw = NULL;
		if(isset($this->_meta_property[$name])){
			$raw = $this->_meta_property[$name];
			if(!is_array($raw)){
				$this->_meta_property[$name] = array( $raw );
			}
		}else{
			$this->_meta_property[$name] = array();
		}

		$this->_meta_property[$name][] = $content;
		return $this;
	}

	
	public function meta($attrs=false)
	{
		$_hash = md5(isset($attrs['name']) ? $attrs['name'] : json_encode($attrs));

		$this->_meta [ $_hash  ] = array('attrs'=>$attrs,'position'=>'head');
		return $this;
	}
	
	public function set_meta_content($name='',$content='',$pos='head')
	{
		$attrs = array();
		$attrs['name'] = $name;
		$attrs['content'] = $content;
		$_hash = md5(isset($attrs['name']) ? $attrs['name'] : json_encode($attrs));
		$this->_meta [$_hash] = array('attrs'=>$attrs,'position'=>$pos);
		return $this;
	}

	public function add_meta_content($name, $content = '',$pos='head'){

		$attrs = array();
		$attrs['name'] = $name;
		$attrs['content'] = $content;
		$_hash = md5(isset($attrs['name']) ? $attrs['name'] : json_encode($attrs));

		$raw = NULL;
		if(isset($this->_meta[$_hash])){
			$raw = $this->_meta[$_hash];
			if(!is_array($raw)){
				$this->_meta[$_hash] = array( $raw );
			}
		}else{
			$this->_meta[$_hash] = array();
		}

		$this->_meta[$_hash][] = array('attrs'=>$attrs,'position'=>$pos);
		return $this;
	}

	/* JS */

	
	public function js($src,$attrs=false,$pos='head',$options=false)
	{
		$args = func_get_args();

		// pass assoicated array(dictionary) to first parameter for different case
		if(!empty($args[0]) && is_array($args[0])){
			$cfg = $args[0];

			// get parsed value from config
			$type = data('type', $cfg, 'import');
			$attrs = data('attrs', $cfg,false);
			$pos = data('pos', $cfg, 'head');
			$options = data('options', $cfg, false);
			$src = data('src', $cfg, false);
			$vals = data('vals', $cfg, false);


			$_import = data('import', $cfg, false);
			$_code = data('code', $cfg, false);
			$_embed = data('embed', $cfg, false);

			if(!empty($_import)) {
				$this->js_import($_import, $attrs, $pos, $options);
			}
			if(!empty($_code)) {
				$this->js_code($_code, $attrs, $pos,$options);
			}
			if(!empty($_embed)) {
				$this->js_embed($_embed, $vals, $attrs, $pos,$options);
			}
			
			if($type == 'import' && !empty($src)){
				$this->js_import($src, $attrs, $pos, $options);
			}else if($type == 'code'){
				$this->js_code($src, $attrs, $pos,$options);
			}else if($type == 'embed'){
				$this->js_embed($src, $vals, $attrs, $pos,$options);
			}
			return $this;
		}

		// act as default function
		// 
		if(is_string($src))
			$this->js_import($src, $attrs, $pos, $options);
		return $this;
	}

	public function js_code($code='',$attrs=false,$pos='head',$options=false){
		$merged = $code;

		if($pos == 'inline'){
			print $this->script_tag($attrs,$merged,$options);
		}else{
			$this->_scripts[] = array('attrs'=>$attrs,'code'=>$merged,'position'=>$pos,'options'=>$options);
		}
		return $this;
	}

	public function js_embed($src, $vals=false, $attrs=false, $pos = 'head',$options=false){
		$code = $this -> load -> view($src, $vals, TRUE);
		$merged = $code;

		if(empty($code) && empty($attrs)) {
			log_message('debug','Render/js_embed, No code/attributes have been assigned.');
			return $this;
		}

		if($pos == 'inline'){
			print $this->script_tag($attrs,$merged,$options);
		}else{
			$this->_scripts[] = array('attrs'=>$attrs,'code'=>$merged,'position'=>$pos,'options'=>$options);
		}
		return $this;
	}
	
	public function js_import($path,$extra_attr=false,$pos='head',$options=false)
	{
		if(isset($this->_assigned_paths[$path])){
			return $this;
		}
		$this->_assigned_paths[$path] = true;

		$attrs = array('type'=>'text/javascript','src'=>$path);
		if(!empty($extra_attr) && is_array($extra_attr)){
			$attrs = array_merge($attrs,$extra_attr );
		}
		
		if($pos == 'inline'){
			print $this->script_tag($attrs,'',$options);
		}
		else{
			$this->_scripts[] = array('attrs'=>$attrs, 'position'=>$pos,'options'=>$options);
		}
		return $this;
	}

	/* CSS */

	
	public function css($src, $attrs=false,$pos='head', $options=false){
		$args = func_get_args();

		// pass assoicated array(dictionary) to first parameter for different case
		if(!empty($args[0]) && is_array($args[0])){
			$cfg = $args[0];

			// get parsed value from config
			$type = data('type', $cfg, 'import');
			$attrs = data('attrs', $cfg,false);
			$pos = data('pos', $cfg, 'head');
			$options = data('options', $cfg, false);
			$src = data('src', $cfg, false);
			$vals = data('vals', $cfg, false);


			$_import = data('import', $cfg, false);
			$_code = data('code', $cfg, false);
			$_embed = data('embed', $cfg, false);

			if(!empty($_import)) {
				$this->css_import($_import, $attrs, $pos, $options);
			}
			if(!empty($_code)) {
				$this->css_code($_code, $attrs, $pos,$options);
			}
			if(!empty($_embed)) {
				$this->css_embed($_embed, $vals, $attrs, $pos,$options);
			}
			
			if($type == 'import' && !empty($src)){
				$this->css_import($src, $attrs, $pos, $options);
			}else if($type == 'code'){
				$this->css_code($src, $attrs, $pos,$options);
			}else if($type == 'embed'){
				$this->css_embed($src, $vals, $attrs, $pos,$options);
			}
			return $this;
		}

		// act as default function
		if(is_string($src))
			$this->css_import($src, $attrs, $pos,$options);
		return $this;
	}
	
	public function css_code($code,$attrs=false,$pos='head', $options=false){
		if(!$attrs) $attrs = array();
		//$attrs['href'] = $view;
		if(!isset($attrs['rel']))$attrs['rel'] = 'stylesheet';
		if(!isset($attrs['type']))$attrs['type'] = 'text/css';
		
		if($pos == 'inline'){
			print $this->css_tag($attrs,$code,$options);
		}else{
			$this->_css[] = array('attrs'=>$attrs,'code'=>$code,'position'=>$pos,'options'=>$options);
		}
		return $this;
	}
	
	public function css_import($path,$attrs=false,$pos='head', $options=false){


		if(isset($this->_assigned_paths[$path])){
			return $this;
		}
		$this->_assigned_paths[$path] = true;

		if(!$attrs) $attrs = array();
		$attrs['href'] = $path;
		if(!isset($attrs['rel']))$attrs['rel'] = 'stylesheet';
		if(!isset($attrs['type']))$attrs['type'] = 'text/css';
		//$attrs['src'] = $path;
		if($pos == 'inline'){
			print $this->link_tag($attrs,$options);
		}else{
			$this->_links[] = array('attrs'=>$attrs,'position'=>$pos,'options'=>$options);
		}

		return $this;
	}

	
	public function css_embed($view, $vals=NULL,$attrs=false,$pos='head', $options=false){
		if(!$attrs) $attrs = array();
		//$attrs['href'] = $view;
		if(!isset($attrs['rel']))$attrs['rel'] = 'stylesheet';
		if(!isset($attrs['type']))$attrs['type'] = 'text/css';
		
		$code = $this -> load -> view($view, $vals, TRUE);
		if($pos == 'inline'){
			print $this->css_tag($attrs,$code,$options);
		}else{
			$this->_css[] = array('attrs'=>$attrs,'code'=>$code,'position'=>$pos,'options'=>$options);
		}
		return $this;
	}

	/* Other */

	public function meta_tag($attrs,$options=false)
	{
		if(!$attrs) $attrs = array();
		$str = '';
		return $this->_handle_options('<meta'.$this->_array_to_attr_str($attrs).' />',$options);
	}
	
	protected function trigger_code_handler($event, $code, $options = false)
	{
		if(isset($options[$event]) && $options[$event]){

			$callable = $options[$event];

			if(is_callable($callable)){
				$code = call_user_func($callable, $code);
			}
		}
		return $code;
	}
	
	public function css_tag($attrs=false,$code='',$options=false)
	{

		$code = $this->trigger_code_handler('before_output', $code, $options);

		if(isset($options['no_tag']) && $options['no_tag']){
			return $this->_handle_options($code,$options);
		}

		if(!$attrs) $attrs = array();
		$str = '';

		if(!isset($attrs['type'])) $attrs['type'] = 'text/stylesheet';
		return $this->_handle_options('<style'.$this->_array_to_attr_str($attrs).'>'.$code.'</style>',$options);
	}
	
	public function script_tag($attrs=false,$code='', $options=false)
	{

		$code = $this->trigger_code_handler('before_output', $code, $options);

		if(isset($options['no_tag']) && $options['no_tag']){
			return $this->_handle_options($code,$options);
		}

		if(!$attrs) $attrs = array();
		
		if(!isset($attrs['type'])) $attrs['type'] = 'text/javascript';

		return $this->_handle_options('<script'.$this->_array_to_attr_str($attrs).'>'.$code.'</script>',$options);
	}
	
	public function link($path,$attrs=false,$pos='head',$options=false){

		if(isset($this->_assigned_paths[$path])){
			return;
		}
		$this->_assigned_paths[$path] = true;
		
		$attrs['href'] = $path;
		if($pos == 'inline') print $this->_link_tag($attrs,$options);
		else $this->_links[] = array('attrs'=>$attrs, 'position'=>$pos,'options'=>$options);  

		return $this;
	}
	
	public function link_tag($attrs=false,$options=false){
		return $this->_handle_options('<link'.$this->_array_to_attr_str($attrs).'/>',$options);
	}
	
	
	public function a_tag($path=false,$name=false,$attrs=false,$options=false){
		if(!$name ) $name = $path;
		if(!$attrs) $attrs = array();
		$attrs['href'] = $path;
		return $this->_handle_options('<a'.$this->_array_to_attr_str($attrs).'>'.$name.'</a>', $options);
	}
	
	protected function _attr_escape($str)
	{
		return html_attribute_escape($str);
	}
	
	protected function _array_to_attr_str($attrs)
	{
		return array_to_html_attribute($attrs);
	}

	protected function _handle_options($code='', $options=false){

		$str = '';
		if(is_array($options) && !empty($options['prefix']))
			$str.= $options['prefix'];
		$str.= $code;
		if(is_array($options) && !empty($options['postfix']))
			$str.= $options['postfix'];
		return $str;
	}
	protected function _is_debug(){
		return defined('ENVIRONMENT') && !in_array(ENVIRONMENT, array('staging','production'));
	}
}
}