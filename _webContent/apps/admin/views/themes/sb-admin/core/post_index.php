<?php

$columns = array();
$columns_info = array();

if($edit_enabled && !$is_dialog){
	$columns[] = array(
		'pk'=>FALSE,
		'data'=>'commands',
		'formatter'=>'commands',
		);
}


foreach($listing_fields as $field_name ){
	if(isset($extra_fields[$field_name]['listing']) && $extra_fields[$field_name]['listing']){

		$field_info = $extra_fields[$field_name];

		$label = !empty($field_info['label']) ? $field_info['label'] : lang('field_'.$field_name);

		$columns[] = array(
				'pk'=>FALSE,
				"data"=> $field_name,
				'info'=> $field_info,
				"title"=> $label,
				'formatter'=>'data',
				'searchable'=>FALSE,
				'sortable'=>FALSE,
			);
		$columns_info[ $field_name] = $field_info;
	}
	if(isset($target_model->fields_details[$field_name]['listing']) && $target_model->fields_details[$field_name]['listing']){

		$field_info = $target_model->fields_details[$field_name];

		$label = !empty($field_info['label']) ? $field_info['label'] : lang('field_'.$field_name);

		$columns[] = array(
				'pk'=> isset($field_info['pk']) && $field_info['pk'],
				"data"=> $field_name,
				'info'=> $field_info,
				"title"=> $label,
				'formatter'=>'data',
				'searchable'=>in_array($field_name, $keyword_fields),
				'sortable'=>in_array($field_name, $sorting_fields),
				'listing_hidden'=> data('listing_hidden', $field_info, false),
			);
		$columns_info[ $field_name] = $field_info;
	}
}



$this -> title[] = $page_header;

$this -> asset -> css(base_url('assets/libs/jquery-bootgrid/jquery.bootgrid.min.css'));
$this -> asset -> js_import(base_url('assets/libs/jquery-bootgrid/jquery.bootgrid.js'));
$this -> asset -> js_embed($theme_path.'core/post_index.js.php',compact('columns','columns_info','edit_enabled'),NULL,'body_foot');


?>


<?php if($is_dialog){ ?>
<div class="modal-header">
	<h4><?php echo $page_header;?></h4>
</div>

<div class="modal-footer float">
<?php if($export_enabled): ?>
				<a class="btn btn-default btn-export" target="_blank" href="<?php echo $endpoint_url_prefix.'/export'.uri_query()?>"><?php echo lang('button_export')?></a>
<?php endif;?>
	<button type="button" class="btn btn-window-close right btn-default"><i class="fa fa-times"></i> <?php echo lang('button_cancel')?></button>
	<button type="button" class="btn btn-rr-select btn-success" disabled="disabled"><i class="fa fa-check"></i> <?php echo lang('button_done')?></button>
</div>
<div class="modal-body">
<?php } ?>

	<div class="row record-list">
		<?php if(!$is_dialog){ ?>
		<div class="col-xs-12">
			<div class="page-header">
				<h2><?php echo $page_header;?></h2>
				<?php if (isset($remark) && !empty($remark)):?>
					<p><?php echo $remark?></p>
				<?php endif;?>	
			<div class="toolbar" style="float:none;">
			<div class="btn-group">
<?php if($export_enabled): ?>
				<a class="btn btn-default btn-export" target="_blank" href="<?php echo $endpoint_url_prefix.'/export'.uri_query()?>"><?php echo lang('button_export')?></a>
<?php endif;?>
<?php if($add_enabled):?>
				<a href="<?php echo ($endpoint_url_prefix.'/add').uri_query()?>" class="btn btn-info"><i class="fa fa-plus"></i> <?php echo lang('button_add')?></a>
<?php endif;?>
			</div>
<?php if( !empty($listing_toolbar_buttons)):?>
			<div class="btn-group">
<?php foreach($listing_toolbar_buttons as $button):?>
				<a href="<?php echo data('url',$button)?>" class="btn btn-default"><?php echo data('label',$button)?></a>
<?php endforeach;?>
			</div>
<?php endif; ?>
			</div>

			</div>
		</div>
		<?php }?>
<?php if(!empty($listing_filters)):?>
		<div class="col-xs-12">


		<div class="panel panel-default listing-filter">
		<div class="panel-heading"><b>Filter</b></div>
		<div class="panel-body">


		<form action="<?php echo $endpoint_url_prefix; ?>" method="get" class="form">
		<div class="row">
<?php foreach($listing_filters as $filter_name => $filter_info):?>
<div class="form-group col-md-2 col-sm-4 col-xs-6">
<?php if($filter_info['control'] == 'select'):?>
			<?php echo form_dropdown('filters['.$filter_name.']', data('options',$filter_info),data('value',$filter_info),' class="form-control"'); ?>
<?php endif;?>
<?php if($filter_info['control'] == 'text'):

$attrs = array('name'=>'filters['.$filter_name.']','value'=>data('value',$filter_info),'class'=>"form-control", 'placeholder'=>lang('field_'.$filter_name));
if(isset($filter_info['control_type'])):

	if($filter_info['control_type'] == 'date'){
		$attrs['class'].= ' datepicker';
	}
	if($filter_info['control_type'] == 'datetime'){
		$attrs['class'].= ' datetimepicker';
	}

endif;
?>

			<?php echo form_input($attrs); ?>
<?php endif;?>
</div>
<?php endforeach;?>
			<div class="form-group col-md-2 col-sm-4 col-xs-6">
			<button type="submit" class="btn btn-info"><?php echo lang('change')?></button>
			</div>
		</div>
		</form>

		</div>
		</div>
		</div>
<?php else:?>
<?php endif;?>
		<div class="col-xs-12">


<?php if(isset($batch_actions) && is_array($batch_actions) && !empty($batch_actions)):?>
			<div hc-table-actions class="dropdown">
				<button class="btn btn-default dropdown-toggle" data-toggle="dropdown">Actions for selected <span data-num></span> items <span class="caret"></span></button>
				<ul class="dropdown-menu">
<?php foreach($batch_actions as $action => $method_name):?>
<?php if(is_array($method_name)){?>
					<li><a href="<?php echo data('href',$method_name,'#')?>" data-action="<?php echo data('action',$method_name,$action)?>"><?php echo lang(data('label',$method_name))?></a></li>
<?php }elseif(is_string($method_name) ){ ?>
<?php if($method_name != '-'){?>
					<li><a href="#" data-action="<?php echo $action?>"><?php echo lang("button_".$method_name)?></a></li>
<?php }else{ ?>
					<li class="divider"></li>
<?php } ?>
<?php } ?>
<?php endforeach;?>
				</ul>
			<?php if (isset($priority_enabled) && $priority_enabled):?>
				<a href="<?php echo ($endpoint_url_prefix.'/priority')?>" class="btn btn-info"><i class="fa"></i> <?php echo lang('priority_btn')?></a>
			<?php endif;?>
			</div>
<?php endif;?>
			<div class="table-responsive">
			<table class="table cell-list table-striped table-hover table-condensed ">
				<thead>
				<?php foreach($columns as $idx => $col_info):?>

					<?php if(!empty($col_info['title'])): ?>
					<th 
					<?php if(!empty($col_info['pk']) && $col_info['pk']) echo 'data-identifier="true"'?>
					<?php echo (!empty($col_info['sortable']) && $col_info['sortable']) ? 'data-sortable="true"' : 'data-sortable="false"' ?>
					<?php echo (!empty($col_info['listing_hidden']) && $col_info['listing_hidden']) ? 'data-visible="false"' : 'data-visible="true"' ?>
					<?php echo (!empty($col_info['searchable']) && $col_info['searchable']) ? 'data-searchable="true"': 'data-searchable="false"' ?>
					<?php if(!empty($col_info['formatter'])) echo 'data-formatter="'. $col_info['formatter'].'"'?>
					data-column-id="<?php echo $col_info['data']?>"
					><?php echo $col_info['title']?></th>
				<?php else: ?>
					<th 
					data-identifier="false"
					data-column-id="<?php echo $col_info['data']?>" 
					data-formatter="<?php echo $col_info['formatter']?>" 
					data-sortable="false"
					data-searchable="false"
					><?php echo lang('tools')?></th>
				<?php endif; ?>
				<?php endforeach;?>
				</thead>
				<tbody>

				</tbody>
			</table>
			</div>
			

		</div>
	</div>
	
<?php if($is_dialog){ ?>
</div>
<?php } ?>
