
<?php if($is_dialog){ ?>
<div class="modal-header">
	<h4><?php echo lang('error');?></h4>
</div>

<div class="modal-footer float">
	<button type="button" class="btn btn-default btn-window-close right" re-action="close"><?php echo lang('button_close')?></button>
</div>
<div class="modal-body">
<?php } ?>

<div class="row">
	<div class="col-xs-12">
		<div class="page-header">
			<h2>Page not found</h2>
		</div>

		<p>Your requested page may not exist or has been removed.</p>
<?php if($this->request->is_debug() ){?><pre><?php debug_print_backtrace();?></pre><?php } ?>
	</div>

</div>

<?php if($is_dialog){ ?>
</div>
<?php } ?>


