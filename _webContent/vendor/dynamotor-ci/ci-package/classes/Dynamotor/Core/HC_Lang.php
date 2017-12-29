<?php
namespace Dynamotor\Core{
use CI_Lang;
use CI_Controller;

class HC_Lang extends CI_Lang
{
	protected $lang_code = '';
	protected $country_code = null;
	protected $locale_code = '';
	protected $language_code = 'en';
	public $locale_def = 'en';
	protected $standard_locale_code = 'en_US';
	protected $extra_values = null;
	protected $is_requseted_locale_supported = false;
	protected $is_locale_supported = false;
	protected $orig_segments = null;
	protected $orig_uri_string = null;

	protected $segments;
	protected $uri_string;

	protected $is_using_default_locale = false;

	protected $uri_parsed_locale_info = null;

	public $requested_locale_code = null;
	public $host = null;
	
	public $uri_has_locale = false;
	public $uri_is_alias_locale = false;
	
	public $supported_locales = array();
	
	protected $_supported_locale_codes = array();
	
	public function __construct()
	{
		parent::__construct();
		
		$this->__init();
	}

	public function has_available_locale()
	{
		$locales = $this->get_available_locale_keys();
		if(count($locales)>0){
			return true;
		}
		return false;
	}

	public function get_available_locale_keys()
	{
		$locales = array();
		foreach($this->supported_locales as $locale_key => $locale_info){
			if(is_string($locale_info)) continue;
			$locales[] = $locale_key;
		}
		return $locales;
	}

	public function get_all_locale_keys(){
		return array_keys($this->supported_locales);
	}

	public function get_locale_info($locale_code){
		if(isset($this->supported_locales[$locale_code]))
			return $this->supported_locales[ $locale_code ];
		return null;
	}
	
	public function url_prefix($locale_code=false)
	{
		if(!$locale_code) $locale_code = $this->locale_code;
		
		$locale_info = $this->get_locale($locale_code);
		
		if($locale_info['locale'] != $this->locale_def){
			return $this->locale_code.'/';
		}
		return '';
	}
	
	protected function parse_url_segments($segments)
	{
		$def_loc = $this->locale_def;
		
		$cur_loc = !empty($segments[1]) ? $segments[1] : null;
		if(empty($cur_loc) || !$this->valid_locale_str($cur_loc)){
			//log_message('debug','HC_Lang/parse_url_segments: non-locale format: ' . print_r($segments,true));
			return null;
			//$cur_loc = $this->locale_def;
		}
		//log_message('debug','MY_Lang/parse_url_segments: locale format: ' . $cur_loc);
		
		$locale_info = $this->parse_locale($cur_loc);
		if(!isset($locale_info['locale'])){
			$locale_info = $this->parse_locale( $def_loc );
		}
		//log_message('debug','HC_Lang/parse_url_segments: parsed locale: ' . print_r($locale_info,true));
		return $locale_info;
	}
	
	public function parse_url($str=''){
		$offset = substr($str,0,1) == '/' ? 1:0;
		$segments = explode('/',$str);
		
		//log_message('debug','MY_Lang/parse_url: ["'.$str.'"/'.$offset.']='.$segments[$offset]);
		
		return $this->parse_locale($segments[$offset]);
	}
	
	/**
	 * Parse the localizcation information by string 
	 *
	 * @return	mixed
	 */
	public function parse_locale($str=''){

		if(preg_match("/^([a-z]{2}|master)[\_\-]([a-zA-Z]{2})$/",$str,$matches)){
		
			return array(
				'locale'=>strtolower($str),
				'lang'=>strtolower($matches[1]),
				'country'=>strtolower($matches[2]),
			);
		}elseif(preg_match("/^([a-z]{2}|master)$/",$str,$matches)){
			return array(
				'locale'=>strtolower($str),
				'lang'=>null,
				'country'=>strtolower($matches[1]),
			);
		}else{
			//log_message('info','MY_Lang/parse_locale# non-locale format:'.$str);
		}
		
		return null;
	}
	
	public function get_locale($str=''){
		$str = strtolower($str);
		$str_info = $this->parse_locale($str);
		//log_message('debug','MY_Lang/get_locale='.$str.',info='.print_r($str_info,true));
		
		if(!isset($this->supported_locales[ $str_info['locale'] ])) return null;
		
		$data = array();
		$data['locale'] = $str_info['locale'];
		$data['lang'] = $str_info['lang'];
		$data['country'] = $str_info['country'];
		$data['language'] = isset($str_info['language']) ? $str_info['language'] : $str_info['lang'];
		$data['extra'] = isset($str_info['extra']) ? $str_info['extra'] : null;
		
		$def_data = $this->supported_locales[ $str_info['locale'] ];
		
		
		if(is_string($def_data)){
			$def_data = $this->supported_locales[ $def_data ];
		}
		if(is_array($def_data)){
			if(isset($def_data['lang']))
				$data['lang'] = $def_data['lang'];
			if(isset($def_data['country']))
				$data['country'] = $def_data['country'];
			if(isset($def_data['locale']))
				$data['locale'] = $def_data['locale'];
			if(isset($def_data['s_locale']))
				$data['s_locale'] = $def_data['s_locale'] ;
			if(isset($def_data['language']))
				$data['language'] = $def_data['language'] ;
			if(isset($def_data['host']))
				$data['host'] = $def_data['host'] ;
			if(isset($def_data['extra']))
				$data['extra'] = $def_data['extra'] ;
		}
		return $data;
	}
	
	public function valid_locale_str($str=''){
		return preg_match("/^([a-z]{2}|master)[\_\-]([A-Z]{2}|[a-z]{2})$/",$str) || preg_match("/^([a-z]{2}|master)$/",$str);
	}
	
	public function has_locale($str='')
	{
		$locale_info = $this->parse_url($str);
		if(isset($locale_info['locale'])){
			return in_array($locale_info['locale'], $this->_supported_locale_codes);
		}
		return false;
	}
	
	public function localize_url($str, $def_locale = null){
		// log_message('debug',get_class($this).'/localize_url, str='.$str);
		if(!empty($str) && $this->has_locale($str)){
			return $str;
		}
		$prefix = $this->url_prefix($def_locale);
		if(substr($prefix,-1,1) !='/' && substr($str,0,1)!='/')
			$prefix.='/';

		return $prefix.$str;
	}
	
	var $query_pattern = '#(\{lang\:([a-zA-Z\-\_0-9\.\s]+)\})#';
	public function line($line = '', $show_original_text=true)
	{

		if(preg_match_all($this->query_pattern,$line,$matches)){
			$text = $line ;
			for($i = 0; $i < count($matches[0]); $i++){
				$pattern = $matches[1][$i];
				$key = $matches[2][$i];
				//print_r(compact('pattern','key'));
				$new_str = $this->line($key, $show_original_text);
				//if($new_str == $key) continue;
				$text = str_replace($pattern, $new_str, $text);
			}

			return $text;
		}

		$value = parent::line($line);

		// Because killer robots like unicorns!
		if ($value === false && $show_original_text)
		{
			return $line;
		}

		return $value;
	}
	
	public function lang(){
		return $this->lang_code;
	}
	
	public function country(){
		return $this->country_code;
	}
	
	public function locale(){
		return $this->locale_code;
	}
	
	public function language(){
		return $this->language_code;
	}

	public function standard_locale(){
		return $this->standard_locale_code;
	}
	
	public function extra($name, $locale = false){
		if(!empty($locale) && is_string($locale)){
			$locale_info = $this->get_locale($locale);
			if(isset($locale_info['extra'][$name])){
				return $locale_info['extra'][$name];
			}
			return null;
		}
		return isset($this->extra_values[$name]) ? $this->extra_values[$name] : '';
	}
	
	public function requested_locale(){
		return $this->requested_locale_code;
	}
	
	public function is_alias(){
		return $this->uri_is_alias_locale;
	}

	public function is_supported($locale, $alias= true){
		if(is_string($locale)){
			$is_available = isset($this->supported_locales[ $locale ]);
			if($is_available && !$alias)
				return !is_string($this->supported_locales[ $locale ]);
			return $is_available;
		}
		return false;
	}

	public function is_locale_supported(){
		return $this->is_locale_supported;
	}

	public function is_requseted_locale_supported(){
		return $this->is_requseted_locale_supported;
	}
	
	protected function __init(){
	
		global $URI,$CFG,$RTR;
		
		$this->orig_segments = $URI->segments;
		$this->orig_uri_string = $URI->uri_string;

		$CFG->load('language');

		$this->reinitialize();
	}
	public function reinitialize($supported_locales = null, $locale_def = null){


		global $URI,$CFG,$RTR;

		$this->reset_data();

		if(empty($locale_def)){
			$locale_def = $CFG->item('default_locale');
		}

		if(!empty($locale_def)){
			$this->locale_def = $locale_def;
		}

		if(empty($supported_locales)){
			$supported_locales = $CFG->item('supported_locales');
		}
		$this->supported_locales = $supported_locales;
		
		
		if(is_array($this->supported_locales)){
			$this->_supported_locale_codes = array_keys($this->supported_locales);
		}
		
		$locale_data = $this->get_locale($this->locale_def);
		
		$locale_info = $this->parse_url_segments($this->orig_segments);
		$this->uri_parsed_locale_info = $locale_info;
		
		if(isset($locale_info['locale'])){
			$this->lang_code= $locale_info['lang'];
			$this->country_code= $locale_info['country'];
			$this->locale_code= $locale_info['locale'];
			$this->uri_has_locale = true;
		}
		
		
		if($this->uri_has_locale ){
			//log_message('debug','MY_Lang# before re-group segments:'.print_r($URI->segments,true));
			//log_message('debug','MY_Lang# before re-group rsegments:'.print_r($URI->rsegments,true));
			//
			if(!empty($this->orig_segments[1]) ){
				$this->requested_locale_code = $this->orig_segments[1];
			
				// replace uri's segment for correct
				$new_segments = array();
				$counter = 1; // URI segment is started from 1
				// first node must be supported locale code
				for($i = 2; $i <= count($this->orig_segments); $i++){
					$new_segments[$counter] = $this->orig_segments[$i];
					$counter++;
				}

				//log_message('debug','MY_Lang# re-group segments:'.print_r($new_segments,true));

				
				$this->segments = $new_segments;
				$this->uri_string = implode("/", $new_segments);

				$URI->segments = $this->segments;
				$URI->uri_string = $this->uri_string;


			}
			
			$this->uri_is_alias_locale = isset($this->supported_locales[$this->requested_locale_code]) && is_string($this->supported_locales[$this->requested_locale_code]);
			
			$locale_data = $this->get_locale($locale_info['locale']);
			
			if(!empty($locale_data['locale']))
				$this->change_locale($locale_data['locale']);

			$this->is_requseted_locale_supported = !empty($this->requested_locale_code) ?  $this->is_supported($this->requested_locale_code) : false;
			$this->is_locale_supported = !empty($this->locale_code) ?  $this->is_supported($this->locale_code) : false;

		}else{
			$this->is_using_default_locale = true;
			$locale_data = $this->get_locale($this->locale_def);
			
			if(!empty($locale_data['locale']))
				$this->change_locale($locale_data['locale']);

			$this->is_locale_supported = true;
			$this->is_requseted_locale_supported = true;
		}

		if(!$this->is_locale_supported){
			log_message('error','HC_Lang# unsupported locale:'.$this->locale_code);
		}

		$CFG->set_item('language', $this->language_code);
		$CFG->set_item('supported_locales', $this->supported_locales);
		
	}

	public function change_locale($locale_code){

		if(empty($locale_code)) return false;


		$locale_data = $this->get_locale($locale_code);
		
		if(isset($locale_data['locale'])){

			//log_message('debug','HC_Lang/change_locale, able to change locale to '.$locale_code.'. Info:'.print_r($locale_data, true));

			if(isset($locale_data['lang']))
				$this->lang_code = $locale_data['lang'];
			if(isset($locale_data['country']))
				$this->country_code = $locale_data['country'];
			if(isset($locale_data['locale']))
				$this->locale_code = $locale_data['locale'];
			if(isset($locale_data['language']))
				$this->language_code = $locale_data['language'];
			if(isset($locale_data['s_locale']))
				$this->standard_locale_code = $locale_data['s_locale'];
			if(isset($locale_data['host']))
				$this->host = $locale_data['host'];
			if(isset($locale_data['extra']))
				$this->extra_values = $locale_data['extra'];
			
			if(is_array($this->is_loaded)){
				foreach($this->is_loaded as $file => $loaded_lang_code){
					$this->load($file, $this->language_code);
				}
			}

			return true;
		}else{
			return false;
		}
	}

	protected function reset_data()
	{
		$this->locale_def = 'en';
		$this->locale_code = 'en';
		$this->lang_code = 'en';
		$this->country_code = 'us';
		$this->extra_values = array();
		$this->language_code = 'en';
		$this->standard_locale_code = 'en_US';
		$this->is_requseted_locale_supported = false;
		$this->requested_locale_code = null;
		$this->is_locale_supported = false;
		$this->host = null;
		$this->uri_has_locale = false;
		$this->uri_is_alias_locale = false;
		$this->supported_locales = array('en');
		$this->_supported_locale_codes = array('en');
		$this->is_using_default_locale = false;

		$this->segments = null;
		$this->uri_string = '';
	}
}
}