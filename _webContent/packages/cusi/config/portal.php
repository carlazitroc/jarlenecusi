<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['web_url'] = '/';
$config['resource_url'] = $config['web_url'];
$config['portal_url']	= $config['web_url'];
$config['pub_url']	= $config['web_url'].'pub/';
$config['asset_url']	= $config['web_url'].'assets/';




$config['menu_source_types'] = array(
	/*
	'store_product' => array('label'=>'{lang:store_product_heading}', 'url'=>'store/product'),
	'ph_category.article'=>array('label'=>'{lang:article_heading} / {lang:category_heading}', 'url'=>'s/article/category'),
	'ph_tag.article'=>array('label'=>'{lang:article_heading} / {lang:tag_heading}', 'url'=>'s/article/tag'),
	'ph_post.article'=>array('label'=>'{lang:article_heading} / {lang:post_heading}', 'url'=>'s/article/post'),
	'ph_tag.blog'=>array('label'=>'{lang:blog_heading} / {lang:tag_heading}', 'url'=>'s/blog/tag'),
	'ph_post.blog'=>array('label'=>'{lang:blog_heading} / {lang:post_heading}', 'url'=>'s/blog/post'),
	/*/
	'ph_post.staticpage' => array('label'=>'{lang:staticpage_heading} / {lang:post_heading}', 'url'=>'s/staticpage/post'),
	//*/
);