<?php 

$this->asset->js_embed($widget_path.'form_chosen.js.php',compact('element_id','row_value','row_label','row_parent'),null,'body_foot');

$_value = ''; 
if(isset($value))
	 $_value = is_array($value) ? implode(',',$value) : $value;

$options =  array(
	'multiple' => isset($multiple) && $multiple,
	'attribute'=>array(
		'hc-elm'=>$element_id,
		'hc-selected'=> $_value,
		'hc-remote'=>$remote_url, 
		'class'=>'form-control chosen'
	),
);
if(!empty($placeholder))
	$options['attribute']['data-placeholder'] = $placeholder;

if(!isset($field_name)) $field_name = '';
if(!empty($attributes)){
	foreach($attributes as $_key => $_val)
		$options['attribute'][$_key] = $_val;
}


echo chained_combobox($field_name, array(), $_value, $options);
