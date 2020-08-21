<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function formatResponse($message, $http_code = 200, $data = null)
    {
        $response = [
            'message' => $message,
        ];

        $data ? $response['attributes'] = $data : null;
        
        return response()->json($response, $http_code);
    }
}
