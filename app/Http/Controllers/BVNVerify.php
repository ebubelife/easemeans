<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Members;
use App\Http\Controllers\SafeHaven;
use GuzzleHttp\Client;
use Exception;
use Illuminate\Support\Facades\Log;

class BVNVerify extends Controller
{
    //

    public function send_bvn_otp(Request $request){

        $validated = $request->validate([
               
                
            'id' => 'required|string',
            'bvn' => 'required|string',
           

        ]);

         //check if email exists
         $member = Members::find($validated["id"]);

         $length = strlen($validated["bvn"]);

         if(!$member){
            return response()->json([
                'success' =>false,
                'status' => 'USER_NOT_EXISTS',
                'message' => "User with that ID does not exist",
                
            ],400);
         }

         if($length != 11){

            return response()->json([
                'success' =>false,
                'status' => 'BVN_FORMAT_WRONG',
                'message' => "BVN must be an 11-digit number",
                'user_data' => $member
            ],401);

         }

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
        $async = false;
        $debitAccountNumber = "0114762128";
        $number = $validated["bvn"]; //"22351451986"
        
        $body = json_encode([
            'type' => $type,
            'async' => $async,
            'debitAccountNumber' => $debitAccountNumber,
            'number' => $number,
            'otp' => strval(rand(100000, 999999))
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

       




    }

    public function verify_bvn_otp(Request $request){

        $validated = $request->validate([
               
                
            'user_id' => 'required|string',
            'code' => 'required|string',
            'request_id' => 'required|string',
           

        ]);

         //check if email exists
         $member = Members::find($validated["user_id"]);

       

         if(!$member){
            return response()->json([
                'success' =>false,
                'status' => 'USER_NOT_EXISTS',
                'message' => "User with that ID does not exist" .$validated["user_id"],
                
            ],400);
         }


        
         $safe_haven = new SafeHaven();
         $swap_assertion = $safe_haven->exchange_safehaven_client_assertion();
 
         $access_token = $swap_assertion["access_token"];
         $ibs_client_id = $swap_assertion["ibs_client_id"];
 
         $client = new Client();
 
         // Define the request parameters
         $url = 'https://api.safehavenmfb.com/identity/v2/validate';
 
         $headers = [
             'ClientID' => $ibs_client_id,
             'authorization' => 'Bearer '.$access_token,
             'accept' => 'application/json',
             'content-type' => 'application/json',
             
 
         ];
 
         $type = "BVN"; 
       
         
         $body = json_encode([
             'type' => $type,
             'identityId' => $validated["request_id"],
             'otp' => $validated["code"]
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

         

         if($decodedBody["statusCode"] == 400){
            //there was an error verifying BVN
            //return the message received and hope that Safehaven properly documented it😇

            return response()->json([
                'success' =>false,
                'status' => 'BVN_VERIFICATION_FAILED',
                'message' => $decodedBody["message"],
               // 'bvn_data' =>  $decodedBody
                
            ],400);

         }
 
        //Else it means it worked and bvn is verified

        //save bvn verification state
        $member->bvn_verified = true;
        $member->save();
        return response()->json([
            'success' =>true,
            'status' => 'SUCCESS',
            'message' => "BVN successfully verified",
            'bvn_data' =>  $decodedBody,
            'user_data' => $member
            
        ],200);
         
         
        


    }
}
