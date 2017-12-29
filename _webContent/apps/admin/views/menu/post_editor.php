<?php

$this -> title[] = $page_header;

$this -> asset -> js_embed($view_path_prefix.'_editor.js.php',NULL,NULL,'body_foot');



if (isset($record['id'])) {
	$this -> title[] = empty($record['title']) ? $record['id'] : $record['title'];
} else {
	$this -> title[] = lang('create');
}
?>
<?php if($is_dialog){ ?>
<div class="modal-header">
	<h4><?php echo $page_header;?></h4>
</div>

<div class="modal-footer float">
	<button type="button" class="btn btn-default btn-window-close right"><i class="fa fa-times"></i> <?php echo lang('button_close')?></button>
	<button type="button" class="btn btn-success" re-action="save"><i class="fa fa-save"></i> <?php echo lang('button_save')?></button>
	<button type="button" class="btn btn-primary hidden visible-edit" re-action="publish"><i class="fa fa-cloud-upload"></i> <?php echo lang('button_publish')?></button>
</div>
<div class="modal-body">
<?php } ?>



<form action="<?php echo ($endpoint_url_prefix.'/save')?>" class="ifrm form-horizontal" method="post" enctype="multipart/form-data">

<div class="row">
<?php if(!$is_dialog){ ?>
	<div class="col-xs-12">
		<div class="page-header">
			<div class="pull-right">
				<button type="button" class="btn btn-primary" re-action="save"><i class="fa fa-save"></i> <?php echo lang('button_save')?></button>
				<button type="button" class="btn btn-success hidden visible-edit" re-action="publish"><i class="fa fa-cloud-upload"></i> <?php echo lang('button_publish')?></button>
				<a href="<?php echo ($endpoint_url_prefix.'/add')?>" class="btn btn-info btn-add hidden visible-edit"><i class="fa fa-plus"></i> <?php echo lang('button_add')?></a>
<?php if($clone_enabled):?>
				<a class="btn btn-info btn-clone hidden visible-edit"><i class="fa fa-plus"></i> <?php echo lang('button_clone')?></a>
<?php endif;?>	
<?php if (forward_url() != '') {?>
				<a class="btn btn-link" href="<?php echo forward_url()?>"><i class="fa fa-times"></i> <?php echo lang('button_discard_changes')?></a>
<?php } else {?>
				<a class="btn btn-link" href="<?php echo $endpoint_url_prefix?>"><i class="fa fa-arrow-left"></i> <?php echo lang('button_list')?></a>
<?php }?>
			</div>
			<h2><?php echo $page_header;?></h2>
		</div>
	</div>
<?php }?>
		
	<div class="col-sm-12">	
	<?php echo lang('form_required_field')?>
	</div>

	<input type="hidden" name="id" readonly="readonly" value="<?php if(!empty($id)) echo $id?>" />
	<input type="hidden" name="forward" value="<?php echo forward_url()?>" />
	<input type="hidden" name="do" value="save" />

	<div class="col-xs-12">



		<!-- BEGIN : Localized -->
			<div class="form-group">

				<div class="col-md-offset-2 col-sm-offset-3 col-md-10 col-sm-9">
					<div class="btn-group">
						<button type="button" class="btn btn-change-locale btn-md btn-default dropdown-toggle" data-toggle="dropdown"><i class="fa fa-language"></i> <span class="loc-name"><?php echo lang('lang_'.$this->lang->locale())?></span> <span class="caret"></span></button>
						<ul class="dropdown-menu" role="dropdown">
		<?php foreach($this->lang->get_available_locale_keys() as $loc_code){?>
							<li><a href="#loc-<?php echo $loc_code?>" re-action="change-locale" re-locale-name="<?php echo lang('lang_'.$loc_code)?>" re-locale="<?php echo $loc_code?>"><?php echo lang('lang_'.$loc_code)?></a></li>
		<?php } ?>
						</ul>
					</div>
				</div>
			</div>


			<div class="tab-content">
	<?php foreach($this->lang->get_available_locale_keys() as $loc_code){ $loc_data = !empty($loc[$loc_code]) ? $loc[$loc_code] : NULL; ?>
		  		<div class="tab-pane fade<?php if($loc_code == $this->lang->locale()) echo' active in';?>" re-locale="<?php echo $loc_code?>">

					<div class="form-group">
						<div class="col-md-offset-2 col-sm-offset-3 col-md-10 col-sm-9">
							<div class="radio">
								<label><input type="radio" name="default_locale" value="<?php echo $loc_code?>" <?php if(data('default_locale',$data) == $loc_code) echo ' checked="checked"'?> /> This is default content.</label>
							</div>
						</div>
					</div>

					<div class="form-group">
						<label class="control-label col-md-2 col-sm-3"><?php echo lang('field_title')?> *</label>
						<div class="col-md-6 col-sm-6">
							<input type="text" name="loc[<?php echo $loc_code?>][title]" re-required class="form-control" value="<?php if(!empty($loc[$loc_code]['title'])) echo $loc[$loc_code]['title']?>" />
						</div>
					</div>


				</div>
	<?php } ?>
			</div>

			<div class="form-group">
				<label class="control-label col-md-2 col-sm-3"><?php echo lang('field_sys_name')?> *</label>
				<div class="col-md-3 col-sm-4">
				<input re-required type="text" name="sys_name" maxlength="40" class="form-control" value="<?php echo $data['sys_name']?>" />
				</div>
			</div>


			<div class="form-group">
				<label class="control-label col-md-2 col-sm-3"><?php echo lang('field_start_date')?></label>
				<div class="col-md-2 col-sm-3">
					<input name="start_date" type="text" class="form-control datepicker" value="<?php echo substr($data['start_date'],0,10);?>" maxlength="10" /> 
					<p class="help-block"><?php echo lang('field_start_date_description')?></p>

				</div>
			</div>
			
			<div class="form-group ">
				<label class="control-label col-md-2 col-sm-3"><?php echo lang('field_end_date')?></label>
				<div class="col-md-2 col-sm-3">
					<input name="end_date" type="text" class="form-control datepicker" value="<?php echo substr($data['end_date'],0,10);?>" maxlength="10" /> 
					<p class="help-block"><?php echo lang('field_end_date_description')?></p>
				</div>
			</div>


			<div class="form-group">
				<label class="control-label col-md-2 col-sm-3"><?php echo lang('field_status')?> *</label>
				<div class="col-md-10 col-sm-9">
					<div class="radio">
						<label for="cb-status-0">
						<input name="status" type="radio" id="cb-status-0" value="0"<?php if($data['status']=='0') print ' checked="checked"'?> /> <?php echo lang('status_0')?></label> <br />
						<label for="cb-status-1">
						<input name="status" type="radio" id="cb-status-1" value="1"<?php if($data['status']=='1') print ' checked="checked"'?> /> <?php echo lang('status_1')?></label> <br />
					</div>
				</div>
			</div>

		<div class="form-group" id="listgroup-items">
			<label class="control-label col-md-2 col-sm-3"><?php echo lang('items')?> *</label>
			<div class=" col-md-10 col-sm-9">
				<div class="dropdown list-group-add">
					<button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">
					Add Item From Source
					<span class="caret"></span>
					</button>
					<ul class="dropdown-menu" role="menu">
	<li role="presentation"><a role="menuitem" data-type="link" tabindex="-1" href="#" data-ref-table="">Link</a></li>
	<li class="separator"></li>
	<?php foreach($source_types as $ref_table => $table_info){?>
	<li role="presentation"><a role="menuitem" data-type="db" tabindex="-1" href="<?php echo site_url($table_info['url'])?>" data-ref-table="<?php echo $ref_table?>"><?php echo lang($table_info['label']);?></a></li>
	<?php } ?>
					</ul>
				</div>

				<ul class="list-group" style="margin-top:15px;">
					<li class="list-group-item empty hidden">
					No selected items.
					</li>
					<li class="list-group-item loading">
					Loading...
					</li>
				</ul>

				<input name="items_payload" type="hidden" />

				<script type="text/x-javascript-template">
					<li class="list-group-item cell">
						
						<div class="btn-group pull-right">
							<button type="button" class="btn btn-xs btn-default" rr-action="edit"><i class="fa fa-pencil"></i></button>
							<button type="button" class="btn btn-xs btn-danger" rr-action="remove"><i class="fa fa-times"></i></button>
						</div>
						<div class="media" style="margin-top:0;">

							<div class="media-body">
								<div class="media-heading">
<?php foreach($this->lang->get_available_locale_keys() as $loc_code): $loc_data = !empty($loc[$loc_code]) ? $loc[$loc_code] : NULL; ?>
									<span class="localized<?php if($this->lang->locale() != $loc_code) echo ' hidden'?>" re-locale="<?php echo $loc_code?>" rr-text="typeof custom_title != 'undefined' && custom_title && custom_title.length  ? custom_title.<?php echo $loc_code?>: ( typeof title.<?php echo $loc_code?> != 'undefined' ? title.<?php echo $loc_code?> : title )" ></span>
<?php endforeach; ?>
								</div>
								<span class="label label-info" rr-text="ref_table_str"></span>
<?php foreach($this->lang->get_available_locale_keys() as $loc_code): $loc_data = !empty($loc[$loc_code]) ? $loc[$loc_code] : NULL; ?>
								<small class="localized<?php if($this->lang->locale() != $loc_code) echo ' hidden'?>" re-locale="<?php echo $loc_code?>" rr-text="( typeof href.<?php echo $loc_code?> != 'undefined' ? href.<?php echo $loc_code?> : href )"></small>
<?php endforeach; ?>
							</div>
						</div>
					</li>
				</script>
			</div>

		</div>

	</div><!--/col-->


	
</div><!--/row-->


</form>

<?php if($is_dialog){?>
</div>
<?php } ?>


<div hc-dialog-template="type-link" class="modal">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
					<div class="btn-group pull-right">
						<button type="button" class="btn btn-change-locale btn-md btn-default dropdown-toggle" data-toggle="dropdown"><i class="fa fa-language"></i> <span class="loc-name"><?php echo lang('lang_'.$this->lang->locale())?></span> <span class="caret"></span></button>
						<ul class="dropdown-menu" role="dropdown">
		<?php foreach($this->lang->get_available_locale_keys() as $loc_code){?>
							<li><a href="#loc-<?php echo $loc_code?>" re-action="change-locale" re-locale-name="<?php echo lang('lang_'.$loc_code)?>" re-locale="<?php echo $loc_code?>"><?php echo lang('lang_'.$loc_code)?></a></li>
		<?php } ?>
						</ul>
					</div>
       			<h4 class="modal-title">Link</h4>
			</div>
			<div class="modal-body">
				<form class="form" method="post" enctype="multipart/form-data">
					<div class="row">
						<div class="col-sm-3">




			<div class="tab-content" hc-value-group="cover_id">
	<?php foreach($this->lang->get_available_locale_keys() as $loc_code){ $loc_data = !empty($loc[$loc_code]) ? $loc[$loc_code] : NULL; ?>
		  		<div class="tab-pane fade<?php if($loc_code == $this->lang->locale()) echo' active in';?>" re-locale="<?php echo $loc_code?>">


							<div class="form-group">
								<label class="control-label"><?php echo lang('field_cover_id')?> (<?php echo lang('lang_'.$loc_code)?>)</label>


								<div class="controls">
									<div style="max-width:240px;">
									<?php
									$this->widget('form_upload', array(
									'field_name' => '',
									'is_image'   => TRUE,
									'value'=> '',
									'attributes'=>array('hc-value'=>'cover_id','hc-locale'=>$loc_code),
									));
									?>
									</div>
					    			<hr class="clear"/>
<button class="btn btn-default btn-sm" data-toggle="copy-value" data-group="cover_id" data-locale="<?php echo $loc_code?>"><i class="fa fa-copy"></i> <?php echo lang('copy_to_other_language')?></button>
								
								</div>
							</div>


				</div>
	<?php } ?>
			</div>

						</div>
						<div class="col-sm-9">


			<div class="tab-content" hc-value-group="title">
	<?php foreach($this->lang->get_available_locale_keys() as $loc_code){ $loc_data = !empty($loc[$loc_code]) ? $loc[$loc_code] : NULL; ?>
		  		<div class="tab-pane fade<?php if($loc_code == $this->lang->locale()) echo' active in';?>" re-locale="<?php echo $loc_code?>">


					<div class="form-group">
								<label class="control-label"><?php echo lang('field_title')?> * (<?php echo lang('lang_'.$loc_code)?>)</label>
						<div>
							<input type="text" hc-value="title" hc-locale="<?php echo $loc_code?>" class="form-control" />
					    	<hr class="clear"/>
<button class="btn btn-default btn-sm" data-toggle="copy-value" data-group="title" data-locale="<?php echo $loc_code?>"><i class="fa fa-copy"></i> <?php echo lang('copy_to_other_language')?></button>
						</div>
					</div>


				</div>
	<?php } ?>
			</div>

							<div class="form-group">
								<label class="control-label">Target Window</label>
								<?php echo form_dropdown('',array(''=>'Same Window','_blank'=>'New Window'),'', ' class="form-control" hc-value="target"') ?>
							</div>

			<div class="tab-content" hc-value-group="href">
	<?php foreach($this->lang->get_available_locale_keys() as $loc_code){ $loc_data = !empty($loc[$loc_code]) ? $loc[$loc_code] : NULL; ?>
		  		<div class="tab-pane fade<?php if($loc_code == $this->lang->locale()) echo' active in';?>" re-locale="<?php echo $loc_code?>">


					<div class="form-group">
								<label class="control-label"><?php echo lang('field_url')?> * (<?php echo lang('lang_'.$loc_code)?>)</label>
						<div>
							<input type="text" hc-value="href" hc-locale="<?php echo $loc_code?>" class="form-control" />
					    	<hr class="clear"/>
<button class="btn btn-default btn-sm" data-toggle="copy-value" data-group="href" data-locale="<?php echo $loc_code?>"><i class="fa fa-copy"></i> <?php echo lang('copy_to_other_language')?></button>
						</div>
					</div>


				</div>
	<?php } ?>
			</div>

						</div>
					</div>
					
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo lang('button_cancel');?></button>
				<button type="button" class="btn btn-success" hc-action="save"><?php echo lang('button_done');?></button>
			</div>
		</div>
	</div>
</div>


<div hc-dialog-template="type-db-custom" class="modal">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
					<div class="btn-group pull-right">
						<button type="button" class="btn btn-change-locale btn-md btn-default dropdown-toggle" data-toggle="dropdown"><i class="fa fa-language"></i> <span class="loc-name"><?php echo lang('lang_'.$this->lang->locale())?></span> <span class="caret"></span></button>
						<ul class="dropdown-menu" role="dropdown">
		<?php foreach($this->lang->get_available_locale_keys() as $loc_code){?>
							<li><a href="#loc-<?php echo $loc_code?>" re-action="change-locale" re-locale-name="<?php echo lang('lang_'.$loc_code)?>" re-locale="<?php echo $loc_code?>"><?php echo lang('lang_'.$loc_code)?></a></li>
		<?php } ?>
						</ul>
					</div>

       			<h4 class="modal-title">Custom</h4>
			</div>
			<div class="modal-body">
				<form class="form" method="post" enctype="multipart/form-data">
					<div class="row">
						<div class="col-sm-3">

			<div class="tab-content" hc-value-group="custom_cover_id">
	<?php foreach($this->lang->get_available_locale_keys() as $loc_code){ $loc_data = !empty($loc[$loc_code]) ? $loc[$loc_code] : NULL; ?>
		  		<div class="tab-pane fade<?php if($loc_code == $this->lang->locale()) echo' active in';?>" re-locale="<?php echo $loc_code?>">

					<div class="form-group">
		  			
					<label class="control-label"><?php echo lang('field_cover_id')?> (<?php echo lang('lang_'.$loc_code)?>)</label>


					<div class="controls">
						<div style="max-width:240px;">
						<?php
						$this->widget('form_upload', array(
						'field_name' => '',
						'is_image'   => TRUE,
						'value'=> '',
						'attributes'=>array('hc-value'=>'custom_cover_id','hc-locale'=>$loc_code),
						));
						?>
						</div>
					    	<hr class="clear"/>
<button class="btn btn-default btn-sm" data-toggle="copy-value" data-group="custom_cover_id" data-locale="<?php echo $loc_code?>"><i class="fa fa-copy"></i> <?php echo lang('copy_to_other_language')?></button>


					</div>

					</div>

				</div>
	<?php } ?>
			</div>

						</div>
						<div class="col-sm-9">

			<div class="tab-content" hc-value-group="title">
	<?php foreach($this->lang->get_available_locale_keys() as $loc_code){ $loc_data = !empty($loc[$loc_code]) ? $loc[$loc_code] : NULL; ?>
		  		<div class="tab-pane fade<?php if($loc_code == $this->lang->locale()) echo' active in';?>" re-locale="<?php echo $loc_code?>">

					<div class="form-group">
						<label class="control-label"><?php echo lang('field_title')?></label>
							<input type="text" hc-value="title" readonly="" hc-locale="<?php echo $loc_code?>" class="form-control" />
					</div>

				</div>
	<?php } ?>
			</div>


			<div class="tab-content" hc-value-group="custom_title">
	<?php foreach($this->lang->get_available_locale_keys() as $loc_code){ $loc_data = !empty($loc[$loc_code]) ? $loc[$loc_code] : NULL; ?>
		  		<div class="tab-pane fade<?php if($loc_code == $this->lang->locale()) echo' active in';?>" re-locale="<?php echo $loc_code?>">


					<div class="form-group">
						<label class="control-label">Custom Title</label>
						<div class="controls">
							<input type="text" hc-value="custom_title" hc-locale="<?php echo $loc_code?>" class="form-control" />
					    	<hr class="clear"/>
<button class="btn btn-default btn-sm" data-toggle="copy-value" data-group="custom_title" data-locale="<?php echo $loc_code?>"><i class="fa fa-copy"></i> <?php echo lang('copy_to_other_language')?></button>

						</div>
					</div>


				</div>
	<?php } ?>
			</div>

					<div class="form-group">
						<label class="control-label">Target Window</label>
						<?php echo form_dropdown('',array(''=>'Same Window','_blank'=>'New Window'),'', ' class="form-control" hc-value="target"') ?>
					</div>


						</div>
					</div>




				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo lang('button_cancel');?></button>
				<button type="button" class="btn btn-success" hc-action="save"><?php echo lang('button_done');?></button>
			</div>
		</div>
	</div>
</div>