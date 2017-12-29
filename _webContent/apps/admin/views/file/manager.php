<?php

$this -> title[] = lang('file_heading');

$this -> asset -> js_embed('file/manager.js.php',null,null,'body_foot');

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
<?php if(!$is_dialog){ ?>
	<div class="row">
		<div class="col-xs-12">
			<div class="page-header">
				<h2><?php echo lang('file_heading');?></h2>
			</div>
		</div>
	</div>
<?php }?>

<div class="record-list">
	<div class="row filedrop record-list">
		<div class="col-xs-12">
			<form method="get" class="form searchbar">
				<div class="row">
					<div class="col-sm-4 col-xs-12">

						<div class="form-group">
							<div class="input-group">
								<div class="input-group-addon">
									<?php echo lang('search');?>
								</div>
								<input type="text" class="form-control" name="q" placeholder="<?php echo lang('keyword')?>" />
								<div class="input-group-btn">
									<button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
								</div>
							</div>

						</div>
					</div>
					<div class="col-sm-4 col-xs-12">

						<div class="form-group">
<?php if($default_type == 'all'){?>
							<div class="btn-group search-type">
								<button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><i class="fa fa-filter"></i> <span class="text">All File Type</span> <span class="caret"></span></button>
								<ul class="dropdown-menu">
									<li><a href="#" class="type-all" data-value="all"><?php echo lang('filetype_all')?></a></li>
<?php foreach($this->file_types as $file_type_key => $file_typ_exts){ ?>
									<li><a href="#" class="type-<?php echo $file_type_key?>" data-value="<?php echo $file_type_key?>"><?php echo lang('filetype_'.$file_type_key);?></a></li>
<?php }?>
								</ul>
							</div>
<?php } ?>

							<div class="btn-group search-order" style=" margin-right:5px;" data-toggle="buttons">
								<label class="btn btn-default active" type="button"><input type="radio" name="direction" value="desc" checked="" /> <i class="glyphicon glyphicon-sort-by-order-alt"></i><span class="sr-only">Ascending</span></label>
								<label class="btn btn-default" type="button"><input type="radio" name="direction" value="asc" /> <i class="glyphicon glyphicon-sort-by-order"></i><span class="sr-only">Descending</span></label>
							</div>
							<div class="input-group" style="display:inline-block; vertical-align:middle;">
							<?php echo form_dropdown('sort',array(
								'file_name'=>lang('field_file_name'),
								'create_date'=>lang('field_upload_date'),
							), $this->input->get('sort'), 'class="form-control"');
							?>
							</div>
						</div>
						<div class="form-group">
						</div>
					</div>
				</div>
				<hr class="clear" />
			</form>
		</div>
	</div>

	<div class="filedrop cell-list">

		<div class="cell group">
		</div>

		<div class="cell file">
		</div>

	</div>

</div>

<?php if($is_dialog){ ?>
</div>
<?php } ?>