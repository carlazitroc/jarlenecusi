<?php
namespace Dynamotor\Core;
class HC_Module
{

    public function __construct(){

    }

    function __get($key)
    {
        if($key == 'config'){
            global $CFG;
            if(!empty($CFG))
            return $CFG;
        }
        if($key == 'load'){
            global $LDR;
            if(!empty($LDR))
                return $LDR;
        }
        if($key == 'uri'){
            if(!empty($GLOBALS['URI']))
                return $GLOBALS['URI'];
        }
        if($key == 'input'){
            if(!empty($GLOBALS['IN']))
                return $GLOBALS['IN'];
        }
        if($key == 'output'){
            if(!empty($GLOBALS['OUT']))
                return $GLOBALS['OUT'];
        }
        if($key == 'router'){
            if(!empty($GLOBALS['RTR']))
                return $GLOBALS['RTR'];
        }

        $CI =& get_instance();

        if(isset($CI->$key))
            return $CI->$key;
    }
    
    public function is_debug(){
        if($this->input->get('debug') == 'no'){
            return FALSE;
        }
        if ( ENVIRONMENT == 'development' && (isset($this->config) && $this->config->item('debug_mode') == 'yes') ) {
            return TRUE;
        }
        if( defined('PROJECT_DEBUG_KEY') && $this->input->get('debug') == PROJECT_DEBUG_KEY)
            return TRUE;
        
        return FALSE;
    }

    public function system_error($code, $message = 'Unknown system error.', $status=200, $data = NULL){
        $case_id = uniqid('SER-');

        $vals = array(
            'message'=>$message,
            'data'=>$data,
            'uri_string'=>uri_string(),
            'uri_query'=>uri_query(),
            'post'=>$_POST,
            'get'=>$_GET,
        );

        // Log data into system folder.
        $details = 'SystemError['.$case_id.'] details: '.print_r($vals, true);
        log_message('error',  $details, true);

        $message = '[Case ID: '.$case_id.']<br />'.$message;
        $data['case_id'] = $case_id;

        return $this->error($code, $message, $status, $data);
    }

    public function error($code, $message = '', $status = 200, $data=NULL) {
        show_error($message, $status, 'Error Code #'.$code);
    }

    public function output($vals){
        return $vals;
    }
}
