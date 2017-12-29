<?php

$this -> title[] = lang('album_heading');


$this -> asset -> js_embed('album/item_index.js.php',NULL,NULL,'body_foot');

?>

<?php if($is_dialog){ ?>
<div class="modal-header">
	<h4><?php echo lang('album_heading');?></h4>
</div>

<div class="modal-footer float">
	<button type="button" class="btn btn-default btn-window-close right"><i class="fa fa-times"></i> <?php echo lang('button_cancel')?></button>
	<button type="button" class="btn btn-success btn-rr-select" disabled="disabled"><i class="fa fa-check"></i> <?php echo lang('button_done')?></button>
</div>
<div class="modal-body">
<?php } ?>


	<div class="row record-list" rl-select-button=".btn-rr-select">
<?php if(!$is_dialog){ ?>
		<div class="col-xs-12">
			<div class="page-header">
				<div class="pull-right"><a href="<?php echo site_url('album/add')?>" class="btn btn-success"><i class="fa fa-plus"></i> <?php echo lang('button_add')?></a></div>
				<h2><?php echo lang('album_heading');?></h2>
			</div>
		</div>
<?php }?>
		<div class="col-xs-12">
			

			<form method="get" class="form searchbar">
				<div class="row">
					<fieldset class="col-sm-4 col-xs-12">
						<div class="form-group">
							<div class="input-group">
								<input type="text" class="form-control" name="q" placeholder="<?php echo lang('keyword')?>" />
								<div class="input-group-btn">
									<button type="submit" class="btn btn-primary"><?php echo lang('button_search');?></button>
								</div>
							</div>
						</div>
					</fieldset>
					<fieldset class="col-sm-4 col-xs-12">
						<div class="form-group">
							<div class="btn-group search-order" data-toggle="buttons">
								<label class="btn btn-default active" type="button"><input type="radio" name="direction" value="desc" checked="" /> <i class="glyphicon glyphicon-sort-by-order"></i><span class="sr-only">Ascending</span></label>
								<label class="btn btn-default" type="button"><input type="radio" name="direction" value="asc" /> <i class="glyphicon glyphicon-sort-by-order-alt"></i><span class="sr-only">Descending</span></label>
							</div>
						</div>

					</fieldset>
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
			
			<ul class="cell-list cell-sm filedrop">
				<li class="template cell">
					<div class="body">
						<div class="thumbnail spacer">
							<img src="<?php echo spacer_url('img/spacer.gif')?>" width="100%" />
						</div>
						<div class="tools">
							<div class="btn-group">
								<label class="btn btn-sm btn-default btn-select"><i data-deactive="fa-circle-o" data-active="fa-dot-circle-o" class="fa fa-circle-o"></i></label>
								<a class="btn btn-sm btn-info btn-zoom" rc-action="modal" rc-href="config.site_url+'album/'+row.id+'/edit'" href="#"><i class="fa fa-edit"></i></a>
							</div>
						</div>
					</div>
				</li>
		
				<li class="last"></li>
			</ul>
		
		</div>
	</div>
</div>
<?php if($is_dialog){?>
</div>
<?php } ?>

