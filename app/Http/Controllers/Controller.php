<?php

namespace App\Http\Controllers;

abstract class Controller
{
    
    /**
     * Summary of SuccessMessage
     * @param array $data
     * @param string $message
     * @param mixed $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function SuccessMessage(array $data, string $message , $code){
        return response()->json(["message"=> $message,"data"=>$data],$code);
    }

    /**
     * Summary of ErrorMessage
     * @param array $error
     * @param string $message
     * @param mixed $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function ErrorMessage(array $error, string $message , $code){
        return response()->json(["message"=> $message,"error"=>$error,"code"=> $code]);
    }
}
