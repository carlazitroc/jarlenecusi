<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| AUTO-LOADER
| -------------------------------------------------------------------
| This file specifies which systems should be loaded by default.
|
| In order to keep the framework as light-weight as possible only the
| absolute minimal resources are loaded by default. For example,
| the database is not connected to automatically since no assumption
| is made regarding whether you intend to use it.  This file lets
| you globally define which systems you would like loaded with every
| request.
|
| -------------------------------------------------------------------
| Instructions
| -------------------------------------------------------------------
|
| Items for loading automatically when application start:
|
| 1. Packages
| 2. Libraries
| 3. Helper files
| 4. Custom config files
| 5. Language files
| 6. Models
|
*/
$autoload['packages'] = array();
$autoload['libraries'] = array('user_agent');
$autoload['helper'] = array('url','file','datetime','form','language');
$autoload['config'] = array('app','admin');
$autoload['language'] = array('common', 'proj');
$autoload['model'] = array();


if (is_dir(VENDOR_DIR.'dynamotor-ci/ci-package')){
	$autoload['packages'][] = VENDOR_DIR.'dynamotor-ci/ci-package';
}
if (defined('PROJECT_PKG')) {
	$autoload['packages'][] = PACKAGE_DIR . PROJECT_PKG;
	if(!in_array('portal', $autoload['config']))
		$autoload['config'][] = 'portal';
}

/* End of file autoload.php */
/* Location: ./application/config/autoload.php */