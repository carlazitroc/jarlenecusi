<?php

$this->title[] = lang($section . '_heading');

$this->asset->js_embed('ph/post_editor.js.php', null, null, 'body_foot');

if (isset($record['id'])) {
	$this->title[] = empty($record['title']) ? $record['id'] : $record['title'];
} else {
	$this->title[] = lang('create');
}
?>
 
<?php if ($is_dialog) {?>
<div class="modal-header">
	<h4><?php echo lang($section . '_heading');?></h4>
</div>

<div class="modal-footer float">
	<button type="button" class="btn btn-default btn-window-close right"><i class="fa fa-times"></i> <?php echo lang('button_close')?></button>
	<button type="button" class="btn btn-success" re-action="save"><i class="fa fa-save"></i> <?php echo lang('button_save')?></button>
	<button type="button" class="btn btn-primary hidden visible-edit" re-action="publish"><i class="fa fa-cloud-upload"></i> <?php echo lang('button_publish')?></button>
</div>
<div class="modal-body">
<?php }?>

<form action="<?php echo site_url('s/' . $section . '/post/save')?>" class="ifrm form" method="post" enctype="multipart/form-data">
<input type="hidden" name="id" readonly="readonly" value="<?php if (!empty($id)) echo $id?>" />
<input type="hidden" name="forward" value="<?php echo forward_url()?>" />
<input type="hidden" name="do" value="save" />


<div class="row">

<?php if (!$is_dialog) {?>
	<div class="col-xs-12">
		<div class="page-header">
			<div class="toolbar">
<?php if (forward_url() != '') {?>
				<a class="btn btn-link" href="<?php echo forward_url()?>"><i class="fa fa-times"></i> <?php echo lang('button_discard_changes')?></a>
<?php } else {?>
				<a class="btn btn-link" href="<?php echo site_url('s/' . $section . '/post')?>"><i class="fa fa-arrow-left"></i> <?php echo lang('button_list')?></a>
<?php }?>
			</div>
			<div class="pull-right toolbar">
			<div class="btn-group">
				<button type="button" class="btn btn-success" re-action="save"><i class="fa fa-save"></i> <?php echo lang('button_save')?></button>
				<button type="button" class="btn btn-primary hidden visible-edit" re-action="publish"><i class="fa fa-cloud-upload"></i> <?php echo lang('button_publish')?></button>
			</div>
				<a target="_blank" href="<?php if(isset($preview_url)) echo $preview_url?>" re-href="preview_url" class="btn btn-default hidden visible-edit"><?php echo lang('preview')?></a>

			</div>
			<h2><?php echo lang($section . '_heading')?></h2>
		</div>
	</div>
<?php }?>
	<div class="col-xs-12"><?php echo lang('form_required_field')?></div>
	<div class="col-xs-12">
<?php 

		
		$editor_sections = array();
		$editor_sections['general']=array(
			'title'=>'General',
		);
		$editor_sections['general']['fields'][] = array(
			'name'=>'status',
			'label'=>lang('field_status'),
			'control'=>'select',
			'description'=>lang('field_status_description'),
			'options'=>array(
				'0'=>lang('status_0'),
				'1'=>lang('status_1'),
				'2'=>lang('status_2'),
			),
			'localized'=>FALSE,
			'attributes'=>array(
			),
		);

		$editor_sections['general']['fields'][] = array(
			'name'=>'title',
			'label'=>lang('field_title'),
			'description'=>lang('field_title_description'),
			'control'=>'text',
			'required'=>'',
			'localized'=>$this->ph->is_localized,
			'attributes'=>array(
				're-required'=>'',
			),
		);
		if($this->ph->config('post_cover_enabled')  !== FALSE){


			$editor_sections['general']['fields'][] = array(
				'name'=>'cover_id',
				'label'=>lang('field_cover_id'),
				'control'=>'select',
				'description'=>lang('field_cover_description'),
				'name'=>'cover_id',
				'control_type'=>'file',
				'localized'=>$this->ph->is_localized,
				'is_image'=>TRUE,
			);
		}
		
		
		if($this->ph->config('post_priority_enabled')  !== FALSE){


			$editor_sections['general']['fields'][] = array(
				'name'=>'priority',
				'label'=>lang('field_priority'),
				'control'=>'text',
				'description'=>lang('field_priority_description'),
				'control_type'=>'number',
			);
		}

 		$editor_sections['general']['fields'][] = array(
			'name'=>'description',
			'label'=>lang('field_description'),
			'control'=>'text',
			'localized'=>$this->ph->is_localized,
			'description'=>lang('field_description_description'),
			'attributes'=>array(
			),
		);

 		$editor_sections['general']['fields'][] = array(
			'name'=>'slug',
			'label'=>lang('field_slug'),
			'control'=>'text',
			'localized'=>FALSE,
			'description'=>lang('field_slug_description'),
			'addon_prefix'=>'<div class="input-group-addon">'. (!$this->ph->is_default  ? $section.'/':'/') .'</div>',
			'attributes'=>array(
			),
		);
		
 		$editor_sections['general']['fields'][] = array(
			'name'=>'publish_date',
			'label'=>lang('field_publish_date'),
			'control'=>'text',
			'type'=>'datetime',
			'description'=>lang('field_publish_date_description'),
			'localized'=>FALSE,
			'attributes'=>array(
			),
		); 

		if ($this->ph->is_category_enabled) {

			$editor_sections['general']['fields'][] = array(
				'name'=>'category_id',
				'label'=>lang('field_category'),
				'control'=>'select',
				'type'=>'remote',
				'url'=>site_url('/s/'.$section.'/category/search.json'),
				'localized'=>FALSE,
			);
		}

		if ($this->ph->is_tag_enabled) {

			$editor_sections['general']['fields'][] = array(
				'name'=>'tag_ids',
				'label'=>lang('field_tag'),
				'control'=>'text',
				'type'=>'tag',
				'localized'=>FALSE,
				'list_url'=>site_url('s/'.$section.'/tag/search.json?autocomplete=yes'),
				'search_url'=>site_url('s/'.$section.'/tag/search.json?autocomplete=yes&q=%QUERY'),
				'save_url'=>site_url('s/'.$section.'/tag/save.json'),
				'description'=>'Press enter to add or select existing tag from dropdown list.',
			);
		}

 		$editor_sections['general']['fields'][] = array(
			'name'=>'content',
			'label'=>lang('field_content'),
			'control'=>'textarea',
			'control_type'=>'rich',
			'localized'=>$this->ph->is_localized,
			'attributes'=>array(
			),
			'config'=>array(
				'menu'=>false,
				'content_css'=>base_url('assets/css/editor/'.$section.'.css'),
			),
		);
 
		if ($this->ph->is_media_enabled) {
			if($this->ph->config('post_album_enabled')  !== FALSE){

			$editor_sections['gallery']=array(
				'title'=>'Gallery',
			);

			if($this->ph->config('post_album_enabled')  !== FALSE){
				$editor_sections['gallery']['fields'][] = array(
					'name'=>'album_id',
					'control'=>'gallery',
				);
			}
		}
		}

		if(!empty($parameter_fields)){

			$editor_sections['extra']=array(
				'title'=>'Extra',
			);
			foreach($parameter_fields as $p_field_name => $p_field_info){

				$field_info = array_merge(array(
					'name'=>$p_field_name,
					'field_name'=>'parameters['.$p_field_name.']',
					'control'=>'custom',
					'value_parent_node' =>'parameters',
					'localized'=> !empty($p_field_info['is_localized']) && $p_field_info['is_localized'] ,
					'view_data'=>$p_field_info,
				),$p_field_info);

				if(!empty($p_field_info['is_localized']) && $p_field_info['is_localized']){
					$field_info['field_name'] = 'loc[{locale_code}][parameters]['.$p_field_name.']';
				}
				$editor_sections['extra']['fields'][] = $field_info;

			}
		}

		$this->widget('form_editor',compact('loc','data','editor_sections'))

	?>
	</div>


	</div><!--/row-->

</form>
<?php if ($is_dialog) {?></div><?php }?>

