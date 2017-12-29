<?php

$this -> title[] = $page_header;

$this -> asset -> js_embed($theme_path.'core/post_editor.js.php',NULL,NULL,'body_foot');

if (isset($record['id'])) {
	$this -> title[] = empty($record['title']) ? $record['id'] : $record['title'];
} else {
	$this -> title[] = lang('create');
}
?>
 
<?php if ($is_dialog) {?>
<div class="modal-header">
	<h4><?php echo $page_header;?></h4>
</div>

<div class="modal-footer float">
	<button type="button" class="btn btn-default btn-window-close right"><i class="fa fa-times"></i> <?php echo lang('button_close')?></button>
	<button type="button" class="btn btn-success" re-action="save"><i class="fa fa-save"></i> <?php echo lang('button_save')?></button>
<?php if($staging_enabled):?>
	<button type="button" class="btn btn-primary hidden visible-edit" re-action="publish"><i class="fa fa-check"></i> <?php echo lang('button_publish')?></button>
<?php endif;?>
</div>
<div class="modal-body">
<?php }?>

<form action="<?php echo ($endpoint_url_prefix. '/save')?>" class="ifrm form" method="post" enctype="multipart/form-data">
<input type="hidden" name="id" readonly="readonly" value="<?php if (!empty($id)) echo $id?>" />
<input type="hidden" name="forward" value="<?php echo forward_url()?>" />


<div class="row">

<?php if (!$is_dialog) {?>
	<div class="col-xs-12">
		<div class="page-header">
			<h2><?php echo $page_header;?></h2>
			<div class="toolbar" style="float:none">
<?php if (forward_url() != '') {?>
				<a class="btn btn-link" href="<?php echo forward_url()?>"><i class="fa fa-times"></i> <?php echo lang('button_discard_changes')?></a>
<?php } else {?>
				<a class="btn btn-link" href="<?php echo ($endpoint_url_prefix)?>"><i class="fa fa-arrow-left"></i> <?php echo lang('button_list')?></a>
<?php }?>
				<button type="button" class="btn btn-success" re-action="save"><i class="fa fa-save"></i> <?php echo lang('button_save')?></button>
<?php if($staging_enabled):?>
		<button type="button" class="btn btn-primary hidden visible-edit" re-action="publish"><i class="fa fa-check"></i> <?php echo lang('button_publish')?></button>
<?php endif;?>

<?php if($clone_enabled):?>
				<a class="btn btn-info btn-clone hidden visible-edit"><i class="fa fa-copy"></i> <?php echo lang('button_clone')?></a>
<?php endif;?>	
	<a href="<?php echo $endpoint_url_prefix.'/add'?>" class="btn btn-info hidden visible-edit"><i class="fa fa-plus"></i> <?php echo lang('button_add')?></a>
			</div>
			<div class=" clearfix"></div>
		</div>
	</div>
<?php }?>
	<div class="col-xs-12"><?php echo lang('form_required_field')?></div>
	<div class="col-xs-12">

	<?php /* <pre><?php var_dump(compact('data','loc'))?></pre> **/ ?>
<?php

if(!isset($editor_sections)){
	$editor_sections = array(
	);
	foreach($editable_fields_details as $field_name => $field_info){
		if(is_array($editable_fields) && !in_array($field_name, $editable_fields)) continue;

		$section = !empty($field_info['section']) ? $field_info['section'] : 'general';
		if(empty($editor_sections[ $section ])){
			$editor_sections[ $section ] = array('title'=> lang('section_'.$section),'fields'=>array());
		}
		$name = !empty($field_info['name']) ? $field_info['name'] : $field_name;
		$label = !empty($field_info['label']) ? $field_info['label'] : lang('field_'.$name);
		$default_value = !empty($field_info['default_value']) ? $field_info['default_value'] : data('default',$field_info);
		$field_info['name'] = $name;
		$field_info['label'] = $label;
		$field_info['default'] = $default_value;

		if(!empty($field_info['url'])){
			$field_info['url'] = site_url($field_info['url']);
		}

		$editor_sections[ $section ]['fields'][] = $field_info;
	}
}
$this->widget('form_editor',compact('loc','data','record','editor_sections'))
?>
	</div>
	
</div><!--/row-->


</form>

<?php if($is_dialog){?>
</div>
<?php } ?>