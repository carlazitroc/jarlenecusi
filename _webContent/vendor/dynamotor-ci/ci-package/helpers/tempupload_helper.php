<?php

function tempupload_get_dir(){
	return PRV_DATA_DIR.DS.'temp'.DS.date('Ymd');
}

function tempupload_add($new_data){

	get_instance()->load->helper('string');
	$tmp_id = random_string('alnum',32);

	$rst = tempupload_get_all();
	$rst['items'][$tmp_id] = $new_data;
	get_instance()->session->set_userdata('tempfiles', $rst);

	return $tmp_id;
}

function tempupload_get($id){
	$rst = tempupload_get_all();

	if(isset($rst['items'][$id])){
		return $rst['items'][ $id ];
	}
	return NULL;
}

function tempupload_remove($id){
	$rst = tempupload_get_all();

	if(isset($rst['items'][$id])){


		$path = realpath($rst['items'][$id]['full_path']);

		if(!substr($path,0, strlen(PRV_DATA_DIR)) == PRV_DATA_DIR){
			return FALSE;
		}

		if(file_exists($path) ){
			log_message('info','tempupload_remove, file remove for id '.$id.' : '.$path);
			@unlink($path);
		}

		unset($rst['items'][$id]);
		get_instance()->session->set_userdata('tempfiles', $rst);
		return TRUE;
	}
	return FALSE;
}

function tempupload_get_all(){
	$rst = get_instance()->session->userdata('tempfiles');
	return !empty($rst) ? $rst : array('items'=>array());
}