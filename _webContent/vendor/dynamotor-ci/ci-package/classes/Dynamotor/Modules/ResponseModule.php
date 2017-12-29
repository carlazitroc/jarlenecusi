<?php
/** 
 * Response module for CodeIgniter
 * @author      leman 
 * @copyright   Copyright (c) 2015, LMSWork. 
 * @link        http://lmswork.com 
 * @since       Version 1.0 
 *  
 */

namespace Dynamotor\Modules;

class ResponseModule extends \Dynamotor\Core\HC_Module
{

    function __construct(){
        parent::__construct();
    }

    public function get_theme($theme = NULL){
        if(empty($theme)){
            $theme = $this->config->item( 'theme');
        }
            
        if(empty($theme) && isset($this->lang)){
            $theme = $this->pref_model->locale_item($this->lang->locale(), 'site_theme');
        }

        return $theme;
    }


    public function view($view, $vals = false, $layout = NULL, $theme = NULL){

        if (!is_array($vals)) {
            $vals = array();
        }

        if (!isset($vals['is_dialog'])) {
            $vals['is_dialog'] = false;
        }

        $theme = $this->get_theme($theme);

        if (empty($layout)) {
            $layout = 'default';
        }

        // load theme based init script.
        if (!empty($theme)) {
            $init_path = 'themes/' . $theme . '/init'.EXT;
            if (file_exists($init_path)) {
                require_once $init_path;
            }
        }

        if (isset($this->request) && !$this->request->is_support_format('html')) {
            $req_ext   = $this->uri->extension();
            $view_path = NULL;

            if (empty($view_path)) {
                $view_path = 'themes/' . $theme . '/' . $view . '.' . $req_ext . EXT;
                if (!file_exists(VIEWPATH . $view_path)) {
                    $view_path = NULL;
                }
            }
            if (empty($view_path)) {
                $view_path = $view . '.' . $req_ext . EXT;
                if (!file_exists(VIEWPATH. $view_path)) {
                    $view_path = NULL;
                }
            }
            if (empty($view_path)) {
                $view_path = 'themes/' . $theme . '/' . $view . EXT;
                if (!file_exists(VIEWPATH . $view_path)) {
                    $view_path = NULL;
                }
            }
            if (empty($view_path)) {
                $view_path = $view . EXT;
                if (!file_exists(VIEWPATH . $view_path)) {
                    $view_path = NULL;
                }
            }
            if (!empty($view_path)) {
                if ($this->uri->is_extension('js')) {
                    $this->output->set_content_type('text/javascript');
                } elseif ($this->uri->is_extension('xml', 'plist')) {
                    $this->output->set_content_type('text/xml');
                } else {

                    $this->output->set_content_type('text/plain');
                }

                return $this->load->view($view_path, $vals);
            }
            return $this->error404('unmatched_resource');
        }

        $this->load->helper('form');

        $view_path = '';
        $view_theme_path = '';
        if (empty($view_path) && !empty($view)) {
            $theme_path = 'themes/' . $theme . '/';
            $view_theme_path = $theme_path;
            $view_path = 'themes/' . $theme . '/' . $view . EXT;
            if (!file_exists(VIEWPATH. $view_path)) {
                $view_path = NULL;
                $theme_path = '';
                $view_theme_path = '';
            }
        }
        if (empty($view_path) && !empty($view)) {
            $view_path = $view . EXT;
            if (!file_exists(VIEWPATH. $view_path)) {
                $view_path = NULL;
            }
        }

        $layout_path = '';

        if (empty($layout_path)) {
            $theme_path = 'themes/' . $theme . '/';
            $layout_path = 'themes/' . $theme . '/layouts/' . $layout . EXT;
            if (!file_exists(VIEWPATH. $layout_path)) {
                $layout_path = NULL;
                $theme_path = '';
            }
        }
        if (empty($layout_path)) {
            $theme_path = 'themes/' . $theme . '/';
            $layout_path = 'themes/' . $theme . '/' . $layout . EXT;
            if (!file_exists(VIEWPATH . $layout_path)) {
                $layout_path = NULL;
                $theme_path = '';
            }
        }
        if (empty($layout_path)) {
            $theme_path = 'themes/' . $theme . '/';
            $layout_path = 'themes/' . $theme . '/default' . EXT;
            if (!file_exists(VIEWPATH. $layout_path)) {
                $layout_path = NULL;
                $theme_path = '';
            }
        }
        if (empty($layout_path)) {
            $layout_path = 'layouts/' . $layout . EXT;
            if (!file_exists(VIEWPATH . $layout_path)) {
                $layout_path = NULL;
            }
        }
        if (empty($layout_path)) {
            $layout_path = $layout;
        }

        $vals['view']      = $view;
        $vals['view_path'] = $view_path;
        $vals['theme_path'] = $theme_path;
        $vals['view_theme_path'] = $view_theme_path;
        $vals['theme']     = $theme;
        $vals['layout']    = $layout;

        $this->load->view($layout_path, $vals);
    }


    public function error($code, $message = 'unknown', $status=500, $data = NULL){

        if ($this->request->is_support_format('data') ){
            if($data !== NULL)
                $vals = $data;
            else $vals = array();
            $vals ['error']['code'] = $code;
            $vals ['error']['message'] = $message;
            //parent::error($code, $message, $status , $data);
            
            return $this->output ($vals);
        }
        return show_error( $message, 500, 'Error - '.$code);

    }

    public function output($vals, $default_format = 'json'){

        $uri ;
        if(isset($this->uri)){
            $uri = $this->uri;
        }elseif(isset($GLOBALS['URI'])){
            $uri = $GLOBALS['URI'];
        }

        $input ;
        if(isset($this->input)){
            $input = $this->input;
        }elseif(isset($GLOBALS['IN'])){
            $input = $GLOBALS['IN'];
        }

        if($uri->is_extension('')){
           $uri->extension($default_format);
        }

        if(isset($this->load))
            $this->load->helper('data');

        if($this->is_debug() && isset($this->db)){
            $vals['queries'] = $this->db->queries;
        }
        
        if(empty($opts) || !is_array($opts))
            $opts = array();

        if($input->get_post('jscallback') !==NULL){

            $callback = $input->get_post('jscallback');
            if($callback!=null && strlen($callback)>0)
                $opts['callback'] = $callback;
            
            $opts['script'] = TRUE;
                
            return $this->json_output($vals,false,$opts);
        }
        
        if($uri->is_extension('json') ){
            
            $callback = $input->get_post('callback');
            if($callback!=null && strlen($callback)>0)
                $opts['callback'] = $callback;
            
            $script = $input->get_post('script');
            if(!empty($script)){
                $opts['script'] = TRUE;
                $opts['callback'] = $script;
            }
                
            return $this->json_output($vals,false,$opts);

        }elseif($uri->is_extension('xml') ){
            return $this->xml_output($vals,false,$opts);

        }elseif($uri->is_extension('plist') ){
            return $this->plist_output($vals,false,$opts);

        }
        return $this->error404('extension_not_matched');
    }

    public function error404($message='unknown'){
        $this->output->set_header('HTTP/1.1 404 Page not found');

        // reset render view's extension
        $this->uri->extension('');

        if ($this->is_debug()) {
            header("Content-type: text/plain");
            print "Message: ".$message."\r\n";
            print "Segments: " . print_r($this->uri->segments, true) . "\r\n";
            print 'Config: '.print_r($this->config, true)."\r\n";
            if($message == 'locale_not_supported'):
                print 'Lang: '.print_r($this->lang, true)."\r\n";
            endif;
            print "Backtrace: " . "\r\n" ; debug_print_backtrace() ; print "\r\n";
            return;
        }

        log_message('error','404 ERROR at '.uri_string().'. Message returned: '.$message.'');

        $this->response->view('404', array('message'=>$message), 'blank');

        $this->output->_display();

        exit();
    }

    protected function json_output($a,$returned = false,$options=false){
        $fileName = isset($options['fileName']) ? $options['fileName'] : 'data'.date("YmdHis").md5(microtime(true));
        
        if(isset($options['callback']) && !empty($options['callback'])){
            $str = $options['callback']."(".json_encode($a).")";

            $type = 'js';
            if(isset($options['script'])) {
                $type = 'html';
                $str = '<script>'.$str.'</script>';
            }
            if($returned) return $str;
            if($type == 'js'){
                @header("Content-type:text/javascript");
                //@header('Content-Disposition: inline; filename="'.$fileName.'.js"');
            }
            print $str;
            exit;
        }
        if($returned) return json_encode($a);
        @header("Content-type:text/plain");
        //@header('Content-Disposition: inline; filename="'.$fileName.'.json"');
        print json_encode($a);
        exit;
    }

    protected function xml_output($a,$returned = false,$options=false){
        $fileName = isset($options['fileName']) ? $options['fileName'] : 'data'.date("YmdHis").md5(microtime(true));
        $charset = isset($options['charset']) ? $options['charset'] : 'utf-8';
        $rootName = isset($options['rootName']) ? $options['rootName'] : 'result';
        
        if($returned)
            return array_to_xml($a,$charset,$rootName);
        @header("Content-type:text/xml; charset=".$charset);
        //@header('Content-Disposition: inline; filename="'.$fileName.'.xml"');
        print array_to_xml($a,$charset,$rootName);
        exit;
    }
    protected function plist_output($a,$returned = false,$options=false){
        $fileName = isset($options['fileName']) ? $options['fileName'] : 'data'.date("YmdHis").md5(microtime(true));
        $charset = isset($options['charset']) ? $options['charset'] : 'utf-8';
        $type = isset($options['type']) ? $options['type'] : 'xml';
        
        $rst = NULL;
        if($type == 'text'){
            if(!$returned)
                @header("Content-type:text/plain; charset=".$charset);
                //@header("Content-type:application/x-apple-plist; charset=".$charset);
            $rst = plist_encode_text($a);
        }else{
            if(!$returned)
                @header("Content-type:text/xml; charset=".$charset);
            $rst = array_to_xml_plist($a);
        }
        if($returned)
            return $rst;
        //@header('Content-Disposition: inline; filename="'.$fileName.'.plist"');
        print $rst;
        exit;
    }
}
