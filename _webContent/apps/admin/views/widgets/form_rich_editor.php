<?php


//$this->asset->js_import(base_url('assets/libs/core/selector.js'),null,'body_foot');
$this->asset->js_import(base_url('assets/libs/tinymce/tinymce.min.js'),null,'body_foot');

$this -> asset -> js_import(site_url('res/widgets/form_rich_editor.js'),null,'body_foot');
//$this-> asset -> js_embed($widget_path.'form_rich_editor.js.php',compact('element_id'),null,'body_foot');

$_field_name = isset($field_name) ? $field_name : '';

$_attrs = array();
$_attrs['dynamotor-rich-editor'] = (!isset($no_id)) ? $element_id : '';
$_attrs['name'] = $_field_name;
$_attrs['rows'] = 10;

$_css_class = array();
$_css_class[] = 'form-control';
//$_css_class[] = 'rich-editor';

if(!empty($css_class)){
	if(is_array($css_class)){
		foreach($css_class as $css_class_name){
			$_css_class[] = $css_class_name;
		}
	}
}


if(isset($attributes)){
	if(is_array($attributes)){
		foreach($attributes as $attr_key => $attr_val){
			$_attrs[ $attr_key ] = $attr_val;
		}
	}
}

$_attrs['class'] = implode(' ', $_css_class);

$_attrs_str = '';
foreach($_attrs as $attr_key => $attr_val){
	if(!empty($attr_val)){
		$_attrs_str.=' '.$attr_key.'="'.$attr_val.'"';
	}else{
		$_attrs_str.=' '.$attr_key;
	}
}

$_tag_name = 'textarea';
echo '<div dynamotor-rich-editor="'.$_attrs['dynamotor-rich-editor'].'">';
echo '<'.$_tag_name.$_attrs_str.'>';
if(!empty($value)) echo $value;
echo '</'.$_tag_name.'>';
if(!isset($no_id) && isset($config) && !empty($config)){
echo '<script>';
echo '$(\'textarea[dynamotor-rich-editor='.$element_id.']\').data(\'config\','.json_encode($config).');';
echo '</script>';
}
echo '</div>';

unset($attributes); unset($css_class); unset($value); 