<?php

$element_id = random_string('alnum',16);

$this->asset->js_embed('user/admin/permission_relationship_editor.js.php', compact('element_id'), NULL, 'body_foot');
?>

<div hc-elm="<?php echo $element_id?>">
	<div class="list-group-add">
		<button class="btn btn-default" type="button">
		<?php echo lang('button_add_permission')?>
		</button>
	</div>

	<div class="table-responsive">
	<table class="table table-rounded table-border">
	<thead>
		<tr>
			<th class="col-xs-1"><i class="fa fa-cogs"></i></th>
			<th class="col-xs-6"><?php echo lang('field_permission_id')?></th>
			<th class="col-xs-2"><?php echo lang('field_start_date')?></th>
			<th class="col-xs-2"><?php echo lang('field_end_date')?></th>
			<th class="col-xs-1"><?php echo lang('field_status')?></th>
		

		</tr>
	</thead>
	<tbody>
	</tbody>
	</table>
	</div>
	<textarea hc-elm-input name="<?php echo $field_name?>" class="hidden"><?php echo data($field_name, $data)?></textarea>

	<script type="text/x-javascript-template" data-template="row">
		<tr>
			<td>
				<div class="btn-group">
					<button type="button" class="btn btn-xs btn-default" rr-action="edit"><i class="fa fa-pencil"></i></button>
					<button type="button" class="btn btn-xs btn-danger" rr-action="remove"><i class="fa fa-times"></i></button>
				</div>
			</td>
			<td><span rr-text="permission_name"></span></td>		
				<td><span rr-text="!row.start_date? 'N/A' : row.start_date"></span></td>
				<td><span rr-text="!row.end_date ? 'N/A' : row.end_date"></span></td>
			<td>
			<span rr-if="value != '1'" class="label label-info"><?php echo lang('status_0')?></span>
			<span rr-if="value == '1'" class="label label-success"><?php echo lang('status_1')?></span>
			</td>
		</tr>
	</script>

	<div hc-dialog-template="permission-form" class="modal">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">

	       			<h4 class="modal-title">Permission Option</h4>
				</div>
				<div class="modal-body">
					<form class="form" method="post" enctype="multipart/form-data">
						<div class="form-group">
							<label class="control-label"><?php echo lang('field_start_date')?></label>
							<?php echo form_input(array('class'=>'form-control datetimepicker','hc-value'=>'start_date')) ?>
						</div>
						<div class="form-group">
							<label class="control-label"><?php echo lang('field_end_date')?></label>
							<?php echo form_input(array('class'=>'form-control datetimepicker','hc-value'=>'end_date')) ?>
						</div>

						<div class="form-group">
							<label class="control-label"><?php echo lang('field_status')?></label>
							<?php echo form_dropdown('',array('0'=>'Disabled','1'=>'Enabled'),'1', ' class="form-control" hc-value="value"') ?>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo lang('button_cancel');?></button>
					<button type="button" class="btn btn-success" hc-action="save"><?php echo lang('button_done');?></button>
				</div>
			</div>
		</div>
	</div>
</div>