<?php

$this -> title[] = lang($section.'_heading');
$this -> title[] = lang('category_heading');

$this -> asset -> js_embed('ph/category_index.js.php',NULL,NULL,'body_foot');
$this -> asset -> css(asset_url('css/icons.css'));

?>

<?php if($is_dialog){ ?>
<div class="modal-header">
	<h4><?php echo lang('category_heading');?></h4>
</div>

<div class="modal-footer float">
		<button type="button" class="btn btn-window-close right btn-default"><i class="fa fa-times"></i> <?php echo lang('button_cancel')?></button>
		<button type="button" class="btn btn-rr-select btn-success" disabled="disabled"><i class="fa fa-check"></i> <?php echo lang('button_done')?></button>
</div>
<div class="modal-body">
<?php } ?>

<div class="row record-list">
	<div class="col-xs-12">
<?php if(!$is_dialog){ ?>
		<div class="page-header">
			<div class="pull-right">
				<a href="<?php echo site_url('s/'.$section.'/category/add')?>" onclick="location.href=$(this).data('href')+'?forward='+escape(location.href);" data-href="<?php echo site_url('s/'.$section.'/category/add')?>" class="btn btn-success"><i class="fa fa-plus"></i> <?php echo lang('button_add')?></a>
			</div>
			<h2><?php echo lang('category_heading');?></h2>
		</div>
<?php }?>
		
		<ul class="breadcrumb">
			<li>
				<a class="listing-query" href="<?php echo site_url('s/'.$section.'/category?path=/')?>"><i class="fa fa-home"></i></a>
			</li>
		</ul>
	</div>

	<div class="col-xs-12">
			<form method="get" class="form searchbar">
				<div class="row">
				<fieldset class=" col-xs-12">
					<div class="form-group">

						<div class="btn-group search-order" data-toggle="buttons">
							<label class="btn btn-default" type="button"><input type="radio" name="direction" value="desc" checked="" /> <i class="glyphicon glyphicon-sort-by-order-alt"></i><span class="sr-only">Ascending</span></label>
							<label class="btn btn-default active" type="button"><input type="radio" name="direction" value="asc" /> <i class="glyphicon glyphicon-sort-by-order"></i><span class="sr-only">Descending</span></label>
						</div>
					</div>

				</fieldset>
				</div>

				<hr class="clear" />
			</form>
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
			
			<hr class="clear"/>
		</div>
		<div class="table-responsive">
			<table class="table cell-list table-striped table-hover table-condensed ">
				<thead>
					<tr>
							<th class="col-xs-4 col-sm-4"><?php echo lang('field_content')?></th>
							<th class="col-xs-2 col-sm-2"><?php echo lang('field_slug')?></th>
							<th class="col-xs-1 col-sm-1"><?php echo lang('post_heading')?></th>
						<th class="col-xs-1 col-sm-1"><?php echo lang('field_priority')?></th>
						<th class="col-xs-2 col-sm-2"><?php echo lang('field_status')?></th>
						<th class="col-xs-2 col-sm-2"><?php echo lang('last_update')?></th>
					</tr>
				</thead>
				<tbody>
					<tr class="template">
						<td>
							<a href="#" class="row-caption listing-query" rc-href="queryToHref({path:row.path})">
							<span rc-if="row.icon!=null && row.icon.length>0" rc-html="'<i class=\''+row.icon+'\'/> '"></span>
							<?php if($ph->is_locale_enabled){?><span class="row-caption" rc-text="loc_title"></span><?php }else{?><span class="rc row-caption" rc-text="title"></span><?php } ?>
							</a>
							
							<div class="extra-tools">
								<div class="btn-group">
									<label class="btn btn-xs btn-default btn-select"><i data-deactive="fa-circle-o" data-active="fa-dot-circle-o" class="fa fa-circle-o"></i></label>
								</div>
								<div class="btn-group">
									<a rc-href="'<?php echo $section_url_prefix?>/post?q=category:'+row.id" target="_blank" class="btn btn-default btn-xs"><i class="fa fa-search"></i> <?php echo lang('post_heading')?></a>
								</div>
								<div class="btn-group">
									<a href="#" rc-if="row.is_pushed>0" rc-href="_live_url" target="_blank" class="btn rc-action btn-default btn-xs"><?php echo lang('button_live')?></a>
									<a href="#" rc-href="_preview_url" target="_blank" class="btn rc-action btn-default btn-xs"><?php echo lang('button_preview')?></a>
									<a rc-href="'<?php echo $endpoint_url_prefix?>/'+row.id+'/edit'" rc-action="modal" class="btn btn-edit btn-default btn-xs"><?php echo lang('button_edit')?></a>
								</div>
							</div>
						</td>
							<td><div rc-text="slug"></div></td>
							<td><div rc-text="num_posts"></div></td>
						<td><span rc-text="priority"></span></td>
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




