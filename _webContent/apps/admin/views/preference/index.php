<?php


$this->helper('string');

$this -> title[] = $page_header;

$this->asset->js_embed('preference/index.js.php',NULL,NULL,'body_foot');
?>

<?php if($is_dialog){ ?>
<div class="modal-header">
	<h4><?php echo $page_header;?></h4>
</div>

<div class="modal-footer float">
	<button type="button" class="btn btn-window-close right"><i class="fa fa-times"></i> <?php echo lang('button_cancel')?></button>
	<button type="button" class="btn btn-item-select" disabled="disabled"><i class="fa fa-check"></i> <?php echo lang('button_done')?></button>
</div>
<div class="modal-body">
<?php } ?>


<div class="row record-list">
	<div class="col-xs-12">
<form hc-form="setting" action="<?php echo site_url($view_path_prefix.'/save')?>" class="ifrm form-horizontal" method="post" enctype="multipart/form-data">
<?php if(!$is_dialog){ ?>
		<div class="page-header">
			<div class="toolbar pull-right">
				<button type="submit" class="btn btn-success btn-save"><i class="fa fa-save"></i> <?php echo lang('button_save')?></button>
	
<?php if(forward_url() !=''){ ?>
				<a class="btn btn-warning btn-link" href="<?php echo forward_url()?>"><?php echo lang('button_discard_changes')?></a>
<?php } ?>
			</div>
			<h2><?php echo $page_header;?></h2>
		</div>
<?php } ?>
	</div>


	<div class="col-xs-12">

	<input type="hidden" name="forward" value="<?php echo forward_url()?>" />
	<input type="hidden" name="do" value="save" />
	<?php 
		$this->widget('form_editor',compact('loc','data','editor_sections'))
	?>
	</div>


</form>

</div><!--/col-->

</div><!--/row-->

<?php if($is_dialog){?>
</div>
<?php } ?>