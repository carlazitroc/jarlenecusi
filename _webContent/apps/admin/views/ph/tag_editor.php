<?php

$this -> title[] = lang($section.'_heading');
$this -> title[] = lang('tag_heading');

$this -> asset -> js_embed('ph/tag_editor.js.php',NULL,NULL,'body_foot');

if (isset($record['id'])) {
	$this -> title[] = empty($record['title']) ? $record['id'] : $record['title'];
} else {
	$this -> title[] = lang('create');
}
?>

<?php if($is_dialog){ ?>
<div class="modal-header">
	<h4><?php echo lang('tag_heading');?></h4>
</div>

<div class="modal-footer float">
	<button type="button" class="btn btn-default btn-window-close right"><i class="fa fa-times"></i> <?php echo lang('button_close')?></button>
		<button type="button" class="btn btn-success" re-action="save"><i class="fa fa-save"></i> <?php echo lang('button_save')?></button>
		<button type="button" class="btn btn-primary hidden visible-edit" re-action="publish"><i class="fa-cloud-up"></i> <?php echo lang('button_publish')?></button>
	<a href="<?php echo site_url('s/'.$section.'/tag/add')?>" class="btn btn-primary btn-add<?php if(empty($record_id)) echo " hidden"?>"><i class="fa fa-plus"></i> <?php echo lang('button_add')?></a>
</div>
<div class="modal-body">
<?php } ?>



<form action="<?php echo site_url('s/'.$section.'/tag/save')?>" class="ifrm form" method="post" enctype="multipart/form-data">
	<input type="hidden" name="id" readonly="readonly" value="<?php if(!empty($id)) echo $id?>" />
	<input type="hidden" name="forward" value="<?php echo forward_url()?>" />
	<input type="hidden" name="do" value="save" />

		
<div class="row">
<?php if(!$is_dialog){ ?>
	<div class="col-xs-12">
		<div class="page-header">
<?php if(!$is_dialog){ ?>	
<div class="pull-right">
		<button type="button" class="btn btn-success" re-action="save"><i class="fa fa-save"></i> <?php echo lang('button_save')?></button>
		<button type="button" class="btn btn-primary hidden visible-edit" re-action="publish"><i class="fa-cloud-up"></i> <?php echo lang('button_publish')?></button>
	
<?php if(forward_url() !=''){ ?>
	<a class="btn btn-warning btn-link" href="<?php echo forward_url()?>"><?php echo lang('button_discard_changes')?></a>
<?php } ?>
		
		
</div>
<?php } ?>
			<h2><?php echo lang('tag_heading');?></h2>
		</div>
	</div>
<?php }?>
	
	<div class="col-sm-12">	
	<?php echo lang('form_required_field')?>
	</div>

	<div class="col-lg-8 col-md-8 col-sm-12">


		<div class="form-group">
			<label class="control-label"><?php echo lang('field_title')?> *</label>
			<input type="text" name="title" re-required class="form-control col-md-6" value="<?php echo $data['title']?>" />
		</div>
		<div class="form-group">
			<label class="control-label"><?php echo lang('field_slug')?></label>
			<div class="input-group">
				<div class="input-group-addon"><?php echo (!$this->ph->is_default  ? $section.'/':'/').'tags' ?></div>
				<input name="slug" type="text" class="form-control col-md-6" value="<?php echo $data['slug']?>" placeholder="<?php if(empty($data['slug'])) echo $id;?>" maxlength="150" /> 
			</div>
			<p><?php echo lang('field_slug_description')?></p>
		</div>

		<hr class="clear" />

<?php $this->view('ph/form_parameters') ?>

	</div><!--/col-->

	<div class="col-lg-4 col-md-4 col-sm-12">
		<div class="box">
			<div class="box-header">
				<h2><i class="fa fa-inbox"></i><?php echo lang('meta')?></h2>
				<div class="box-icon">
					<a href="#" class="btn-minimize"><i class="fa fa-chevron-up"></i></a>
				</div>
			</div>
			<div class="box-content">
				<fieldset class="col-sm-12">

					<div class="form-group">
						<label class="control-label"><?php echo lang('field_publish_date')?></label>
						<input name="publish_date" type="text" class="form-control col-sm-2 date-picker" value="<?php echo substr($data['publish_date'],0,10);?>" maxlength="10" /> 
						<p><?php echo lang('field_publish_date_description')?></p>
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

					

				</fieldset>


				<hr class="clear" />


			</div>
		</div>
		
						
					
	</div><!--/col-->
	
</div><!--/row-->


</form>

<?php if($is_dialog){?>
</div>
<?php } ?>