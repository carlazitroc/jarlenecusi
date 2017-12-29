<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['picture_sizes'] = array();
$config['picture_sizes']['file'] = array(
	'small'=>array('width'=>80,'height'=>80,'suffix'=>'_s','crop'=>true,'scale'=>'fill','format'=>array('jpg')),
	'thumb'=>array('width'=>200,'height'=>200,'suffix'=>'_t','crop'=>true,'scale'=>'fit','format'=>array('jpg')),
	'thumbnail'=>array('width'=>200,'height'=>200,'suffix'=>'_t','crop'=>true,'scale'=>'fit','format'=>array('jpg')),
	'large'=>array('width'=>640,'suffix'=>'_l','scale'=>'fit','format'=>array('jpg')),
	'source'=>array('suffix'=>'_src'),
);
