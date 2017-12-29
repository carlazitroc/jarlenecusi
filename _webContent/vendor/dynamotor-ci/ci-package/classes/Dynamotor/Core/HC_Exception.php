<?php 

namespace Dynamotor\Core;

use \CI_Exceptions;
use \Exception;

class HC_Exception extends Exception
{
	public $code;
	public $data;

	public function __construct($code, $message='', $data = NULL){
		parent::__construct($message);

		$this->code = $code; 
		$this->message = $message;
	}
}
