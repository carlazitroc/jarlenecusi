<?php

?>


<div class="row">
	<div class="col-xs-12">
		<div class="page-header">
			<h2><?php echo lang('dashboard_heading')?></h2>
		</div>

<?php if($this->acl->has_permission('FULL_MANAGE')):


$this->asset->js_import(site_url('welcome.js'), NULL, NULL,'body_foot');

?>
		<div class="row">
			<div class="col-xs-12">
			<button type="button" class="btn btn-danger btn-sys-cache">Clear All System Cache</button>
			</div>
		</div>
<?php endif;?>
	</div>
</div>