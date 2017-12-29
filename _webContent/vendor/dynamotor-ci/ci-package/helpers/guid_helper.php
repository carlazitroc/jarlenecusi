<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


if(!function_exists('guid')){

     function guid(){
          return \Dynamotor\Helpers\Guid::newGuid();
     }
}
