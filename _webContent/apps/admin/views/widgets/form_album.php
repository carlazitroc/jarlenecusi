<?php

$this -> asset -> css(base_url('assets/css/ifuploader.min.css'));
//$this -> asset -> js_import(base_url("assets/libs/core/ifuploader.min.js"),NULL,'body_foot');

$this->asset->js_embed($widget_path.'form_upload.js.php',compact('element_id','field_name'),null,'body_foot');

?>

				<div hc-elm="<?php echo $element_id?>" class="hc-album-selector">
					<div class="input-group">
						<input type="hidden" name="<?php echo $field_name?>" value="<?php echo isset($data[$field_name]) ? $data[$field_name]  :'' ?>" />
						<div class="btn-group">
							<button type="button" class="btn btn-default btn-select"><?php echo lang('button_select')?></button>
							<button type="button" class="btn btn-default btn-edit hidden"><?php echo lang('button_edit')?></button>
							<button type="button" class="btn btn-default btn-create"><?php echo lang('button_create')?></button>
						</div>
					</div>
					<div class="preview-box auto-height empty">
						<img src="<?php echo base_url('assets/img/spacer.gif')?>" class="spacer" />
						<div class="body"></div>
						<span class="remark empty"><?php echo lang('no_selected_album')?></span>
					</div>
				</div>
