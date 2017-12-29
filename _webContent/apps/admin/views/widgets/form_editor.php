<?php 

$def_section = 'general';

$has_file = FALSE;
$has_localized = FALSE;

if(is_array($editor_sections)){
	foreach($editor_sections as $section_key =>$section_info){

		// skip if no fields assigned
		if(!isset($section_info['fields'])) continue;
		$fields = $section_info['fields'];
		foreach($fields as $idx => $field_info){
			if(isset($field_info['localized']) && $field_info['localized']){
				$has_localized = TRUE;
			}

			if(isset($field_info['control']) && $field_info['control'] == 'file'){
				$has_file = true;
			}

		}
	}
}

if($has_localized)
	$this->asset->js_embed('widgets/form_editor.js.php',compact('has_localized'),null,'body_foot');
?>

<?php if($has_localized): ?>
<div class="padd-y">
<!-- BEGIN : Localized -->
<div class="btn-group">
	<button type="button" class="btn btn-change-locale btn-md btn-default dropdown-toggle" data-toggle="dropdown"><i class="fa fa-language"></i> <?php echo lang('button_editing_language_prefix')?> <span class="loc-name"><?php echo lang('lang_'.$this->lang->locale())?></span> <span class="caret"></span></button>
	<ul class="dropdown-menu" role="dropdown">
<?php foreach($this->lang->get_available_locale_keys() as $loc_code){?>
		<li><a href="#loc-<?php echo $loc_code?>" re-action="change-locale" re-locale-name="<?php echo lang('lang_'.$loc_code)?>" re-locale="<?php echo $loc_code?>"><?php echo lang('lang_'.$loc_code)?></a></li>
<?php } ?>
	</ul>
</div>
<!-- END : Localized -->
</div>
<?php endif;?>

<!-- Nav tabs -->
<ul class="nav nav-tabs">
<?php 
if(is_array($editor_sections) && !empty($editor_sections)){
foreach($editor_sections as $section_key => $section_info){?>
  <li<?php if($def_section == $section_key) echo ' class="active"'?>><a href="#<?php echo $section_key?>" data-toggle="tab"><?php echo $section_info['title']?></a></li>
<?php }
} ?>
</ul>

<!-- Tab panes -->

<div class="tab-content">
<?php 
if(is_array($editor_sections) && !empty($editor_sections)){
	foreach($editor_sections as $section_key => $section_info){

	?>
  <div class="tab-pane padd-y<?php if($def_section == $section_key) echo ' active'?>" id="<?php echo $section_key?>">
<?php
		// if section assigned a view, use the view directly
		if(isset($section_info['view']) && !empty($section_info['view'])){

			$view_vals = compact('section_info','section_key');
			if(isset($section_info['view_data'])){
				$view_vals = array_merge($view_vals, $section_info['view_data']);
			}

			$source = $data;
			
			if(isset($section_info['value_parent_node']))
				$source = $source[ $section_info['value_parent_node'] ];


			$this->view($section_info['view'], $view_vals);

		// otherwise, we use our editor form.
		}else {
?>
<?php if(!empty($section_info['sidebar_content'])){?>
<div class="row">
<div class="<?php echo data('sidebar_class', $section_info,'col-sm-3 pull-right')?>">
<?php echo $section_info['sidebar_content'];?>
</div>
<div class="<?php echo data('body_class', $section_info,'col-sm-9')?>">
<?php } ?>

<?php 


			if(isset($section_info ['fields']) && is_array($section_info ['fields'])) {
				$fields = $section_info ['fields'];

				foreach($fields as $field_index => $field_info){


					$_input_name = isset($field_info['name']) ? $field_info['name'] : '';

					$field_raw_name = $_input_name;
					
					if(empty($field_info['control'])) $field_info['control'] = 'textarea';
					if(empty($field_info['label'])) $field_info['label'] = $_input_name;

					$field_control = $field_info['control'];

					$source = $data;
					
					if(isset($field_info['value_parent_node']))
						$source = $source[ $field_info['value_parent_node'] ];
					
					$_value = data($_input_name, $source);
					
					$is_localized = isset($field_info['localized']) && $field_info['localized'];

					$attr_id = 'si-'.random_string('alnum',16);
					$field_name = $_input_name;


					if( isset($field_info['field_name'])) $field_name = $field_info['field_name'];

					if($is_localized){
?>
			<div class="tab-content">
<?php
						foreach($this->lang->get_available_locale_keys() as $loc_code){

							$attr_id = 'si-'.random_string('alnum',16);
							$field_name = 'loc['.$loc_code.']['.$_input_name.']';
							if(!empty($field_info['value_parent_node'])){
								$field_name = 'loc['.$loc_code.']['.$field_info['value_parent_node'].']['.$_input_name.']';
							}
							//if( isset($field_info['field_name'])) $field_name = $field_info['field_name'];

							// for support mulitple language, it may passed parameter from setting
							$field_name = str_replace('{locale}',$loc_code, $field_name);
							$field_name = str_replace('{locale_code}',$loc_code, $field_name);

							$source = data($loc_code, isset($loc) ? $loc: NULL);
							if(isset($field_info['value_parent_node']))
								$source = $source[ $field_info['value_parent_node'] ];

							$value = data($_input_name, $source);

?>
<div class="localized-pane tab-pane<?php if($loc_code == $this->lang->locale()) echo ' active in'?>" re-locale="<?php echo $loc_code?>">

<?php $this->view('widgets/form_editor_group',compact('attr_id','field_control','field_raw_name','field_name','value','field_info','field_index','section_key','is_localized','loc_code')); ?>

</div>

<?php  
							
						}
?>
			</div>
<?php
					}else{
						$value = $_value;
						$loc_code = $this->lang->locale();
						
						// if view assigned, we pass the data to
						$this->view('widgets/form_editor_group',compact('attr_id','field_control','field_raw_name','field_name','value','field_info','field_index','section_key','is_localized','loc_code'));
						
					}
				} // end of foreach
			} // end of checking any fields ?>


<?php if(!empty($section_info['sidebar_content'])){?>
</div><!-- //.col -->
</div><!-- //.row -->
<?php } // end of checking editor_section.view ?>

<?php } // end of checking editor_section.view ?>

  </div>
<?php } // end of listing sections ?>



</div>
<?php } // end of checking any sections ?>