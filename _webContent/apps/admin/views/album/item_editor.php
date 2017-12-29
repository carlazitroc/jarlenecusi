<?php

$this -> title[] = lang('album_heading');

$this -> asset -> css(base_url('assets/css/ifuploader.css'));
$this -> asset -> js_import(base_url('assets/libs/core/ifuploader.js'));
$this -> asset -> js_import(base_url('assets/js/jquery.filedrop.js'));
$this -> asset -> js_embed('album/item_editor.js.php',NULL,NULL,'body_foot');


$this->config->set_item('main_menu_selected', array('album'));

?>
<?php if($is_dialog){ ?>
<div class="modal-header">
	<h4><?php echo lang('album_heading');?></h4>
</div>

<div class="modal-footer float">
	<button type="button" class="btn btn-default right" re-action="cancel"><i class="fa fa-times"></i> <?php echo lang('button_cancel')?></button>
	<button type="button" class="btn btn-success" re-action="save"><i class="fa fa-save"></i> <?php echo lang('button_save')?></button>
	<button type="button" class="btn btn-primary" re-action="done"><i class="fa fa-check"></i> <?php echo lang('button_done')?></button>
</div>
<div class="modal-body">
<?php } ?>



		
<div class="row">
	
<?php if(!$is_dialog){ ?>	
	<div class="col-xs-12">	
		<div class="page-header">
<div class="pull-right toolbar">
	<button type="submit" class="btn btn-primary" re-action="save"><i class="fa fa-save"></i> <?php echo lang('button_save')?></button>
	
<?php if(forward_url() !=''){ ?>
	<a class="btn btn-warning btn-link" href="<?php echo forward_url()?>"><i class="fa fa-times"></i> <?php echo lang('button_discard_changes')?></a>
<?php } ?>
		
		
</div>
			<h2><?php echo lang('album_heading')?></h2>
		</div>

	</div>
<?php } ?>

<div class="filedrop col-xs-12">
	<ul id="file-list" class="cell-list sortable">
		<li class="template ">
			<div class="thumbnail spacer handle" style="background-image:url(<?php echo spacer_url('img/spacer.gif')?>)"></div>
			<div class="sequence"><span>-</span></div>
			<div class="tools">
				<div class="btn-group">
					<a href="#" class="btn btn-small btn-danger btn-delete"><i class="fa fa-minus-circle"></i></a>
					<a href="#" class="btn btn-small btn-info btn-detail"><i class="fa fa-info"></i></a>
				</div>
			</div>
		</li>

		<li class="last"></li>
	</ul>
	<div class="upload">
		<div class="body">
			<button type="button" class="btn btn-info btn-photo-select right"><i class="fa fa-cloud-download"></i> <?php echo lang('button_import_server_file')?></button>
			<p> or </p>
			<form class="ifrm" action="<?php echo site_url('file/upload.html')?>" method="post" enctype="multipart/form-data">
			<div class="fileupload fileupload-new" data-provides="fileupload">
			  <div class="ifupr-file-wrap">
			    <span class="btn btn-default btn-file "><span class="fileupload-new"><i class="fa fa-upload"></i> <?php echo lang('button_select_image')?></span>
			    <input id="file_upload" type="file" name="new_file" class="ifupr-file" />
			    </span>
			<span class="visible-md visible-lg"><small><?php echo lang('file_list_dragzone_description');?></small></span>
			  </div>
			</div>
			</form>
		</div>
	</div>
</div>
</div>
<?php if($is_dialog){ ?>	
</div>
<?php } ?>



<div id="photo-detail" class="modal fade" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
       			<h4 class="modal-title"><?php echo lang('detail')?></h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal">
					<div class="col-sm-12">
						<div class="form-group">
							<label class="control-label"><?php echo lang('field_name')?></label>
							<div class="control-group">
								<input type="text" data-field="name" class="form-control" placeholder="<?php echo lang('placeholder_optional')?>" />
							</div> 
						</div>
						<div class="form-group">
							<label class="control-label"><?php echo lang('field_link')?></label>
							<div class="control-group">
								<input type="text" data-field="link" class="form-control" placeholder="<?php echo lang('placeholder_optional')?> http://yourdomain.com" />
							</div> 
						</div>

						<div class="form-group">
							<label class="control-label"><?php echo lang('field_content')?></label>
							<div class="control-group">
								<textarea data-field="content" rows="5" class="form-control" placeholder="<?php echo lang('placeholder_optional')?>"></textarea>
							</div> 
						</div>
					</div>
					<hr class="clear"/>
				</div>
				<hr class="clear"/>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary " re-action="save"><?php echo lang('button_update');?></button>
				<button type="button" class="btn btn-danger" data-dismiss="modal"><?php echo lang('button_cancel');?></button>
			</div>
		</div>
	</div>
</div>

<div id="photo-remove" class="modal fade" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
       			<h4 class="modal-title"><?php echo lang('remove');?></h4>
			</div>
			<div class="modal-body">
				<p><?php echo lang('remove_message');?></p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-photo-remove btn-danger"><?php echo lang('button_yes');?></button>
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo lang('button_no');?></button>
			</div>
		</div>
	</div>
</div>
