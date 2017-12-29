<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Curl Class
 *
 * @package		Module Creator
 * @subpackage	ThirdParty
 * @category	Libraries
 * @author		Philip Sturgeon
 * @copyright	Copyright (c) 2008 - 2010, Phil Sturgeon
 * @created			09/12/2008
 */

class Curl
{
    
    private $response;          // Contains the cURL response for debug
   
    private $session;           // Contains the cURL handler for a session
    private $url;               // URL of the session
    private $options = array(); // Populates curl_setopt_array
    private $headers = array(); // Populates extra HTTP headers
    
    private $error_code;         // Error code returned as an int
    private $error_string;       // Error message returned as a string
    private $info;               // Returned after request (elapsed time, etc)
    
    public function get_error_code(){ return $this->error_code;}
    public function get_error_message(){ return $this->error_string;}
    public function get_response(){ return $this->response;}
    public function get_info(){return $this->info;}
    
    function __construct($url = '')
    {
        log_message('debug', 'cURL Class Initialized');
        
        if (!$this->is_enabled())
		{
            log_message('error', 'cURL Class - PHP was not built with cURL enabled. Rebuild PHP with --with-curl to use cURL.') ;
        }
        
        if ($url)
        {
        	$this->create($url);
        }
    }
 
    
    function __call($method, $arguments)
    {
    	if (in_array($method, array('simple_get', 'simple_post', 'simple_put', 'simple_delete')))
    	{
    		// Take off the "simple_" and past get/post/put/delete to _simple_call
    		$verb = str_replace('simple_', '', $method);
    		array_unshift($arguments, $verb);
    		return call_user_func_array(array($this, '_simple_call'), $arguments);
    	}
    }
    
    /* =================================================================================
     * SIMPLE METHODS 
     * Using these methods you can make a quick and easy cURL call with one line.
     * ================================================================================= */
 
    // Return a get request results
    public function _simple_call($method, $url, $params = array(), $options = array())
    {
        // If a URL is provided, create new session
        $this->create($url);

		$this->{$method}($params, $options);
        
        // Add in the specific options provided
        $this->options($options);

        return $this->execute();
    }
 
    public function simple_ftp_get($url, $file_path, $username = '', $password = '')
    {
        // If there is no ftp:// or any protocol entered, add ftp://
        if (!preg_match('!^(ftp|sftp)://! i', $url))
		{
            $url = 'ftp://'.$url;
        }
        
        // Use an FTP login
        if ($username != '')
        {
            $auth_string = $username;
            
            if ($password != '')
            {
            	$auth_string .= ':'.$password;
            }
            
            // Add the user auth string after the protocol
            $url = str_replace('://', '://'.$auth_string.'@', $url);
        }
        
        // Add the filepath
        $url .= $file_path;

        $this->options(CURLOPT_BINARYTRANSFER, TRUE);
        $this->options(CURLOPT_VERBOSE, TRUE);
        
        return $this->execute();
    }
    
    /* =================================================================================
     * ADVANCED METHODS 
     * Use these methods to build up more complex queries
     * ================================================================================= */

    public function post($params = array(), $options = array())
	{
        // If its an array (instead of a query string) then format it correctly
        if (is_array($params))
		{
            $params = http_build_query($params, NULL, '&');
        }
        
        // Add in the specific options provided
        $this->options($options);
        
        $this->http_method('post');
        
        $this->option(CURLOPT_POST, TRUE);
        $this->option(CURLOPT_POSTFIELDS, $params);
    }
    
    public function put($params = array(), $options = array())
    { 
        // If its an array (instead of a query string) then format it correctly
        if (is_array($params))
        {
            $params = http_build_query($params, NULL, '&');
        }
        
        // Add in the specific options provided
        $this->options($options);
        
        $this->http_method('put');
        $this->option(CURLOPT_POSTFIELDS, $params);
        
        // Override method, I think this overrides $_POST with PUT data but... we'll see eh?
        $this->option(CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: PUT'));
    }
    
    public function delete($params, $options = array())
    {
        // If its an array (instead of a query string) then format it correctly
        if (is_array($params))
        {
            $params = http_build_query($params, NULL, '&');
        }
        
        // Add in the specific options provided
        $this->options($options);
        
        $this->http_method('delete');
        
        $this->option(CURLOPT_POSTFIELDS, $params);
    }
    
    public function set_cookies($params = array())
    {
        if (is_array($params))
        {
            $params = http_build_query($params, NULL, '&');
        }
        
        $this->option(CURLOPT_COOKIE, $params);
        return $this;
    }
    
    public function http_header($header_string)
    {
		$this->headers[] = $header_string;
    }
    
    public function http_method($method)
    {
    	$this->options[CURLOPT_CUSTOMREQUEST] = strtoupper($method);
        return $this;
    }
    
    public function http_login($username = '', $password = '', $type = 'any')
    {
		$this->option(CURLOPT_HTTPAUTH, constant('CURLAUTH_'.strtoupper($type) ));
        if(!empty($password))
            $this->option(CURLOPT_USERPWD, $username.':'.$password);
        else
            $this->option(CURLOPT_USERPWD, $username);
        return $this;
    }
    
    public function proxy($url = '', $port = 80)
    {
        $this->option(CURLOPT_HTTPPROXYTUNNEL, TRUE);
        $this->option(CURLOPT_PROXY, $url.':'. $port);
        return $this;
    }
    
    public function proxy_login($username = '', $password = '')
    {
        $this->option(CURLOPT_PROXYUSERPWD, $username.':'.$password);
        return $this;
    }
    
    public function options($options = array())
    {
        // Merge options in with the rest - done as array_merge() does not overwrite numeric keys
        foreach($options as $option_code => $option_value)
        {
            $this->option($option_code, $option_value);
        }
        
        // Set all options provided
        @curl_setopt_array($this->session, $this->options);
        
        return $this;
    }
    
    public function option($code, $value)
    {
    	if (is_string($code) && !is_numeric($code))
    	{
    		$code = constant('CURLOPT_' . strtoupper($code));
    	}
    	
    	$this->options[$code] = $value;
        return $this;
    }
    
    // Start a session from a URL
    public function create($url)
    {
        // Reset the class
        $this->set_defaults();

        // If no a protocol in URL, assume its a CI link
        if (!preg_match('!^\w+://! i', $url))
        {
            get_instance()->load->helper('url');
            $url = site_url($url);
        }
        
        $this->url = $url;
        $this->session = curl_init($this->url);
        
        return $this;
    }
    
    // End a session and return the results
    public function execute()
    {
        // Set two default options, and merge any extra ones in
        if (!isset($this->options[CURLOPT_TIMEOUT]))           $this->options[CURLOPT_TIMEOUT] = 30;
        if (!isset($this->options[CURLOPT_RETURNTRANSFER]))    $this->options[CURLOPT_RETURNTRANSFER] = TRUE;
        if (!isset($this->options[CURLOPT_FOLLOWLOCATION]))    $this->options[CURLOPT_FOLLOWLOCATION] = TRUE;
        if (!isset($this->options[CURLOPT_FAILONERROR]))       $this->options[CURLOPT_FAILONERROR] = FALSE;

		if (!empty($this->headers))
		{
			$this->option(CURLOPT_HTTPHEADER, $this->headers); 
		}

        $this->options();

        // Execute the request & and hide all output
        $this->response = curl_exec($this->session);

        // Request failed
        if ($this->response === FALSE)
        {
            $this->error_code = curl_errno($this->session);
            $this->error_string = curl_error($this->session);
            
            curl_close($this->session);
            $this->session = NULL;
            return FALSE;
        } 
        
        // Request successful
        else
        {
            $this->info = curl_getinfo($this->session);
            
            curl_close($this->session);
            $this->session = NULL;
            return $this->response;
        }
    }
    
    public function is_enabled()
    {
		return function_exists('curl_init');
    }
    
    public function debug($return = false)
    {
    	$str = '';
        $str.= "=============================================<br/>\n";
        $str.= "<h2>CURL Test</h2>\n";
        $str.= "=============================================<br/>\n";
        $str.= "<h3>response</h3>\n";
        $str.= "<code>".nl2br(htmlentities($this->response))."</code><br/>\n\n";
    
        if ($this->error_string)
        {
    	    $str.= "=============================================<br/>\n";
    	    $str.= "<h3>Errors</h3>";
    	    $str.= "<strong>Code:</strong> ".$this->error_code."<br/>\n";
    	    $str.= "<strong>Message:</strong> ".$this->error_string."<br/>\n";
        }
    
        $str.= "=============================================<br/>\n";
        $str.= "<h3>Info</h3>";
        $str.= "<pre>";
        $str.=print_r($this->info, true);
        $str.= "</pre>";
        if($return)
            return $str;
		echo $str;
	}

	public function debug_request()
	{
		return array(
			'url' => $this->url
		);
	}
    
    private function set_defaults()
    {
        $this->response = '';
        $this->info = array();
        $this->options = array();
        $this->error_code = 0;
        $this->error_string = '';
    }
}

// END Curl Class

/* End of file Curl.php */
/* Location: ./application/libraries/Curl.php */