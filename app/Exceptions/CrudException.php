<?php

namespace App\Exceptions;

use Exception;

class CrudException extends Exception
{

   /**
    * Summary of __construct
    * @param mixed $message
    * @param mixed $code
    */
   public function __construct($message = "CRUD Operation Failed", $code = 400){
     parent::__construct($message, $code);
   }

}
