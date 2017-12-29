<?php

if(!defined('LOCALIZED_HELPER_PARSE_PATTERN')) 
	define('LOCALIZED_HELPER_PARSE_PATTERN','/^([a-z]{2}[\_\-][A-Za-z]{2}|[a-z]{2})$/');

function valid_locale_string($str=''){
	return preg_match(LOCALIZED_HELPER_PARSE_PATTERN,$str) ;
}

function parse_locale($loc_str=''){
	if(!valid_locale_string($loc_str)) return NULL;
	
	if(preg_match(LOCALIZED_HELPER_PARSE_PATTERN,$loc_str,$matches)){
		if(count($matches)>0){
			
			$nodes = preg_split("/[\-\_]/",$loc_str,3);
			
			$tmp_loc = strtolower( $nodes[0]);
			if(count($nodes) > 1){
				$tmp_loc = strtolower($nodes[0]).'_'.strtoupper($nodes[1]);
			}
			
			return array('locale'=>$tmp_loc);
		}
	}
	
	return NULL;
}

function is_locale_supported($loc_str)
{
	$CI = &get_instance();
	if(!$CI)return FALSE;
	
	$CI->load->model('country_model');
	
}

function localized_save($ref_table, $ref_id, $loc_code, $loc_data, $is_live = '0', $edit_info= NULL, $locale_fields = false){
	$CI = &get_instance();
			// multiple language part
			// required model
	$CI->load->model('text_locale_model');


	if(empty($locale_fields))
		$locale_fields = array('title','description','content','parameters','status');

	// get all localized content for this record
	$curr_loc_row = $CI->text_locale_model->read(array('ref_table'=>$ref_table,'ref_id'=>$ref_id, 'is_live'=>$is_live, 'locale'=> $loc_code));

	$sql_loc_data = array();
	foreach($locale_fields as $idx => $field_name){
		if(isset($loc_data[$field_name]))
			$sql_loc_data [$field_name] = $loc_data[$field_name];
	}

	// if no localized id for this record, then create it
	if(empty($curr_loc_row['id'])){
		$sql_loc_data['ref_table'] = $ref_table;
		$sql_loc_data['ref_id'] = $ref_id;
		$sql_loc_data['is_live'] = $is_live;
		$sql_loc_data['locale'] = $loc_code;
		$CI->text_locale_model->save($sql_loc_data, NULL, $edit_info);

	}else{
		$CI->text_locale_model->save($sql_loc_data, array('id'=>$curr_loc_row['id'],'is_live'=>'0','locale'=>$loc_code), $edit_info);
	}

	return TRUE;
}
