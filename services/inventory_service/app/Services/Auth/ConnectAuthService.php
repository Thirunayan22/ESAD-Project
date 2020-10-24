<?php

namespace App\Services\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Exception;

class ConnectAuthService
{

    public function getUserDetails(Request $request)
    {
        try {
            $authApi = 'https://odd-seahorse-4.loca.lt/api/user';
            $response = Http::withHeaders($request->headers->all())->get($authApi);

            if (!$response->successful()) {
                throw new Exception("ERROR_RETURNED_FROM_FROM_AUTH_SERVICE", getStatusCodes('EXCEPTION'));
            }
            $jsonData = $response->json();
//            $jsonData = ["id" => 5, "role_id" => 4];

            return $jsonData;
        } catch (Exception $exception) {
            dd($exception);
            return $exception->getMessage();
        }
    }

}
