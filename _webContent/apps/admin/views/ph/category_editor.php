<?php

$this -> title[] = lang($section.'_heading');
$this -> title[] = lang('category_heading');

$this -> asset -> js_embed('ph/category_editor.js.php',NULL,NULL,'body_foot');

if (isset($record['id'])) {
	$this -> title[] = empty($record['title']) ? $record['id'] : $record['title'];
} else {
	$this -> title[] = lang('create');
}
?>

<?php if($is_dialog){ ?>
<div class="modal-header">
	<h4><?php echo lang('category_heading');?></h4>
</div>

<div class="modal-footer float">
	<button type="button" class="btn btn-window-close right btn-default"><i class="fa fa-times"></i> <?php echo lang('button_close')?></button>
	<button type="button" class="btn btn-success" re-action="save"><i class="fa fa-save"></i> <?php echo lang('button_save')?></button>
	<button type="button" class="btn btn-primary hidden visible-edit" re-action="publish"><i class="fa fa-cloud-upload"></i> <?php echo lang('button_publish')?></button>
</div>
<div class="modal-body">
<?php } ?>

<form action="<?php echo site_url('s/'.$section.'/category/save')?>" class="ifrm form" method="post" enctype="multipart/form-data">
	<input type="hidden" name="id" readonly="readonly" value="<?php if(!empty($id)) echo $id?>" />
	<input type="hidden" name="forward" value="<?php echo forward_url()?>" />
	<input type="hidden" name="do" value="save" />

<div class="row">
<?php if(!$is_dialog){ ?>
	<div class="col-xs-12">
		<div class="page-header">
			<div class="toolbar">
<?php if (forward_url() != '') {?>
				<a class="btn btn-link" href="<?php echo forward_url()?>"><i class="fa fa-times"></i> <?php echo lang('button_discard_changes')?></a>
<?php } else {?>
				<a class="btn btn-link" href="<?php echo site_url('s/' . $section . '/category')?>"><i class="fa fa-arrow-left"></i> <?php echo lang('button_list')?></a>
<?php }?>
			</div>
			<div class="pull-right">
			<div class="btn-group">
		<button type="button" class="btn btn-success" re-action="save"><i class="fa fa-save"></i> <?php echo lang('button_save')?></button>
		<button type="button" class="btn btn-primary hidden visible-edit" re-action="publish"><i class="fa fa-cloud-upload"></i> <?php echo lang('button_publish')?></button>
	</div>
		<a href="<?php echo site_url('s/'.$section.'/category/add')?>" class="btn btn-default hidden visible-edit"><i class="fa fa-plus"></i> <?php echo lang('button_add')?></a>
	
<?php if(forward_url() !=''){ ?>
				<a class="btn btn-warning btn-link" href="<?php echo forward_url()?>"><?php echo lang('button_discard_changes')?></a>
<?php } ?>
			</div>
			<h2><?php echo lang('category_heading');?></h2>
		</div>
	</div>
<?php }?>
		
	<div class="col-sm-12">	
	<?php echo lang('form_required_field')?>
	</div>

	<div class="col-lg-9 col-md-8 col-sm-12 col-xs-12">

<?php if($this->ph->is_locale_enabled){?>
		<!-- BEGIN : Localized -->
		<div class="btn-group">
			<button type="button" class="btn btn-change-locale btn-md btn-default dropdown-toggle" data-toggle="dropdown"><i class="fa fa-language"></i> <span class="loc-name"><?php echo lang('lang_'.$this->lang->locale())?></span> <span class="caret"></span></button>
			<ul class="dropdown-menu" role="dropdown">
<?php foreach($this->lang->get_available_locale_keys() as $loc_code){?>
				<li><a href="#loc-<?php echo $loc_code?>" re-action="change-locale" re-locale-name="<?php echo lang('lang_'.$loc_code)?>" re-locale="<?php echo $loc_code?>"><?php echo lang('lang_'.$loc_code)?></a></li>
<?php } ?>
			</ul>
		</div>
		<!-- END : Localized -->
<?php } ?>

		
<?php if($this->ph->is_locale_enabled){?>
		<div class="tab-content">
<?php foreach($this->lang->get_available_locale_keys() as $loc_code){?>
	  		<div class="tab-pane fade<?php if($loc_code == $this->lang->locale()) echo' active in';?>" re-locale="<?php echo $loc_code?>">
				
				<div class="form-group radio">
					<label><input type="radio" name="default_locale" value="<?php echo $loc_code?>" <?php if(data('default_locale',$data) == $loc_code) echo ' checked="checked"'?> /> This is default content.</label>
				</div>

				<div class="form-group">
					<label class="control-label"><?php echo lang('field_title')?> *</label>
					<input type="text" name="loc[<?php echo $loc_code?>][title]" re-required class="form-control" value="<?php if(!empty($loc[$loc_code]['title'])) echo $loc[$loc_code]['title']?>" />
					<hr class="clear" />
				</div>
				<div class="form-group">
					<label class="control-label"><?php echo lang('field_description')?></label>
					<div class="help-block"><?php echo lang('field_description_description');?></div>
					<input type="text" name="loc[<?php echo $loc_code?>][description]" maxlength="255" class="form-control col-md-6" value="<?php if(!empty($loc[$loc_code]['description'])) echo $loc[$loc_code]['description']?>" />
					
					<hr class="clear" />
				</div>
			</div>
<?php } ?>
		</div>
<?php } ?>



<?php if(!$this->ph->is_locale_enabled){?>
		<div class="form-group">
			<label class="control-label"><?php echo lang('field_title')?> *</label>
			<input type="text" name="title" re-required class="form-control col-md-6" value="<?php echo $data['title']?>" />
			<hr class="clear" />
		</div>
		<div class="form-group">
			<label class="control-label"><?php echo lang('field_description')?></label>
			<div class="help-block"><?php echo lang('field_description_description');?></div>
			<input type="text" name="description" maxlength="255" class="form-control col-md-6" value="<?php echo $data['description']?>" />
			<hr class="clear" />
		</div>
<?php } ?>
		<div class="form-group">
			<label class="control-label"><?php echo lang('field_slug')?></label>
			<div class="help-block"><?php echo lang('field_slug_description')?></div>
			<div class="input-group">
				<div class="input-group-addon"><?php echo (!$this->ph->is_default  ? $section.'/':'/').'category' ?></div>
				<input name="slug" type="text" class="form-control col-md-6" value="<?php echo $data['slug']?>" placeholder="<?php if(empty($data['slug'])) echo $id;?>" maxlength="150" /> 
			</div>
			<hr class="clear" />
		</div>
		

		

		<hr class="clear" />



		<?php $this->view('ph/form_parameters') ?>

	</div><!--/col-->

	<div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
		
		
		<div class="box">
			<div class="box-header">
				<h2><i class="fa fa-inbox"></i><?php echo lang('meta')?></h2>
				<div class="box-icon">
					<a href="#" class="btn-minimize"><i class="fa fa-chevron-up"></i></a>
				</div>
			</div>
			<div class="box-content">

				<div class="form-group">
					<label class="control-label"><?php echo lang('field_parent_id')?></label>			


<?php echo chained_combobox('parent_id', $categories, data('parent_id',$data), array(
			'root'=>'Root',
			'startLevel'=>1,
			'attribute'=>' class="form-control"',
			)); ?>

				</div>
		

				<div class="form-group">
					<label class="control-label"><?php echo lang('field_priority')?></label>
					<div class="help-block"><?php echo lang('field_priority_description')?></div>
					<input name="priority" type="number" class="form-control col-sm-2" value="<?php echo $data['priority']?>" maxlength="5" /> 
				</div>

				<div class="form-group">
					<label class="control-label"><?php echo lang('field_publish_date')?></label>
					<div class="help-block"><?php echo lang('field_publish_date_description')?></div>
					<input name="publish_date" type="text" class="form-control col-sm-2 date-picker" value="<?php echo substr($data['publish_date'],0,10);?>" maxlength="10" /> 
				</div>



				<div class="form-group">
					<label class="control-label"><?php echo lang('field_status')?> *</label>
					<div class="input-group">
						<label for="cb-status-0">
						<input name="status" type="radio" id="cb-status-0" value="0"<?php if($data['status']=='0') print ' checked="checked"'?> /> <?php echo lang('status_0')?></label> <br />
						<label for="cb-status-1">
						<input name="status" type="radio" id="cb-status-1" value="1"<?php if($data['status']=='1') print ' checked="checked"'?> /> <?php echo lang('status_1')?></label> <br />
						<label for="cb-status-2">
						<input name="status" type="radio" id="cb-status-2" value="2"<?php if($data['status']=='2') print ' checked="checked"'?> /> <?php echo lang('status_2')?></label>
					</div>
				</div>
				<hr class="clear" />
			</div>
		</div>
			
	</div><!--/col-->
	
</div><!--/row-->
</form>

<?php if($is_dialog){?>
</div>
<?php } ?>