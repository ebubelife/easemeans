<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class SafeHaven extends Controller
{
    
    public function exchange_safehaven_client_assertion(){

        /*

        This method exchanges safehaven client assertion for a token. Read more
        https://safehavenmfb.readme.io/reference/exchange-client-credentials

        */

        $client = new Client();

        // Define the request parameters
        $url = 'https://api.safehavenmfb.com/oauth2/token';

        $headers = [
        
            'accept' => 'application/json',
            'content-type' => 'application/json',
            

        ];

        $data = [
      
        'grant_type'=> 'client_credentials',
        'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
        'client_id' => '387cbcfe86b75f0abc54b579cb49feff',
        'client_assertion' => 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJodHRwczovL2Vhc2VtZWFucy5jb20iLCJzdWIiOiIzODdjYmNmZTg2Yjc1ZjBhYmM1NGI1NzljYjQ5ZmVmZiIsImF1ZCI6Imh0dHBzOi8vYXBpLnNhZmVoYXZlbm1mYi5jb20iLCJpYXQiOjE3MzA3MTgxMTQsImV4cCI6MTgyMjM0ODc5OX0.DQ7cfBdzKluYB8Gufb9UGqHJA1Lh97emCemak6KGqt7Qf7VOgb2sIs-M6uEugrNp7Fbgwqiz-_u5DGPy57w_Bm6-NiIl_82_oigU5h2r0_0htr2fwnemsCz7ySjsmp1REdIqxTjkG4fvsRM6WLSra17SpOpKk23h_7kRy2xAQPU',

       
        ];
 
        // POST request using the created object
        $postResponse = $client->post($url, [
            'headers' => $headers,
            'json' => $data,
        ]);

        // Get the response code
        $responseCode = $postResponse->getStatusCode();

        // Get the response body
        $responseBody = $postResponse->getBody()->getContents();

        $decodedBody = json_decode($responseBody, true);

        return $decodedBody;

        
    }

}
