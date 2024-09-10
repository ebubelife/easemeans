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
        $url = 'https://api.sandbox.safehavenmfb.com/oauth2/token';

        $headers = [
            'ClientID' => '8b7783aa56bed8fd48e4cdfba5409423',
            'accept' => 'application/json',
            'content-type' => 'application/json',
            

        ];

        $data = [
      
        'grant_type'=> 'client_credentials',
        'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
        'client_id' => '8b7783aa56bed8fd48e4cdfba5409423',
        'client_assertion' => 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJodHRwczovL2Vhc2VtZWFucy5jb20iLCJzdWIiOiI4Yjc3ODNhYTU2YmVkOGZkNDhlNGNkZmJhNTQwOTQyMyIsImF1ZCI6Imh0dHBzOi8vYXBpLnNhbmRib3guc2FmZWhhdmVubWZiLmNvbSIsImlhdCI6MTcyNTcxOTYxMiwiZXhwIjoxODIyMzQ4Nzk5fQ.As_HSRz1aXK6PsXwaEkcuWz23WCK9VUUMAq6xZi_NUe71uJJ7pn1KVLEd9nU8n-PV-YxGQ7_KMSioX4Lr_tc2s_L33VhxEOegr_KdGl6EtJ1jEZEdy26mm2V2eNV2R4QJD33VN8LsZwk1CTZAVIS0u32I6mxolFf0PRE89CfnB4',

       
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
