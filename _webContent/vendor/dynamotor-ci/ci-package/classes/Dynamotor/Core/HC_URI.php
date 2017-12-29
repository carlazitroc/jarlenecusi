<?php
namespace Dynamotor\Core{
use CI_URI;

class HC_URI extends CI_URI {

    function __construct() {
        parent::__construct();
    }
    
    var $_extension = '';   // The request extension name.  
    var $_raw_extension = '';   // The request extension name.  
  
    /** 
     * Explode the URI Segments. The individual segments will 
     * be stored in the $this->segments array.   
     * 
     * @access  private 
     * @return  void 
     */       
    protected function _set_uri_string($str) 
    {
        parent::_set_uri_string($str);

        $last_segment_index = $this->total_segments();
        $refLastSegment =& $this->segments[ $last_segment_index ];  
        
        if ( preg_match('/(.+)\.(\w+)$/', $refLastSegment, $matches) ) {  
            $refLastSegment = $matches[1];  
             $this->_raw_extension = $this->_extension = strtolower($matches[2]);  
        } 
    }  
  
    // --------------------------------------------------------------------  
      
    /**  
     * Fetch the file extension.  
     *   
     * If user request 'http://localhost/ci_test/index.php/control/index.xml',  
     * this will return 'xml'.                 
     *  
     * @access  public  
     * @return  string  
     */  
    public function extension($val=FALSE)  
    {  
        if($val !== FALSE)
            $this->_extension = $val;
        return $this->_extension;  
    } 

    public function raw_extension()  
    {
        return $this->_raw_extension;  
    } 
    
    public function is_extension($val=FALSE)  
    {  
        if(is_array($val))
            return in_array($this->_extension,$val);
        return $this->_extension == $val;  
    }
}

}