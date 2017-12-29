<?php

$this -> title[] = $page_header;

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
 
 ->js_embed($theme_path.'core/post_position.js.php',null,null,'body_foot');

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
				<a href="javascrip:void(0)" class="btn btn-info" id="btn_update"><i class="fa "></i> <?php echo lang('button_update')?></a>
			</div>
			<h2><?php echo lang('priority_update');?></h2>
		</div>
	</div>
<?php }?>

	<div class="col-xs-12">
		<form id="priority_form">
		<div class="table-responsive">
					<ul class="list_template" id="sortable" style="list-style-type: none; ">
						
					</ul>
		</div>
		</form>
	</div>
</div>

<?php if($is_dialog){?> 
	
</div>
<?php } ?>

