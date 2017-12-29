<?php
namespace Dynamotor\Core;
use \CI_Input;

class HC_Input extends CI_Input
{
	
	/**
	* Fetch an item from the QUERY_STRING array
	*
	* @access	public
	* @param	string
	* @param	bool
	* @return	string
	*/
	public function get($index = '', $xss_clean = FALSE)
	{
		if(isset($_SERVER['QUERY_STRING']))
			parse_str($_SERVER['QUERY_STRING'],$ary);
		return $this->_fetch_from_array($ary, $index, $xss_clean);
	}
	
	/**
	 * [request_method description]
	 * @return string [description]
	 */
	public function request_method()
	{
		return $this->_detect_request_method();
	}
	
	protected function _detect_request_method() {
		$method = strtolower($this->server('REQUEST_METHOD'));
		
		if (empty($method)) {
			if ($this->input->post('_method')) {
				$method = strtolower($this->input->post('_method'));
			} else if ($this->input->server('HTTP_X_HTTP_METHOD_OVERRIDE')) {
				$method = strtolower($this->input->server('HTTP_X_HTTP_METHOD_OVERRIDE'));
			}      
		}
		
		if (in_array($method, array('get', 'delete', 'post', 'put'))) {
			return $method;
		}
		
		return 'get';
	}
}
