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

    public function get_services(Request $request){
    
        //https://api.sandbox.safehavenmfb.com/vas/services

        $safe_haven_access_cred = $this->exchange_safehaven_client_assertion();
        $access_token  = $safe_haven_access_cred["access_token"];
        $ibs_client_id = $safe_haven_access_cred["ibs_client_id"];

        $client = new Client();
 
        // Define the request parameters
        $url = 'https://api.safehavenmfb.com/vas/services';

        $headers = [
            'ClientID' => $ibs_client_id,
            'authorization' => 'Bearer '.$access_token,
            'accept' => 'application/json',
            'content-type' => 'application/json',
            

        ];

         // POST request using the created object
         $postResponse = $client->get($url, [
            'headers' => $headers,
            
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
               'status' => 'OPERATION_FAILED',
               'message' => $decodedBody["message"],
              // 'bvn_data' =>  $decodedBody
               
           ],400);

        }

        return response()->json([
            'success' =>true,
            'status' => 'SUCCESS',
            'message' => "Services retrieved successfully",
            'response_data' =>  $decodedBody,
            
            
        ],200);



        
        
    }

    public function get_service_categories(Request $request, $service_id){
        //https://safehavenmfb.readme.io/reference/get-service-categories

        $safe_haven_access_cred = $this->exchange_safehaven_client_assertion();
        $access_token  = $safe_haven_access_cred["access_token"];
        $ibs_client_id = $safe_haven_access_cred["ibs_client_id"];

        $client = new Client();
 
        // Define the request parameters
        $url = 'https://api.safehavenmfb.com/vas/service/'.$service_id.'/service-categories';

        $headers = [
            'ClientID' => $ibs_client_id,
            'authorization' => 'Bearer '.$access_token,
            'accept' => 'application/json',
            'content-type' => 'application/json',
            

        ];

         // POST request using the created object
         $postResponse = $client->get($url, [
            'headers' => $headers,
            
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
               'status' => 'OPERATION_FAILED',
               'message' => $decodedBody["message"],
              // 'bvn_data' =>  $decodedBody
               
           ],400);

        }

        return response()->json([
            'success' =>true,
            'status' => 'SUCCESS',
            'message' => "Services retrieved successfully",
            'response_data' =>  $decodedBody,
            
            
        ],200);

    }

    public function get_products(Request $request, $service_id){
        //https://safehavenmfb.readme.io/reference/get-category-products

        $safe_haven_access_cred = $this->exchange_safehaven_client_assertion();
        $access_token  = $safe_haven_access_cred["access_token"];
        $ibs_client_id = $safe_haven_access_cred["ibs_client_id"];

        $client = new Client();
 
        // Define the request parameters
        $url = 'https://api.safehavenmfb.com/vas/service-category/'.$service_id.'/products';

        $headers = [
            'ClientID' => $ibs_client_id,
            'authorization' => 'Bearer '.$access_token,
            'accept' => 'application/json',
            'content-type' => 'application/json',
            

        ];

         // POST request using the created object
         $postResponse = $client->get($url, [
            'headers' => $headers,
            
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
               'status' => 'OPERATION_FAILED',
               'message' => $decodedBody["message"],
              // 'bvn_data' =>  $decodedBody
               
           ],400);

        }

        return response()->json([
            'success' =>true,
            'status' => 'SUCCESS',
            'message' => "Services retrieved successfully",
            'response_data' =>  $decodedBody,
            
            
        ],200);
        
    }

}
