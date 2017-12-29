<?php

$this -> asset -> css(base_url('assets/css/ifuploader.min.css'));
//$this -> asset -> js_import(base_url("assets/libs/core/ifuploader.min.js"),NULL,'body_foot');

$this -> asset -> js_import(base_url('assets/js/jquery.filedrop.js'),NULL,'body_foot');
$this -> asset -> js_import(site_url('res/widgets/form_gallery_editor.js'),null,'body_foot');

$_field_name = isset($field_name) ? $field_name : '';
$_value = isset($value) ? $value : '';


$_attrs = array();
$_attrs['dynamotor-gallery-editor-input'] = (!isset($no_id)) ? $element_id : '';
$_attrs['value'] = $_value;
$_attrs['name'] = $_field_name;
$_attrs['type'] = 'hidden';

if(!empty($attributes)){
	if(is_array($attributes)){
		foreach($attributes as $attr_key => $attr_val){
			$_attrs[ $attr_key ] = $attr_val;
		}
	}
}

//$this->asset->js_embed($widget_path.'form_gallery_editor.js.php',compact('element_id', '_field_name','element_id','_value'),NULL,null,'body_foot');
?>

<div dynamotor-gallery-editor="<?php echo $_attrs['dynamotor-gallery-editor-input']?>">
<?php echo form_input($_attrs);?>

<div class="padd-y visible-changed hidden">
<div class="btn-group">
<button class="btn btn-success" type="button" hc-gallery-action="save"><i class="fa fa-check"></i> <?php echo lang('button_confirm');?></button>
<button class="btn btn-danger" type="button" hc-gallery-action="reset"><i class="fa fa-times"></i> <?php echo lang('button_cancel');?></button>
</div>
</div>

<p>
Upload your disk's files into below or 
<span class="btn btn-default" type="button" hc-gallery-action="import">import server files</span>.
</p>

<div dynamotor-gallery-editor-elm="upload-message" class="alert alert-warning hidden">
<i class="fa fa-spin fa-fw fa-spinner"></i> File(s) are uploading, please do not leave this page...
</div>


<div class="panel panel-default">
<div class="panel-body">
<div class="filedrop">
<ul class="cell-list picture-list selectable ">
</ul>
</div>
</div>
</div>



<script hc-template="<?php echo $element_id?>" type="text/template">

<li class="cell item cell-sm col-xs-6 col-sm-4 col-md-3 col-lg-2">
	<div class="body">
	<div class="cover">
	<img src="<?php echo spacer_url('img/spacer.gif')?>" width="100%" rc-background-image="hc.net.site_url('file/'+row.file_id+'/picture?width=200&height=200&crop=true&scale=fill')" />
	</div>
	<div class="actions">
<div class="btn-group"><span hc-action="remove" class="btn btn-sm btn-danger"><i class="fa fa-fw fa-times"></i></span><span hc-action="edit" class="btn btn-sm btn-default"><i class="fa fa-fw fa-cogs"></i></span></div>
	

	</div>
</div>
</li>

</script>


<div hc-modal="slide-editor" class="modal" role="modal">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-header">
<?php if(isset($localized) && $localized){ ?>
<!-- BEGIN : Localized -->
<div class="btn-group pull-right">
	<button type="button" class="btn btn-change-locale btn-md btn-default dropdown-toggle" data-toggle="dropdown"><i class="fa fa-language"></i> <span class="loc-name"><?php echo lang('lang_'.$this->lang->locale())?></span> <span class="caret"></span></button>
	<ul class="dropdown-menu" role="dropdown">
<?php foreach($this->lang->get_available_locale_keys() as $loc_code){?>
		<li><a href="#loc-<?php echo $loc_code?>" re-action="change-locale" re-locale-name="<?php echo lang('lang_'.$loc_code)?>" re-locale="<?php echo $loc_code?>"><?php echo lang('lang_'.$loc_code)?></a></li>
<?php } ?>
	</ul>
</div>
<!-- END : Localized -->
<?php } ?>

<h4>Slide</h4>
</div>
<div class="modal-body">


<div class="row">
<div class="col-md-3">
<div class="form-group">
	<label class="control-label">Default Image</label>
<?php
	$this->widget('form_upload', array(
		'field_name' => '',
		'is_image'   => TRUE,
		'value'=> '',
		'attributes'=>array(
			'hc-value'=>'file_id',
		),
	));
?>
</div>
</div>

<div class="col-md-9">


<?php if(isset($localized) && $localized){ ?>
<div class="tab-content">

<?php foreach($this->lang->get_available_locale_keys() as $loc_code){?>
<div class="tab-pane fade<?php if($loc_code == $this->lang->locale()) echo' active in';?>" re-locale="<?php echo $loc_code?>">
	<div class="form-group">
	<label class="control-label">Localized Image [<?php echo lang('lang_'.$loc_code)?>] <i class="fa fa-language"></i></label>
<?php
	$this->widget('form_upload', array(
		'field_name' => '',
		'is_image'   => TRUE,
		'value'=> '',
		'attributes'=>array(
			'hgc-value'=>'file_id',
			'hgc-locale'=>$loc_code,
		),
	));
?>
	</div>

	<div class="form-group hidden">
		<input type="text" hgc-value="title" hgc-locale="<?php echo $loc_code?>" class="form-control" placeholder="<?php echo lang('field_title')?>" />
	</div>

	<div class="form-group hidden">
		<input type="text" hgc-value="url" hgc-locale="<?php echo $loc_code?>" class="form-control" placeholder="<?php echo lang('field_url')?>" />
	</div>

	<div class="form-group hidden">
		<textarea type="text" hgc-value="description" hgc-locale="<?php echo $loc_code?>" class="form-control" placeholder="<?php echo lang('field_description')?>"></textarea>
	</div>

</div>
<?php } ?>
</div>
<?php } else  { ?>
	<div class="form-group hidden">
		<input type="text" hgc-value="title" class="form-control" placeholder="<?php echo lang('field_title')?>" />
	</div>

	<div class="form-group hidden">
		<input type="text" hgc-value="url" class="form-control" placeholder="<?php echo lang('field_url')?>" />
	</div>

	<div class="form-group hidden">
		<textarea type="text" hgc-value="description" class="form-control" placeholder="<?php echo lang('field_description')?>"></textarea>
	</div>
<?php } ?>

</div>
</div>

</div>
<div class="modal-footer">
<button class="btn btn-default" hgc-action="cancel" type="button"><i class="fa fa-times"></i> <?php echo lang('button_cancel')?></button>
<button class="btn btn-success" hgc-action="done" type="button"><i class="fa fa-check"></i> <?php echo lang('button_done')?></button>
</div>
</div>
</div>
</div>

</div>