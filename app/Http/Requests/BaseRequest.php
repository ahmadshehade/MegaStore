<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BaseRequest extends FormRequest
{
       /**
        * Summary of failedValidation
        * @param \Illuminate\Contracts\Validation\Validator $validator
        * @throws \Illuminate\Http\Exceptions\HttpResponseException
        * @return never
        */
       public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator){
           throw new HttpResponseException(response()->json([
            'message'=>'Validated Error',
            'error'=>$validator->errors(),
            
           ],422));
       }
}
