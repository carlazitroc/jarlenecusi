<?php

use Dynamotor\Helpers\ResourceHelper;

// CI_Config helper function
function config_get($item_name){
	$CI = &get_instance();
	
	return $CI->config->item($item_name);
}

function config_set($item_name,$new_val=NULL){
	$CI = &get_instance();
	
	$CI->config->set_item($item_name,$new_val);
}

function config_load($config_name){
	$CI = &get_instance();
	$CI->load->config($config_name);

}

// Output text from language with parameter support
// if the parameter's value is an array, will use comma ',' to separate it
function slang($key, $parameters=false){
	$CI = &get_instance();
	$CI->load->helper('language');
	$text = lang($key);
	return stext($text, $parameters);
}

// Output text with parameter support
// if the parameter's value is an array, will use comma ',' to separate it
function stext($text='',$parameters=false){
	if(!$text) $text = '';
	$parameters['base_url'] = base_url();

	if(!empty($parameters) && is_array($parameters)){
		foreach($parameters as $key => $val){
			if(is_string($val) || is_int($val) || is_float($val)){
				$text = str_replace('{'.$key.'}',$val, $text);
			}elseif(is_array($val)){
				$text = str_replace('{'.$key.'}',implode(', ',$val), $text);
			}
		}
	}
	return $text;
}

function spacer_url(){
	return 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
}

function array_to_html_attribute($attrs)
{
	$str = '';
	if(!is_array($attrs) || count($attrs)<1)return $str;
	foreach($attrs as $key => $val)
	{
		$str.=' '.$key.'="'.html_attribute_escape($val).'"';
	}
	return $str;
}

function html_attribute_escape($str)
{
	$str = str_replace('"',"&quot;",$str);
	$str = str_replace("<","&lt;",$str);
	return $str;
}

function uri_query($extra=false, $prefix_sign = '?'){
	
	$data = NULL;
	if(isset($_SERVER['QUERY_STRING']))	
		parse_str($_SERVER['QUERY_STRING'],$data);
	if(is_array($extra)){
		foreach($extra as $key=> $val){
			$data[$key] = $val;
		}
	}
	$str = '';
	if(!empty($data) && !empty($prefix_sign))
		$str = $prefix_sign;
	if(!empty($data))
		$str.= http_build_query($data);
	return $str; 
}

function has_scheme($str){ return preg_match('/^[a-z0-9]+\:\/\/.+/',$str) ? TRUE: FALSE; }
function fix_http_url($path){
	if(has_scheme($path)) return $path;

	if( substr($path,0,2) == '//'){
		if(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443'){
			$path = 'https:'.$path;
		}else{
			$path = 'http:'.$path;
		}
	}
	return $path;
}

function create_url($path='', $type = 'base_url', $options = null){
	$CI = &get_instance();
	
	return $CI->config->url($path, $type, $options);
}

// URL Helper function
function web_url($path=''){
	return create_url($path, 'web_url');
}

function pub_url($path=''){
	return create_url($path, 'pub_url');
}

// Passing url by themes folder
function theme_url($path=''){
	$CI = &get_instance();
	
	return $CI->config->theme_url($path);
}

function asset_url($path=''){
	return create_url($path, 'asset_url');
}

function has_tail_slash($path){
	return (substr($path,-1,1) == '/') ? TRUE : FALSE;
}

function site_path($path=''){
	$root_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 'https://':'http://').$_SERVER['HTTP_HOST'];
	$root_path = substr(site_url(),strlen($root_url));
	if(substr($root_path,-1,1)!='/' && substr($path,0,1)!='/')
		$root_path .= '/';
	return $root_path.$path;
}

function res_url($path=''){
	return create_url($path, 'resource_url');
}

function resource_url($path=''){
	return create_url($path, 'resource_url');
}

function forward_url(){
	$CI = &get_instance();
	return $CI->input->get_post('forward');
}

function url($path='', $type = 'base_url', $options = null){
	$CI = &get_instance();
	return $CI->config->url($path, $type, $options);
}

// Uploaded File Helper for generate public accessible URL

function uploaded_file($file,$rebuild=false,$source_path='files'){
	$CI = &get_instance();
	return $CI->resource->uploaded_file($file, $rebuild, $source_path);
}

function uploaded_file_url($file,$rebuild=false,$source_path='files', $dest_path = 'files', $options = NULL){
	$CI = &get_instance();
	return $CI->resource->uploaded_file($file, $rebuild, $source_path, $dest_path, $options);
}


function picture_url($cfg,$size_group='default', $size_name='default', $rebuild=false, &$image_info=false,$src_folder='files',$dest_folder='pictures',$croparea=NULL, $options = NULL){
	$CI = &get_instance();
	return $CI->resource->picture_url($cfg, $size_group, $size_name, $rebuild, $image_info, $src_folder, $dest_folder, $croparea, $options);
}

function _post($name){
	$CI = &get_instance();
	return $CI->input->post($name);
}
function _get($name){
	$CI = &get_instance();
	return $CI->input->get($name);
}

function _get_post($name){
	$CI = &get_instance();
	return $CI->input->get_post($name);
}

function _get_link_tail($prefix='?',$fields = false){
	$_linkTail = array();
	if(_get('dialog') == 'yes'){
		$_linkTail['dialog']= _get('dialog');
	}
	if(_get('callback') != NULL){
		$_linkTail['callback']= _get('callback');
	}
	if(_get('type') != NULL){
		$_linkTail['type']= _get('type');
	}
	if(_get('ignore-type') != NULL){
		$_linkTail['ignore-type']= _get('ignore-type');
	}
	if(is_array($fields)){
		foreach($fields as $idx => $field){
					
			if(_get($field) != NULL){
				$_linkTail[$field]= _get($field);
			}
		}
	}
	if(empty($_linkTail)) return '';
	return $prefix.http_build_query($_linkTail);
}

function link_detect($content){
	$content = str_replace("{root}",asset_url(),$content);
	$content = str_replace("{files}",USER_FILE_URL,$content);
	$content = str_replace("{pub}",PUB_URL,$content);
	$content = str_replace("'pub/","'".PUB_URL,$content);
	$content = str_replace("\"pub/","\"".PUB_URL,$content);
	return $content;
}

function am() {
	$r = array();
	$args = func_get_args();
	foreach ($args as $a) {
		if (!is_array($a)) {
			$a = array($a);
		}
		$r = array_merge($r, $a);
	}
	return $r;
}

function data($name, $_data=NULL, $null_value = NULL){
	global $data;
	
	$__data = $data;
	if(is_array($_data)){
		$__data = $_data;
	}
	
	if(isset($__data[$name]))
		return $__data[$name];
	return $null_value;
}

function print_choice($search='',$ary=false,$labelField=false,$notFoundStr='Undefined'){
	if(!$ary) print $search;
	$keys = array_keys($ary);
	if(!in_array($search,$keys)){
		print $notFoundStr;
	}else{
		if(isset($ary[$search][$labelField]) && $labelField){
			print $ary[$search][$labelField];
		}else{
			print $ary[$search];
		}
	}
}

function print_empty($val=NULL,$notValidStr='-'){
	if($val === false || $val === NULL || strlen($val)<1) print $notValidStr;
	print $val;
}

function chained_combobox($field, $data, $selected=false, $options=false){
	if(!$options) return '';
	$hasRoot = false;
	$rootName = 'Root';
	$rootDisabled = false;
	$rootValue = '';
	
	$hasUnknownRow = false;
	$unknownName = 'Unknown Value';
	$unknownValue = '';
	
	$startLevel = 1;
	
	$defVal = NULL;
	
	if(isset($options['root'])) {
		$hasRoot = true;
		$rootName = $options['root'];
		if(isset($options['root_disabled']) && $options['root_disabled']==true) $rootDisabled = true;
		if(isset($options['root_value']) ) $rootValue = $options['root_value'];
	}
	if(isset($options['unknown'])) {
		$hasUnknownRow = true;
		$unknownName = $options['unknown'];
	}
	if(isset($options['start'])) {
		$startLevel= $options['start'];
	}
	//$data = !isset($options['data'])? array():$options['data'];
	$unknownValue = $defVal = !isset($options['default'])? '':$options['default'];
	$attr = !isset($options['attribute'])? '':$options['attribute'];
	$parentIdKey = !isset($options['parent'])? 'parent_id':$options['parent'];
	$idKey = !isset($options['node'])? 'id':$options['node'];
	$labelKey = !isset($options['label'])? 'title':$options['label'];

	if(!empty($selected)){
		if(!is_array($selected))
			$selected = array($selected);
	}else{
		$selected = array();
	}

	// attrs
	if(!is_string($attr)){
		$str = '';
		foreach($attr as $key => $val){
			$str.= ' '.$key.'="'.$val.'"';
		}
		$attr = $str;
	}

	if(isset($options['multiple']) && $options['multiple'])
		$attr.=' multiple';

	$attr.=' name="'.$field.'"';
	
	// options
	$str = '';
	$found = false;
	$nodes = _chained_combo_nodes($data,-1,$startLevel,$idKey,$parentIdKey);
	foreach($nodes as $idx => $row){
		$str.='<option value="'.$row[$idKey].'"';
		if($row[$idKey] == $defVal || (!empty($selected) && in_array($row[$idKey], $selected)) ){
			$found = true;
			$str.= ' selected="selected"';
		}
		$str.='>';
		if($row['level']>=0){
			for($i=1 ;$i<$row['level']+$startLevel;$i++){
				$str.='&nbsp;&nbsp;';
			}
		}
		$str.=$row[$labelKey].'</option>';
	}
	if(!$found && $defVal!=$rootValue && $hasUnknownRow){
		$c ='<option value="'.$unknownValue.'"';
		$c.=' selected="selected"';
		$c.='>'.$unknownName." [".$unknownValue.']</option>';
		$str = $c.$str;
	}
	if($hasRoot){
		$c ='<option value="'.$rootValue.'"';
		if(!$hasUnknownRow || $rootValue == $defVal) $c.=' selected="selected"';
		if($rootDisabled) $c.= ' disabled="disabled"';
		$c.='>'.$rootName.'</option>';
		$str = $c.$str;
	}
	
	return '<select'.$attr.'>'.$str.'</select>';
}

	

function combobox($options=false){
	if(!$options) return '';
	$hasRoot = false;
	$rootName = 'Root';
	$rootDisabled = false;
	$rootValue = '';
	
	$hasUnknownRow = false;
	$unknownName = 'Unknown Value';
	$unknownValue = '';
	
	$startLevel = 1;
	
	if(isset($options['root'])) {
		$hasRoot = true;
		$rootName = $options['root'];
		if(isset($options['rootDisabled']) && $options['rootDisabled']==true) $rootDisabled = true;
		if(isset($options['rootValue']) ) $rootValue = $options['rootValue'];
	}
	if(isset($options['unknown'])) {
		$hasUnknownRow = true;
		$unknownName = $options['unknown'];
	}
	
	$data = !isset($options['data'])? array():$options['data'];
	$unknownValue = $defVal = !isset($options['defaultValue'])? '':$options['defaultValue'];
	$attr = !isset($options['attribute'])? '':$options['attribute'];
	
	$idKey = !isset($options['nodeKey'])? 'id':$options['nodeKey'];
	$labelKey = !isset($options['labelKey'])? 'title':$options['labelKey'];
	
	$str = '';
	$found = false;
	
	foreach($data as $idx => $row){
		$str.='<option value="'.$row[$idKey].'"';
		if($row[$idKey] == $defVal){
			$found = true;
			$str.= ' selected="selected"';
		}
		$str.='>';
		
		$str.=$row[$labelKey].'</option>';
	}
	if(!$found && $defVal!=$rootValue && $hasUnknownRow){
		$c ='<option value="'.$unknownValue.'"';
		$c.=' selected="selected"';
		$c.='>'.$unknownName." [".$unknownValue.']</option>';
		$str = $c.$str;
	}
	if($hasRoot){
		$c ='<option value="'.$rootValue.'"';
		if(!$hasUnknownRow || $rootValue == $defVal) $c.=' selected="selected"';
		if($rootDisabled) $c.= ' disabled="disabled"';
		$c.='>'.$rootName.'</option>';
		$str = $c.$str;
	}
	
	print '<select'.$attr.'>'.$str.'</select>';
}

function _chained_combo_nodes ( &$data, $parentNodeId=-1, $level=0,$idKey='id',$parentIdKey='parent_id'){
	$child = array();
	if(is_array($data)){
		foreach($data as $idx=>$row){
			if($parentNodeId == -1 && empty($row[$parentIdKey]) || $parentNodeId ==  $row[$parentIdKey]){
				$row['level'] = $level;
				$child[] = $row;
				$sub = _chained_combo_nodes($data, $row[$idKey], $level+1, $idKey,$parentIdKey);
				$child = array_merge($child,$sub);
			}
		}
	}
	
	return $child ;
}

function format_money($number, $cents = 1){
	if(is_numeric($number)){
		if(!$number)
			$money = ($cents == 2 ? '0.00' : '0');
	else
		if(floor($number)==$number) $money = number_format($number, ($cents == 2 ? 2 : 0));
		else $money = number_format(round($number, 2), ($cents == 0 ? 0 : 2));
	return "$".$money;
	}
}


function spot_text($text,$search,$length=50,$encoding='utf8'){
$pos =mb_strpos($text,$search,0,$encoding);
if($pos!=-1){
	$start = $pos - $length;
	if($start < 0 )$start = 0;
	
	$end = $pos + $length ;
	if($end > mb_strlen($text,$encoding)) $end = mb_strlen($text,$encoding);
	$str = mb_substr($text,$start,$end-$start,$encoding);
	return str_replace($search,'<span class="spot-text">'.$search.'</span>',$str);
	}
	return mb_substr($text,0,$length,$encoding);
}


function content_link($text,$attrs=' target="_blank"'){
	$text = strip_tags($text);
	// The Regular Expression filter
	$reg_exUrl = "/((http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?|[a-zA-Z0-9][a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?)/";

	// Check if there is a url in the text
	if(preg_match_all($reg_exUrl, $text, $matches)) {
		foreach($matches[0] as $idx => $pattern){
		    // make the urls hyper links
		    $url = $pattern;
		    $label = $url;
		    if(substr($url,0,4)!='http' ){ $label = $url;  $url= 'http://'.$url;}
			$text = str_replace($pattern, '<a href="'.$url.'"'.$attrs.'>'.$label.'</a>', $text);
		}

	} 
	return $text;
}


/**
 * Render element by giving view file's path with data
 * 
 * Render view file by giving data, language code
 * 
 * @param	string	The path of view file which under /application/views. 
 * 			It is not necessary to giving language code, requested extension name and the view file extension
 * @param	array	View Data that will apply to view file
 * @param	bool	if true, return the source code after render compile the view file with data.
 * 			otherwise, it will output to browser directly
 * @return	string	return source code if the forth parameter is true.
 * 			otherwise, return void;
 */
function render_element($view,$data=FALSE,$return=FALSE,$use_ext=FALSE)
{
	$CI = &get_instance();
	$loadpath = $viewpath = $view;
	
	$request_ext = '';
	
	if($use_ext === ''){
		$request_ext = '';
	}elseif($use_ext === FALSE){
		if($CI->uri->extension() != '' && $CI->uri->extension() != 'html')
			$request_ext = '.'.$CI->uri->extension() ;
	}else{
		$request_ext = '.'.$use_ext;
	}
	
	$loadpath = $viewpath.$request_ext;
	if($request_ext!='') $loadpath.= EXT;
	
	//log_message('debug','RenderElement('.$view.'/'.($use_ext === '' ? "''" : '').'/'.($use_ext === FALSE ? "FALSE" : '').'): '.$loadpath);
	
	return $CI->load->view($loadpath,$data,$return);
}

function render($view,$layout=false,$return=false)
{
	$CI = &get_instance();
	
	$vals = array();
	
	// prepare data for layout
	$params = $CI->params;
	$params = array_merge($params,$vals);
	$CI->load->params = $params;
	
	$content = render_element($view,$params,TRUE);
	$vals['mainContent'] = $content;
	
	if(!$layout)
		$layout = $CI-> render ->layout;
	
	if(!empty($CI-> render ->og)){
		foreach($CI-> render ->og as $key => $val){
			$vals['og'][$key] = $val;	
		}
	}
	
	// prepare data for mainContent
	$params = $CI->params;
	$params = array_merge($params,$vals);
	
	//$CI->load->params = $vals;
	// render full page
	$content = render_element('layouts/'.$layout,$params,$return);
	
	if($return) return $content;
	print $content;
}

function render_mail($view,$data=false,$layout = false)
{
	$CI = &get_instance();
	
	
	$content = render_element($view,$data,TRUE,'');
	$params['mainContent'] = $content;
	
	if(!$layout)
		$layout = 'mail';
	
	$content = render_element('layouts/'.$layout,$params,true,'');
	
	return $content;
}
