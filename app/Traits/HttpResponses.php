<?php
namespace App\Traits;

trait HttpResponses
{
    protected function success ($data, $message=null, $code = 200){
        return response()->json([
            'status'=> $code,
            'message'=> $message,
            'data' => $data
        ], $code);
    }

    protected function error ($data, $message=null, $code = 401){
        return response()->json([
            'status'=>$code,
            'message'=> $message,
            'data' => $data
        ], $code);
    }
}