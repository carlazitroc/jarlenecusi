
<?php if(!empty($parameter_view)){

	$this->view($parameter_view);
} else{ ?>
<?php if($this->ph->is_locale_enabled){?>
		<div class="tab-content">
<?php foreach($this->lang->get_available_locale_keys() as $loc_code){?>
	  		<div class="tab-pane fade<?php if($loc_code == $this->lang->locale()) echo' active in';?>" re-locale="<?php echo $loc_code?>">
				
					
<!-- Localized Parameters Value -->
<?php if(!empty($parameter_fields)){?>
<?php foreach($parameter_fields as $p_field => $p_info){

	if(!isset($p_info['form_type'])) continue;

	$def_val =  data('default_value',$p_info,'');

	if(!empty($loc[$loc_code]['parameters'][$p_field]) )
		$def_val = $loc[$loc_code]['parameters'][$p_field];

	$field_label = data('field_label',$p_info,'field_'.$p_field);

	if($p_info['form_type'] == 'custom' ){

		$this->view($p_info['view'], array('field_name'=>'loc['.$loc_code.'][parameters]['.$p_field.']','field_value'=>$def_val));
		continue;
	}

	?>
<?php if(empty($p_info['is_locale_enabled']) || !$p_info['is_locale_enabled']) continue;?>
					<div class="form-group">
						<label class="control-label"><?php echo lang($field_label)?></label>
<?php if($p_info['form_type'] == 'dropdown'){


	?>
						
<?php echo form_dropdown("loc[$loc_code][parameters][$p_field]", data('values',$p_info,array()),$def_val,' class="form-control"');?>

<?php
}elseif($p_info['form_type'] == 'radio'){


	foreach($p_info['values'] as $pv_value => $pv_info){

		$label = $pv_info['label'];
		$value = $pv_value;
		$text = $label;
		if(!empty($p_info['label_template']) && (!isset($pv_info['use_template']) || $pv_info['use_template'] !== false)){
			$text = $p_info['label_template'];
			$text = str_replace('{value}',$value, $text);
			$text = str_replace('{label}',$label, $text);
		}
	?>
	
						<div class="radio">
							<label>
								<input type="radio" name="loc[<?php echo $loc_code?>][parameters][<?php echo $p_field?>]" value="<?php echo $pv_value?>"<?php if($def_val == $pv_value) echo ' checked="checked"'?> /> <?php echo $text?>
							</label>
						</div>
<?php } ?>
<?php

}else{
	?>

						<input type="<?php echo $p_info['form_type']?>" class="form-control" name="loc[<?php echo $loc_code?>][parameters][<?php echo $p_field?>]" value="<?php echo $def_val?>" />
						

<?php } ?>
<?php if(!empty($p_info['help'])) {?>
						<div class="help-inline"><?php echo $p_info['help']?></div>
<?php } ?>
					</div>

<?php }?>
<?php }?>
<!-- Localized Parameters Value -->
			</div>

<?php } ?>
		</div>
<?php } ?>
	

<!-- Non-localized Parameters Value -->
<?php if(!empty($parameter_fields)){?>
<?php foreach($parameter_fields as $p_field => $p_info){

	if(!isset($p_info['form_type'])) continue;

	$def_val =  data('default_value',$p_info,'');

	if(!empty($data['parameters'][$p_field]) )
		$def_val = $data['parameters'][$p_field];

	$field_label = data('field_label',$p_info,'field_'.$p_field);

	if($p_info['form_type'] == 'custom' ){

		$this->view($p_info['view'], array('field_name'=>'parameters['.$p_field.']','field_value'=>$def_val));
		continue;
	}
	?>
<?php if(!empty($p_info['is_locale_enabled']) && $p_info['is_locale_enabled']) continue;?>
					<div class="form-group">
						<label class="control-label"><?php echo lang($field_label)?></label>
<?php if($p_info['form_type'] == 'dropdown'){


	?>
						
<?php echo form_dropdown("parameters[$p_field]", data('values',$p_info,array()),$def_val,' class="form-control"');?>

<?php
}elseif($p_info['form_type'] == 'radio'){


	foreach($p_info['values'] as $pv_value => $pv_info){

		$label = $pv_info['label'];
		$value = $pv_value;
		$text = $label;
		if(!empty($p_info['label_template']) && (!isset($pv_info['use_template']) || $pv_info['use_template'] !== false)){
			$text = $p_info['label_template'];
			$text = str_replace('{value}',$value, $text);
			$text = str_replace('{label}',$label, $text);
		}
	?>

						<div class="radio">
							<label>
								<input type="radio" name="parameters[<?php echo $p_field?>]" value="<?php echo $pv_value?>" <?php if($def_val == $pv_value) echo ' checked="checked"'?> /> <?php echo $text?>
							</label>
						</div>
						<?php if(!empty($p_info['help'])) {?>
						<div class="help-inline"><?php echo $p_info['help']?></div>
						<?php } ?>
<?php } ?>
<?php 

}else{
	?>

						<input type="<?php echo $p_info['form_type']?>" class="form-control" name="parameters[<?php echo $p_field?>]" value="<?php echo $def_val?>" />
						
<?php } ?>
<?php if(!empty($p_info['help'])) {?>
						<div class="help-inline"><?php echo $p_info['help']?></div>
<?php } ?>
					</div>

<?php }?>
<?php }?>
<!-- Non-localized Parameters Value -->

<?php } // end of if-cond for parameter_view ?>