<?php


$this->asset->js_import(base_url('assets/libs/jquery-file-upload/js/jquery.fileupload.js'));
$this->asset->css(base_url('assets/libs/jquery-file-upload/css/jquery.fileupload.css'));
$this->asset->css(base_url('assets/libs/jquery-file-upload/css/jquery.fileupload-ui.css'));

if(!isset($url)) $url = site_url('file/temp/upload'); 
$this->asset->js_embed($widget_path.'form_temp_upload.js.php', compact('element_id','url','name','callback'),null,'body_foot');

if(empty($name))
	$name = 'new_file';

if(empty($label))
	$label = lang('upload');

$attributes['hc-elm'] = $element_id;
if(!isset($attributes['class']))
	$attributes['class'] = 'tempuploader';

?>

<div <?php echo array_to_html_attribute($attributes)?>>
<span class="btn btn-success fileinput-button">
<?php if(!empty($icon) && is_string($icon)):?>
    <i class="<?php echo $icon?>"></i>
<?php endif; ?>
<?php if(!empty($label)):?>
    <span><?php echo $label?></span>
<?php endif; ?>
    <input type="file" name="<?php echo $name?>" />
</span>

<span class="fileupload-process"></span>

</div>