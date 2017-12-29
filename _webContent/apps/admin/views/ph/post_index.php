<?php

$this -> title[] = lang($section.'_heading');

$this -> asset -> js_embed('ph/post_index.js.php',null,null,'body_foot');

?>

<?php if($is_dialog){ ?>
<div class="modal-header">
	<h4><?php echo lang($section.'_heading');?></h4>
</div>

<div class="modal-footer float">
	<button type="button" class="btn btn-window-close right btn-default"><i class="fa fa-times"></i> <?php echo lang('button_cancel')?></button>
	<button type="button" class="btn btn-rr-select btn-success" disabled="disabled"><i class="fa fa-check"></i> <?php echo lang('button_done')?></button>
</div>
<div class="modal-body">
<?php } ?>


<div class="row record-list">
<?php if(!$is_dialog){ ?>
	<div class="col-xs-12">
		<div class="page-header">
			<div class="pull-right">
				<a target="_blank" href="<?php if(isset($preview_url)) echo $preview_url?>" class="btn btn-default"><?php echo lang('preview')?></a>
				<a href="<?php echo site_url('s/'.$section.'/post/add')?>" class="btn btn-success"><i class="fa fa-plus"></i> <?php echo lang('button_add')?></a>
			</div>
			<h2><?php echo lang($section.'_heading');?></h2>
		</div>
	</div>
<?php }?>
	<div class="col-xs-12">
		

		<form method="get" class="form searchbar">
			<div class="row">
				<div class="col-md-4 col-sm-6 col-xs-12">

					<div class="form-group">
						<div class="input-group">
							<input type="text" class="form-control" name="q" placeholder="<?php echo lang('keyword')?>" />
							<div class="input-group-btn">
								<button type="submit" class="btn btn-primary"><?php echo lang('button_search');?></button>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-4 col-sm-6 col-xs-12">

					<div class="form-group">

						<div class="btn-group search-order" data-toggle="buttons">
							<label class="btn btn-default" type="button"><input type="radio" name="direction" value="desc" checked="" /> <i class="glyphicon glyphicon-sort-by-order-alt"></i><span class="sr-only">Ascending</span></label>
							<label class="btn btn-default active" type="button"><input type="radio" name="direction" value="asc" /> <i class="glyphicon glyphicon-sort-by-order"></i><span class="sr-only">Descending</span></label>
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
				<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><?php echo lang('listing_selected_action')?> <span class="caret"></span></button>
				<ul class="dropdown-menu">
					<li role="presentation" class="dropdown-header"><?php echo lang('status')?></li>
					<li role="presentation"><a role="menuitem" href="#" class="action-status-enable" data-action="status-enable"><?php echo lang('button_status_enable');?></a></li>
					<li role="presentation"><a role="menuitem" href="#" class="action-status-disable" data-action="status-disable"><?php echo lang('button_status_disable');?></a></li>
					<li role="presentation" class="divider"></li>
					<li role="presentation"><a role="menuitem" href="#" class="action-publish" data-action="publish"><?php echo lang('button_publish');?></a></li>
					<li role="presentation"><a role="menuitem" href="#" class="action-remove" data-action="remove"><?php echo lang('button_remove');?></a></li>
				</ul>
			</div>
<?php } ?>
			<ul class="pagination">
				<li class="previous disabled"><a href="#">&larr;</a></li>
				<li><span><b class="paging-offset-start">0</b> - <b class="paging-offset-end">0</b> / <b class="paging-total">0</b></span></li>
				<li class="next"><a href="#">&rarr;</a></li>
			</ul>
			
			<a href="<?php echo site_url('s/'.$section.'/post/priority')?>" class="btn btn-default priority-btn"><i class="fa"></i> <?php echo lang('priority_btn')?></a>
			<hr class="clear"/>
		</div>
		<div class="table-responsive">
			<table class="table cell-list table-striped table-hover table-condensed ">
				<thead>
					<tr>
						<th class="col-xs-8 col-sm-8"><?php echo lang('field_content')?></th>
						<th class="col-xs-2 col-sm-2"><?php echo lang('field_status')?></th>
						<th class="col-xs-2 col-sm-2"><?php echo lang('last_update')?></th>
					</tr>
				</thead>
				<tbody>
					<tr class="template">
						<td>
							<div class="media-row">
							<img rc-if="row.cover_url.length>0" rc-src="cover_url" style="max-width:100px; max-height:50px; margin-right: 10px;" align="left" class="media" />
							
							
							<div class="media-body">
							<?php if($this->ph->is_localized){?><div class="row-caption" rc-text="loc_title"></div><?php }else{?><div class="rc row-caption" rc-text="title"></div><?php } ?>

							<?php if($this->ph->is_localized){?><div rc-text="loc_description"></div><?php }else{?><div rc-text="description"></div><?php } ?>
<?php if($this->ph->is_tag_enabled):?>
							<ul class="label-list">
								<li class="item" rc-loop="tag..tags"><div class="label label-info" rc-data="{id:tag.id}" rc-action="tag_filter"><i class="fa fa-tag"></i> <span class="text" rc-text="tag.title"></span></div></li>
							</ul>
<?php endif;?>
<?php if($this->ph->is_category_enabled):?>
							<ul class="label-list">
								<li class="item" rc-loop="category..categories"><div class="label label-warning" rc-data="{id:category.id}" rc-action="category_filter"><i class="fa fa-folder-open"></i> <span class="text" rc-text="category.title_full"></span></div></li>
							</ul>
<?php endif;?>

							<div class="extra-tools">
								<div class="btn-group">
									<label class="btn btn-xs btn-default btn-select"><i data-deactive="fa-circle-o" data-active="fa-dot-circle-o" class="fa fa-circle-o"></i></label>
								</div>
								<div class="btn-group">
									<a href="#" rc-if="row.is_pushed>0" rc-href="_live_url" target="_blank" class="btn rc-action btn-default btn-xs"><?php echo lang('button_live')?></a>
									<a href="#" rc-href="_preview_url" target="_blank" class="btn rc-action btn-default btn-xs"><?php echo lang('button_preview')?></a>
								<a href="#" rc-href="'<?php echo $endpoint_url_prefix?>/'+row.id+'/edit'" class="btn rc-action btn-default btn-xs"><?php echo lang('button_edit')?></a>
								</div>
							</div>
							</div>
							</div></td>
						<td><small><span rc-text="status_str"></span><br /><span rc-text="is_pushed_str"></span><small rc-if="row.last_pushed && row.last_pushed.length>0">, <span rc-date-format="ago" rc-text="last_pushed"></span></small></small></td>
						<td><div rc-date-format="ago" rc-text="modify_date"></div></td>
					</tr>
				</tbody>
			</table>
		</div>

	</div>
</div>

<?php if($is_dialog){?> 
	
</div>
<?php } ?>

