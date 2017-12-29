<?php


$config['ph_sections'] = array();

$section = 'staticpage';

$config['ph_section_default'] = $section;
$config['ph_sections'][$section] =array(
	'icon'=>'fa fa-folder-open',
	'listing_enabled'  => FALSE,
	'category_enabled'=>false,
	'tag_enabled'=>false,
);


/*
$section = 'project';
$config['ph_sections'][$section] =array(
	'icon'=>'fa fa-folder-open',
	'category_enabled'=>true,
	'tag_enabled'=>true,
);


$section = 'press';
$config['ph_sections'][$section] =array(
	'icon'=>'fa fa-folder-open',
	'category_enabled'=>true,
	'tag_enabled'=>true,
);

//*/