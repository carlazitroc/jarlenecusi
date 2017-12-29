<?php
/** 
 * Request module for CodeIgniter
 * @author      leman 
 * @copyright   Copyright (c) 2015, LMSWork. 
 * @link        http://lmswork.com 
 * @since       Version 1.0 
 *  
 */

namespace Dynamotor\Modules{

    class RequestModule extends \Dynamotor\Core\HC_Module
    {

        function __construct(){
            parent::__construct();
        }
    	
    	var $supported_extensions = array(
    		'html'=> array('', 'htm', 'html'),
    		'view'=> array('', 'htm', 'html', 'js'),
    		'asset'=> array('css', 'js'),
    		'data'=> array('json', 'plist', 'xml'),
    	);

        public function is_support_format($group='html'){
            if(!isset($this->supported_extensions[$group])) return FALSE;
            $group_exts = $this->supported_extensions[$group];

            return $this->uri->is_extension($group_exts);
        }
    }
}