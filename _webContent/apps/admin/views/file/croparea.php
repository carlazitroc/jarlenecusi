<?php
$this->title[] = 'Crop Area Editor';
$this->asset->css(asset_url('libs/jquery-cropbox/jquery.cropbox.css'));
$this->asset->js_import(asset_url('libs/jquery-cropbox/jquery.cropbox.js'));
$this -> asset -> js_import(site_url('file/croparea.js'));
?>
<?php if($is_dialog){ ?>	
<div class="toolbar navbar<?php if($is_dialog) echo ' navbar-fixed-top';?>">
	 <div class="navbar-header btn-only">
	 	
		<button type="button" class="btn btn-default btn-window-close right" re-action="close"><i class="fa fa-times"></i> <?php echo lang('button_cancel')?></button>
		<button type="button" class="btn btn-primary"><i class="fa fa-check"></i> <?php echo lang('button_done')?></button>
	</div>	
</div>
<?php }else{?>
<h1>Crop Area Editor</h1>
<?php } ?>
<?php if($is_dialog){ ?>	
<div id="main" class="container">
<?php }?>
<div class="cropbox" data-callback="<?php echo strip_tags($this->input->get('callback'));?>" data-width="<?php echo $tar_width?>" data-height="<?php echo $tar_height?>" data-crop-area="<?php echo $src_croparea?>">
	<img src="<?php echo site_url('file/'.$file_id.'/picture?size=source')?>" />
</div>

<?php if($is_dialog){ ?>	
</div>
<?php }?>