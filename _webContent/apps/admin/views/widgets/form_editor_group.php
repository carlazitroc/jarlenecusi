<?php

$_pre_desc_controls = array();
$_no_label_controls = array('gallery','separator','custom');

$label_class = ' col-md-2 col-sm-3';
$control_class = ' col-md-10 col-sm-9';
$help_class = ' col-md-10 col-sm-9 col-md-offset-2 col-sm-offset-3';

//if(!empty($field_info['description'])){
	if( in_array($field_control, $_pre_desc_controls)  ){
		$help_class = $control_class;
	}else{
		$control_class = ' col-md-6 col-sm-9';
		$help_class = ' col-md-offset-0 col-md-4 col-sm-offset-3 col-sm-9';
	}
//}

if(!isset($is_localized)) $is_localized = false;

if(!isset($attr_id)){
	$attr_id = random_string('alnum',16);
}
if(empty($field_info['label'])){
	$control_class = '';
}

?>
<div class="form-group" dynamotor-editor-group-field="<?php echo $field_name?>">

<?php if(!in_array($field_control,$_no_label_controls)){ ?>
<?php if(!empty($field_info['label'])){ ?>
    <label for="<?php echo $attr_id?>" class="control-label<?php echo $label_class?>">
	    <?php echo $field_info['label']?>
	    <?php if(isset($field_info['required'])){
    		echo ' * ';
	    }?>
	    <?php if($is_localized) echo ' <i class="fa fa-language"></i>'?>
    </label>
<?php } ?>
<?php } ?>

<?php if( in_array($field_control, $_pre_desc_controls) && !empty($field_info['description'])){?>
	<div class="<?php echo $help_class?>">
    <div class="help-block" style="margin-top:0;">
    	<?php echo $field_info['description']?>
    </div>
    </div>
    <hr class="clear"/>
<?php } // end of description ?>

<?php if($field_control == 'text'){

	if(is_array($value)) $value = implode(',', $value);

	$attrs = array(
		'name'=>$field_name,
		'id'=>$attr_id,
		'value'=> $value,
		'class'=>'form-control',
		're-field'=>$field_raw_name,
	);
	if(isset($field_info['required'])) $attrs['re-required'] = '';
	if(isset($field_info['constraint'])) $attrs['maxlength'] = $field_info['constraint'];
	if(isset($field_info['placeholder'])){
		$attrs['placeholder'] = $field_info['placeholder'];
	}
	if(!empty($field_info['attributes'])) {
		foreach($field_info['attributes'] as $attr_name => $attr_val){
			$attrs [$attr_name] = slang($attr_val, array('locale'=>$loc_code,'locale_code'=>$loc_code,'loc_code'=>$loc_code));
		}
	}


	if(isset($field_info['control_type'])){
		if($field_info['control_type'] == 'tag'){

			$this->asset->js_embed('widgets/form_editor_tag.js.php', array(
				'selector'=> '#'.$attr_id,
				'list_url' => data('list_url',$field_info),
				'search_url' => data('search_url',$field_info),
				'save_url' => data('save_url',$field_info),
			),null,'body_foot');
                }elseif($field_info['control_type'] == 'time'){        
                        $attrs['class'] .= ' datetimepicker';
			$attrs['data-format'] = 'HH:mm';
                        $attrs['timeOnly'] = 'true';
                        
		}elseif($field_info['control_type'] == 'date'){
			$attrs['class'] .= ' datetimepicker';
			$attrs['data-format'] = 'YYYY-MM-DD';
		}else if($field_info['control_type'] == 'datetime'){
			$attrs['class'] .= ' datetimepicker';
			$attrs['data-format'] = 'YYYY-MM-DD HH:mm:ss';
		}else if($field_info['control_type'] == 'time'){
			$attrs['class'] .= ' timepicker';
		}else if($field_info['control_type'] == 'color'){
			$attrs['class'] .= ' colorpicker';
		}else if(in_array($field_info['control_type'], array('email','password','number','tel'))){
			$attrs['type'] = $field_info['control_type'];
		}

	}
	
	$has_addon = FALSE;

	if(!empty($field_info['addon_prefix'])) $has_addon = TRUE;
	if(!empty($field_info['addon_suffix'])) $has_addon = TRUE;
	//if($is_localized) $has_addon = TRUE;
?>
    <div class="controls<?php echo $control_class?>">

    <?php if($has_addon): ?>
		<div class="input-group">
    		<?php if(!empty($field_info['addon_prefix'])) echo $field_info['addon_prefix'] ;?>
<?php endif;?>
    	<?php echo form_input($attrs);?>
    <?php if($has_addon): ?>

    		<?php if(!empty($field_info['addon_suffix'])) echo $field_info['addon_suffix'] ;?>

    	</div>
    <?php endif; ?>
    <?php if($is_localized):?>
    	<hr class="clear"/>
<button class="btn btn-default btn-sm add-on-btn" data-toggle="copy-value" data-name="<?php echo data('name', $field_info)?>" data-field="<?php echo $field_name?>" data-locale="<?php echo $loc_code?>"><i class="fa fa-copy"></i> <?php echo lang('copy_to_other_language')?></button>
    <?php endif ;?>
    </div>



<?php } // end of control == text ?>

<?php if($field_control == 'textarea'){

?>
    <div class="controls<?php echo $control_class?>">
<?php
	if(isset($field_info['control_type']) && $field_info['control_type'] == 'rich'){

		$attrs = array(
			'class'=>'form-control',
			'id'=>$attr_id,
			're-field'=>$field_raw_name,
		);
		if(isset($field_info['required'])) $attrs['hc-required'] = '';

		$this->widget('form_rich_editor', array(
			'field_name'=>$field_name,
			'value'=>$value,
			'config'=> data('config', $field_info),
			'attributes'=>$attrs, 
		));
	}else{

		$attrs = array(
			'name'=>$field_name,
			'id'=>$attr_id,
			'value'=>$value,
			'class'=>'form-control',
			're-field'=>$field_raw_name,
		);
		if(isset($field_info['required'])) $attrs['hc-required'] = '';

		echo form_textarea($attrs);
	}
	?>
    <?php if($is_localized):?>
    	<hr class="clear"/>
<button class="btn btn-default btn-sm" data-toggle="copy-value" data-name="<?php echo data('name', $field_info)?>" data-field="<?php echo $field_name?>" data-locale="<?php echo $loc_code?>"><i class="fa fa-copy"></i> <?php echo lang('copy_to_other_language')?></button>
    <?php endif ;?>
    </div>

<?php } // end of control == textarea ?>

<?php if($field_control == 'bool' || $field_control == 'boolean'){?>
	
    <div class="controls<?php echo $control_class?>">
	<div class="radio">
	<label>
      <input type="radio" name="<?php echo $field_name?>" re-field="<?php echo $field_raw_name?>" value="1"<?php if($value == '1') echo ' checked=""'?>/> <?php echo lang('yes')?>
    </label>
    </div>
	<div class="radio">
	<label>
      <input type="radio" name="<?php echo $field_name?>" re-field="<?php echo $field_raw_name?>" value="0"<?php if($value != '1') echo ' checked=""'?>/> <?php echo lang('no')?>
    </label>
    </div>
	</div>


<?php } // end of control == bool ?>

<?php
if($field_control == 'gallery'){

	$this->widget('form_gallery_editor',array('field_name'=> $field_name,'value'=>$value,'localized'=> data('control_localized', $field_info, false))); 
} // end of control == gallery ?>

<?php if($field_control == 'radio'){?>

    <div class="controls<?php echo $control_class?>">
	<?php 

	foreach($field_info['values'] as $pv_value => $pv_info){

		$label = $pv_info['label'];
		$value = $pv_value;
		$text = $label;
		if(!empty($field_info['label_template']) && (!isset($pv_info['use_template']) || $pv_info['use_template'] !== false)){
			$text = $field_info['label_template'];
			$text = str_replace('{value}',$value, $text);
			$text = str_replace('{label}',$label, $text);
		}
	?>

		<div class="radio">
			<label>
				<input type="radio" name="<?php echo $field_name?>" re-field="<?php echo $field_raw_name?>" value="<?php echo $pv_value?>" <?php if($value == $pv_value) echo ' checked="checked"'?> /> <?php echo $text?>
			</label>
		</div>

<?php 
	}
	?>
	</div>



<?php } // end of control == radio ?>


<?php if($field_control == 'checkbox'){?>

    <div class="controls<?php echo $control_class?>">
	<?php 

	foreach($field_info['values'] as $pv_value => $pv_info){

		$label = $pv_info['label'];
		$value = $pv_value;
		$text = $label;
		if(!empty($field_info['label_template']) && (!isset($pv_info['use_template']) || $pv_info['use_template'] !== false)){
			$text = $field_info['label_template'];
			$text = str_replace('{value}',$value, $text);
			$text = str_replace('{label}',$label, $text);
		}
	?>

		<div class="checkbox">
			<label>
				<input type="checkbox" name="<?php echo $field_name?>[]" re-field="<?php echo $field_raw_name?>" value="<?php echo $pv_value?>" <?php if($value == $pv_value) echo ' checked="checked"'?> /> <?php echo $text?>
			</label>
		</div>

<?php 
	}
	?>
	</div>


<?php } // end of control == checkbox ?>

<?php if($field_control == 'selector'){?>

    <div class="controls<?php echo $control_class?>">

<?php $this->widget('form_simple_selector', array(
		'field_name'=> $field_name ,
		'url'=> data('url', $field_info),
		'row_url'=> data('row_url', $field_info),
		'row_value'=>data('row_value',$field_info,'id'),
		'row_label'=> data('row_label', $field_info,'title'),
		'value'=>$value,
		'attributes'=>array(
			're-field'=>$field_raw_name,
		),
	)); ?>
	</div>
<?php } // end of control == selector ?>

<?php if($field_control == 'select'){?>

<?php if(data('control_type',$field_info) == 'chainable'){?>

    <div class="controls<?php echo $control_class?>">

<?php echo chained_combobox($field_name, data('options', $field_info, array()), $value, array(
			'root'=>lang('root'),
			'startLevel'=>1,
			'attribute'=>' class="form-control" re-field="'.$field_raw_name.'"',
			)); ?>
	</div>


<?php }elseif(data('control_type',$field_info) == 'remote'){?>

    <div class="controls<?php echo $control_class?>">
	<?php 
	$this->widget('form_chosen',array(
		'field_name'=>$field_name,
		'value'=>$value,
		'remote_url'=>data('url',$field_info,NULL),
		'multiple'=>data('multiple',$field_info,FALSE),
		'row_value'=>data('row_value',$field_info,'id'),
		'row_label'=>data('row_title',$field_info,'title'),
		'attributes'=>array(
			're-field'=>$field_raw_name,
		),
	));?>
	</div>


<?php }elseif(data('control_type',$field_info) == 'album'){ ?>

    <div class="controls<?php echo $control_class?>">
<div style="max-width:240px;">
<?php
	$this->widget('form_album', array(
		'field_name' => $field_name,
		'is_image'   => true,
		'value'=> $value,
		'attributes'=>array(
			're-field'=>$field_raw_name,
		),
	));
?>
</div>
	</div>
    <hr class="clear"/>
<?php }elseif(data('control_type',$field_info) == 'file'){ ?>
    <div class="controls<?php echo $control_class?>">
<div style="max-width:240px;">
<?php
	$this->widget('form_upload', array(
		'field_name' => $field_name,
		'is_image'   => data('is_image',$field_info,false),
		'value'=> $value,
		'attributes'=>array(
			're-field'=>$field_raw_name,
		),
	));
?>
</div>
    <?php if($is_localized):?>
    	<hr class="clear"/>
<button class="btn btn-default btn-sm add-on-btn" data-toggle="copy-value" data-name="<?php echo data('name', $field_info)?>" data-field="<?php echo $field_name?>" data-locale="<?php echo $loc_code?>"><i class="fa fa-copy"></i> <?php echo lang('copy_to_other_language')?></button>
    <?php endif ;?>
	</div>

<?php }else{ ?>


    <div class="controls<?php echo $control_class?>">
	<?php echo form_dropdown($field_name, data('options', $field_info, array()), $value, ' class="form-control"');?>
	</div>

<?php } ?>

<?php } // end of control == select ?>

<?php if($field_control == 'file'){?>

    <div class="controls<?php echo $control_class?>">
	<?php $this->widget('form_upload',array(
		'field_name'=>$field_name,
		'is_image'   => data('is_image',$field_info,false),
		'value'=>$value
	));?>
	</div>


<?php } // end of control == file ?>

<?php if($field_control == 'separator'){ ?>
	<?php if(data('control_type', $field_info) == 'header'){?>
<div class="col-sm-12">

	<h2><?php echo $field_info['label']?></h2>
<?php if(!empty($field_info['content'])){?>

<?php print $field_info['content']?>

<?php }?>

</div>
	<?php }else{?>
	<?php }?>
	<hr />

<?php }// end of control == separator ?>

<?php if($field_control == 'plain'){ ?>

<?php if(data('control_type', $field_info) == 'html') :?>

    <div class="controls<?php echo $control_class?>" re-html="<?php echo $field_name?>">
    <?php echo ($value) ?>
    </div>
<?php else:?>
    <div class="controls<?php echo $control_class?>" re-text="<?php echo $field_name?>">
    <?php print htmlentities($value) ?>
    </div>
<?php endif;?>

<?php }// end of control == plain ?>

<?php if($field_control == 'custom'){ ?>
<?php if(!empty($field_info['view'])) $this->view($field_info['view'], array('field_name'=> $field_name, 'value'=>$value, 'attr_id'=>$attr_id))?> 

<?php }// end of control == custom ? ?>


<?php if(!in_array($field_control, $_pre_desc_controls) && !empty($field_info['description'])){?>
    <div class="<?php echo $help_class?>">
    <div class="help-block">
    	<?php echo $field_info['description']?>
    </div>
    </div>
<?php } // end of description ?>
    <hr class="clear"/>

  </div>