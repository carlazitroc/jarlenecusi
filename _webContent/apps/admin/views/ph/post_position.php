<?php

$this -> title[] = lang($section.'_heading');

$this -> asset 
 -> css_code('
 
 	#sortable: after {
 		clear:both;
 		display:block;
 		width:100%; height:1px;
 		content: " ";
 	}
 	
 	#sortable .ui-state-default,
 	#sortable .ui-state-highlight{
 		width:200px;
 		height:230px;
 		float:left; 
 		display:block;
 	}
 	#sortable .ui-state-default img{width:100%;}
 ')
 
 ->js_embed('ph/post_position.js.php',null,null,'body_foot');

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
				<a href="javascrip:void(0)" class="btn btn-default" id="btn_update"><i class="fa "></i> <?php echo lang('button_update')?></a>
			</div>
			<h2><?php echo lang('priority_update');?></h2>
		</div>
	</div>
<?php }?>

	<div class="col-xs-12">
		<form id="priority_form">
		<div class="table-responsive">
			<!--<ul>
				<li class="col-xs-2 col-sm-2"><?php echo lang('field_priority')?></li>				
				<li class="col-xs-8 col-sm-8"><?php echo lang('field_content')?></li>				
			</ul>
			
			<table class="table cell-list table-striped table-hover table-condensed ">
				<!--<thead>
					<tr>
						<th class="col-xs-2 col-sm-2"><?php echo lang('field_priority')?></th>
						<th class="col-xs-8 col-sm-8"><?php echo lang('field_content')?></th>
						<th class="col-xs-2 col-sm-2"><?php echo lang('last_update')?></th>
					</tr>
				</thead>
				<tbody> -->
					<ul class="list_template" id="sortable" style="list-style-type: none; ">
						
					</ul>
			<!--	</tbody>
			</table>-->
		</div>
		</form>
	</div>
</div>

<?php if($is_dialog){?> 
	
</div>
<?php } ?>

