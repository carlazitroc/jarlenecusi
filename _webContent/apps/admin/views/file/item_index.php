<?php

$this -> title[] = lang('file_heading');

$this -> asset -> css(base_url('assets/css/ifuploader.min.css'));
$this -> asset -> js_import(base_url('assets/js/jquery.filedrop.js'),null,'body_foot');

$this -> asset -> js_embed('file/item_index.js.php',null,null,'body_foot');

?>

<?php if($is_dialog){ ?>
<div class="modal-header">
	<h4><?php echo lang('file_heading');?></h4>
</div>

<div class="modal-footer float">
	<button type="button" class="btn btn-window-close right btn-default"><i class="fa fa-times"></i> <?php echo lang('button_cancel')?></button>
	<button type="button" class="btn btn-rr-select btn-primary" disabled="disabled"><i class="fa fa-check"></i> <?php echo lang('button_done')?></button>
</div>
<div class="modal-body">
<?php } ?>



	<div class="row">
		<?php if(!$is_dialog){ ?>
		<div class="col-xs-12">
			<div class="page-header">
				<h2><?php echo lang('file_heading');?></h2>
			</div>
		</div>
		<?php }?>
	</div>

	<div class="filedrop">
		<div class="row record-list">
			<div class="col-xs-12">
				<form method="get" class="form searchbar">
					<div class="row">
						<div class=" col-lg-4 col-sm-6 col-xs-12">

							<div class="form-group">
								<div class="input-group">
									<?php if($default_type == 'all'){?>
									<div class="input-group-btn">
								<div class="btn-group search-type">
									<button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><i class="fa fa-filter"></i> <span class="caret"></span></button>
									<ul class="dropdown-menu">
										<li><a href="#" class="type-all" data-value="all"><?php echo lang('filetype_all')?></a></li>
										<?php foreach($this->file_types as $file_type_key => $file_typ_exts){ ?>
										<li><a href="#" class="type-<?php echo $file_type_key?>" data-value="<?php echo $file_type_key?>"><?php echo lang('filetype_'.$file_type_key);?></a></li>
										<?php }?>
									</ul>
								</div>
									</div>
								<?php } ?>
									<input type="text" class="form-control" name="q" placeholder="<?php echo lang('keyword')?>" />
									<div class="input-group-btn">
										<button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
									</div>
								</div>
							</div>

						</div>
						<div class="col-lg-4 col-sm-6 col-xs-12">

							<div class="form-group">
								<div class="btn-group search-order" style=" margin-right:5px;" data-toggle="buttons">
									<label class="btn btn-default" type="button"><input type="radio" name="direction" value="desc" checked="" /> <i class="glyphicon glyphicon-sort-by-order-alt"></i><span class="sr-only">Ascending</span></label>
									<label class="btn btn-default active" type="button"><input type="radio" name="direction" value="asc" /> <i class="glyphicon glyphicon-sort-by-order"></i><span class="sr-only">Descending</span></label>
								</div>

								<div class="input-group" style="display:inline-block; vertical-align:middle;">
								<?php echo form_dropdown('sort',array(
									'file_name'=>lang('field_file_name'),
									'create_date'=>lang('field_upload_date'),
									), $this->input->get('sort'), 'class="form-control"');
									?>
								</div>
							</div>

						</div>
					</div>




					<hr class="clear" />
				</form>
			</div>
			<div class="col-xs-12">
				<div class="actionbar top">
					<div class="btn-group">
						<button type="button" class="btn btn-default" rl-toggle-select-all><i data-deactive="fa-circle-o" data-active="fa-dot-circle-o" class="fa fa-circle-o"></i></button>
		                <button type="button" class="btn btn-default" rl-reload><i class="fa fa-refresh"></i> <?php echo lang('button_reload');?></button>
		            </div>
					<?php if(!$is_dialog){ ?>
					<div class="btn-group btn-selected-action">
						<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> <span class="caret"></span></button>
						<ul class="dropdown-menu">
							<li><a href="#" class="action-remove" data-action="remove"><?php echo lang('button_remove');?></a></li>
						</ul>
					</div>
					<?php } ?>
					<ul class="pagination">
						<li class="previous disabled"><a href="#">&larr;</a></li>
						<li><span><b class="paging-offset-start">0</b> - <b class="paging-offset-end">0</b> / <b class="paging-total">0</b></span></li>
						<li class="next"><a href="#">&rarr;</a></li>
					</ul>

					<hr class="clear"/>
				</div>

				<ul id="file-list" class="cell-list cell-sm uploaded-list" style="margin-left:-10px; margin-right:-10px;">
					<li class="template cell">
						<div class="body">
							<div class="thumbnail spacer">
								<img src="<?php echo spacer_url('img/spacer.gif')?>" width="100%" />
							</div>
							<div class="tools">
								<div class="btn-group">
									<label class="btn btn-sm btn-default btn-select"><i data-deactive="fa-circle-o" data-active="fa-dot-circle-o" class="fa fa-circle-o"></i></label>
								</div>
							</div>
							<div rc-if="row.file_name && row.file_name.length>0" class="title"><span rc-text="file_name"></span></div>
						</div>
					</li>

					<li class="last"></li>
				</ul>

			</div>
			<div class="col-xs-12 visible-filedrop-uploading">
				<div class="alert alert-info">
					<div class="fa-stack">
						<i class="fa fa-circle fa-stack-2x"></i>
						<i class="fa fa-spin fa-spinner fa-inverse fa-stack-1x"></i>
					</div>
					<?php echo lang('uploading')?>
				</div>
			</div>
			<div class="col-xs-12">
				<div class="upload">
					<div class="body">
						<form class="ifrm" action="<?php echo site_url('file/upload.html')?>" method="post" enctype="multipart/form-data">
							<div class="fileupload fileupload-new" data-provides="fileupload">
								<div>
									<span id="btn-file-upload" class="btn btn-default btn-file"><span class="fileupload-new"><i class="fa fa-upload"></i> <?php echo lang('button_select_file')?></span><input type="file" name="new_file" /></span>
								</div>
							</div>
							<div class="visible-desktop">
								<span class="hidden-has-drag"><?php echo lang('file_list_dragzone_description');?></span>
								<span class="visible-has-drag"><p><?php echo lang('file_list_dropfile_description');?></p></span>

							</div>

						</form>
					</div>
				</div>
			</div>	
		</div>
	</div>

<?php if($is_dialog){?> 

</div>
<?php } ?>
