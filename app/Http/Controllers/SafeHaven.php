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
            'ClientID' => '8b7783aa56bed8fd48e4cdfba5409423',
            'accept' => 'application/json',
            'content-type' => 'application/json',
            

        ];

        $data = [
      
        'grant_type'=> 'client_credentials',
        'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
        'client_id' => '687f506bb556c185217357deeb27b156',
        'client_assertion' => 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJodHRwczovL2Vhc2VtZWFucy5jb20iLCJzdWIiOiI2ODdmNTA2YmI1NTZjMTg1MjE3MzU3ZGVlYjI3YjE1NiIsImF1ZCI6Imh0dHBzOi8vYXBpLnNhZmVoYXZlbm1mYi5jb20iLCJpYXQiOjE3MjYyNzE1MjAsImV4cCI6MTgyMjM0ODc5OX0.ZNoqmU21Jbh-XDB_cSiYoi-9sVxhzkDmAjMqF02giV5G4E01umZrZbDx6bfaD3r5YDhP8IMB24F36MtTjz75KG4Q_lA_tdVjsmsiL9LsFRuqsFjzF_VTPdVt-ZHtSuBbdnfsMV81OAE-Tfxxph4J41LT7HpIy8zBsiwQytyyrMQ',

       
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
