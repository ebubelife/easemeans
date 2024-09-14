<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Members;
use App\Http\Controllers\SafeHaven;
use GuzzleHttp\Client;

class BVNVerify extends Controller
{
    //

    public function send_bvn_otp(Request $request){

        $safe_haven = new SafeHaven();
        $swap_assertion = $safe_haven->exchange_safehaven_client_assertion();

        $access_token = $swap_assertion["access_token"];
        $ibs_client_id = $swap_assertion["ibs_client_id"];

        $client = new Client();

        // Define the request parameters
        $url = 'https://api.safehavenmfb.com/identity/v2';

        $headers = [
            'ClientID' => $ibs_client_id,
            'authorization' => 'Bearer '.$access_token,
            'accept' => 'application/json',
            'content-type' => 'application/json',
            

        ];

        $type = "BVN";
        $async = true;
        $debitAccountNumber = "0114762128";
        $number = "22351451986";
        
        $body = json_encode([
            'type' => $type,
            'async' => $async,
            'debitAccountNumber' => $debitAccountNumber,
            'number' => $number,
            'otp' => '123456'
        ]);
 
        // POST request using the created object
        $postResponse = $client->post($url, [
            'headers' => $headers,
            'body' => $body,
        ]);

        // Get the response code
        $responseCode = $postResponse->getStatusCode();

        // Get the response body
        $responseBody = $postResponse->getBody()->getContents();

        $decodedBody = json_decode($responseBody, true);

       
        return $decodedBody;

        $validated = $request->validate([
               
                
            'email' => 'required|string|email',
            'bvn' => 'required|string',
           

        ]);

         //check if email exists
         $member = Members::where("email",$validated["email"])->first() ;




    }
}
