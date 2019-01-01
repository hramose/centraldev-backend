<?php

use Carbon\Carbon;
use Illuminate\Http\Request;

if (!function_exists('json_response'))
{
    /**
     * @param string     $message
     * @param string     $doc_url
     * @param array      $errors
     * @param int        $http_code
     * 
     * @return  \Illuminate\Http\JsonResponse
     */
    function json_response($message, $doc_url, $errors, $data, $http_code)
    {
        $response = [
            'endpoint' => request()->path() === '/' ? '/' : '/'.request()->path(),
        ];
        isset($message) ? $response['message'] = snake_case($message) : null;
        isset($doc_url) ? $response['documentation_url'] = $doc_url : null;
        isset($errors) ? $response['errors'] = $errors : null;
        $response['timestamp'] = Carbon::now()->timestamp;
        isset($data) ? $response['data'] = $data : null;
        isset($http_code) ? $response['http_code'] = $http_code : null;

        return response()->json($response, $http_code);
    }
}