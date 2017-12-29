<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['picture_sizes'] = array();
$config['picture_sizes']['file'] = array(
	'thumb'=>array('width'=>200,'height'=>200,'suffix'=>'_t','crop'=>true,'scale'=>'fit','format'=>array('jpg')),
	'large'=>array('width'=>640,'suffix'=>'_l','crop'=>true,'scale'=>'fit','format'=>array('jpg')),
	'source'=>array('suffix'=>'_src'),
);

// Sample
/*
$config['picture_sizes']['product'] = array(
	'thumbnail'=>array(
		'width'=>128,'height'=>128,'suffix'=>'_t','crop'=>true,'scale'=>'fill',
		'format'=>array(
			'jpg'
		)
	),
	'large'=>array(
		'width'=>800,'height'=>800,'suffix'=>'_l','scale'=>'fill','format'=>array(
			'jpg'
		)
	),
	'cover'=>array(
		'width'=>200,'suffix'=>'_cover','scale'=>'fit',
		'format'=>array(
			'jpg'
		)
	),
	'source'=>array(
		'suffix'=>'_src',
		'format'=>array('jpg','png'),
	),
);
//*/