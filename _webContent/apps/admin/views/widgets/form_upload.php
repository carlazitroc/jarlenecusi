<?php

$this -> asset -> css(base_url('assets/css/ifuploader.min.css'));
//$this -> asset -> js_import(base_url('assets/libs/core/ifuploader.min.js'),NULL,'body_foot');


$this -> asset -> js_import(base_url('assets/js/jquery.filedrop.js'),NULL,'body_foot');
$this -> asset -> js_import(site_url('res/widgets/form_upload.js'),NULL,'body_foot');

$_field_name = isset($field_name) ? $field_name : '';

$_attrs = isset($attributes) && !empty($attributes) ? $attributes : '';
$_is_image = isset($is_image) && $is_image;
$_has_croparea = isset($has_croparea) && $has_croparea;
$selector = $_is_image ? 'image' : 'all';
if(isset($select_type)) $selector = $select_type;

$_full_ui = !isset($full_ui) || $full_ui == true;


$_attrs_str = is_string($_attrs)? $_attrs : '';

$_show_label = !isset($show_label) || $show_label !== FALSE;

if(is_array($_attrs)){
	$_attrs_str = '';
	foreach($_attrs as $name => $val){
		if($name == 'name'){
			$_field_name = $val;
		}else{

			$_attrs_str.=' '.$name.'="'.$val.'"';
		}
	}
}

$_value = '';
if(isset($value))
	$_value = $value;
if(isset($data[$_field_name]))
	$_value = $data[$_field_name];

if(!isset($crop_field_name)) $crop_field_name = '';
if(!isset($crop_width)) $crop_width = 0;
if(!isset($crop_height)) $crop_height = 0;
if(!isset($has_croparea)) $has_croparea = FALSE;
if(!isset($params)) $params = NULL;

//$this->asset->js_embed($widget_path.'form_upload.js.php',compact('_full_ui','_is_image','field_name','_has_croparea','selector','element_id','params','has_croparea','select_type','crop_field_name','crop_width','crop_height'),null,'body_foot');
?>

<div dynamotor-uploader="<?php if(!isset($no_id)) echo $element_id?>" class="hc-uploader" 
data-image="<?php echo json_encode($_is_image ? true:false)?>" 
data-crop-width="<?php echo json_encode($crop_width)?>" 
data-crop-height="<?php echo json_encode($crop_height)?>"
data-crop-field-name="<?php echo json_encode($crop_field_name)?>"
data-crop="<?php echo json_encode($has_croparea ? true : false)?>"
data-params="<?php echo json_encode($params)?>"
>

<?php if($_full_ui){?>

<?php if($_is_image){?>
<div class="preview-box pb-image auto-height empty filedrop" style="max-width:250px;">
<img src="<?php echo base_url('assets/img/spacer.gif')?>" class="spacer" />
<div class="body" style="background-repeat:no-repeat;"></div>
<span class="remark empty"><?php echo lang('no_selected_image')?></span>
</div>
<?php }else{?>
<div class="preview-box pb-text empty">
<div class="body" style="background-repeat:no-repeat;"></div>
<span class="remark empty"><?php echo lang('no_selected_file')?></span>
</div>
<?php } ?>


<div class="input-group">
<?php if($_is_image && $_has_croparea){ ?>
<input type="hidden" name="<?php echo $crop_field_name?>" hc-uploader-croparea value="<?php echo $_value ?>" />
<?php }?>
<input type="hidden" name="<?php echo $_field_name?>" hc-uploader-value value="<?php echo $_value ?>"<?php echo $_attrs_str?> />

<div class="btn-group">
<button data-action="cancel" style="margin-right:5px;" type="button" class="btn btn-danger" title="<?php echo lang('cancel')?>"><i class="fa fa-times"></i></button>
</div>

<div class="btn-group">
<?php if($_is_image){ ?>
<button data-action="selector" type="button" class="btn btn-default" title="<?php echo lang('select')?>"><i class="fa fa-cloud-download"></i></button>
<?php }else{ ?>
<button data-action="selector" type="button" class="btn btn-default" title="<?php echo lang('select')?>"><i class="fa fa-cloud-download"></i></button>
<?php } ?>

<div class="ifupr-file-wrap">

<?php if($_is_image){ ?>
<button data-action="select-file" type="button" class="ifupr-file-btn btn btn-default" title="<?php echo lang('upload')?>"><i class="fa fa-upload"></i></button>
<?php }else{ ?>
<button data-action="select-file" type="button" class="ifupr-file-btn btn btn-default" title="<?php echo lang('upload')?>"><i class="fa fa-upload"></i></button>
<?php } ?>
<input data-upload="instant" type="file" class="ifupr-file" />
</div>
</div>
<?php if($_is_image && $_has_croparea){ ?>
<div class="btn-group" style="margin-left:5px;">
<button data-action="croparea" type="button" class="btn btn-default btn-croparea"><i class="fa fa-crop"></i> <?php echo lang('button_crop')?></button>
</div>
<?php }?>
</div>


<?php }else{ ?>
<input type="hidden" name="<?php echo $_field_name?>" value="<?php echo isset($data[$field_name]) ? $data[$field_name]  :'' ?>"<?php echo $_attrs_str?> />

<?php } ?>


<div dynamotor-uploader-elm="upload-message" class="alert alert-warning hidden">
<i class="fa fa-spin fa-fw fa-spinner"></i> File is uploading, please do not leave this page...
</div>

</div>
<?php

if(isset($is_image)) unset($is_image);
if(isset($_field_name)) unset($_field_name); 
if(isset($select_type)) unset($select_type);
if(isset($params)) unset($params);
if(isset($has_croparea)) unset($has_croparea);
if(isset($field_name)) unset($field_name);
if(isset($crop_field_name)) unset($crop_field_name);
if(isset($crop_width)) unset($crop_width);
if(isset($crop_height)) unset($crop_height);
if(isset($full_ui)) unset($full_ui);
if(isset($show_label)) unset($show_label);
