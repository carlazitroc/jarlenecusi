<?php

$_value = isset($value) ? $value : '';
$_url = !empty($url) ? $url : '';
$_field_name = isset($field_name) ? $field_name : '';
$_label = isset($label) ? $label : '';
$_row_url = !empty($row_url) ? $row_url : $_url.'/{val}.json';
$_row_label = isset($row_label) ? $row_label : '{title}';

$_hash = $field_name.'_'.md5(rand(0,10000).'-'.time());


//$this -> asset -> js_import(base_url("assets/libs/core/hc-full.min.js"),NULL,'body_foot');
$this -> asset -> js_import(site_url('res/widgets/form_simple_selector.js'),null,'body_foot');

?>

<div class="dmr-selector"
	dmr-selector="<?php echo $_hash?>" 
	dmr-selector-field="<?php echo $_field_name?>" 
	dmr-selector-row-label="<?php echo $_row_label?>" 
	dmr-selector-url="<?php echo $_url?>" 
	dmr-selector-row-url="<?php echo $_row_url?>"
	>
	<input dmr-selector-input type="hidden" name="<?php echo $_field_name?>" value="<?php echo $_value?>" />
	<div class="input-group">
		
		<div dmr-selector-label class="form-control" readonly=""></div>
		<div class="input-group-btn">
			<button type="button" dmr-selector-btn="select" class="btn btn-default"><i class="fa fa-chain"></i> <span class="sr-only"><?php echo lang('button_select')?></span></button>
			<button type="button" dmr-selector-btn="clear" class="btn btn-danger" disabled=""><i class="fa fa-times"></i></button>
		</div>
	</div>
</div>

<?php
unset($value);

unset($url); unset($field_name); unset($row_url); unset($label); unset($row_label); unset($_on_preview_load);