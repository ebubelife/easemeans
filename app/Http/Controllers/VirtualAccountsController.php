<?php

namespace App\Http\Controllers;

use App\Models\VirtualAccounts;
use Illuminate\Http\Request;
use App\Models\Members;
use App\Http\Controllers\SafeHaven;
use GuzzleHttp\Client;



class VirtualAccountsController extends Controller
{


    public function create_sub_account(Request $request)
    {

        /* this method will verify  BVN and also create asub account from the Safe Haven API
        https://safehavenmfb.readme.io/reference/create-sub-account-new

        */

        $validated = $request->validate([
               
                
            'user_id' => 'required|string',
            'code' => 'required|string',
            'request_id' => 'required|string',
           

        ]);

         //check if user exists
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
         $url = 'https://api.safehavenmfb.com/accounts/v2/subaccount';
 
         $headers = [
             'ClientID' => $ibs_client_id,
             'authorization' => 'Bearer '.$access_token,
             'accept' => 'application/json',
             'content-type' => 'application/json',
             
 
         ];
 
         $type = "BVN"; $async = false;

         $external_reference = $this->generateRandomString();
       
         
         $body = json_encode([
             'identityType' => $type,
             'identityId' => $validated["request_id"],
             'otp' => $validated["code"],
             'identityNumber' => $member->bvn,
             'phoneNumber' => '+234 ' . substr($member->phone, 1),
             'emailAddress' => $member->email,
             'externalReference' => $external_reference,
            // 'async' => $async,
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
                'status' => 'OPERATION_FAILED',
                'message' => $decodedBody["message"],
               // 'bvn_data' =>  $decodedBody
                
            ],400);

         }
 
        //Else it means it worked and bvn is verified

        //save bvn verification state
        $member->bvn_verified = true;
        $member->account_number = $decodedBody["data"]["accountNumber"];
        $member->safehaven_account_data = $decodedBody["data"];
       
        $member->save();

        return response()->json([
            'success' =>true,
            'status' => 'SUCCESS',
            'message' => "User sub account successfully created",
            'response_data' =>  $decodedBody,
            'user_data' => $member
            
        ],200);
         
         
        


    }

    public function get_sub_accounts(Request $request)
    {

        /* This method will get all the sub accounts 
        https://safehavenmfb.readme.io/reference/create-sub-account-new

        */

     

        
         $safe_haven = new SafeHaven();
         $swap_assertion = $safe_haven->exchange_safehaven_client_assertion();
 
         $access_token = $swap_assertion["access_token"];
         $ibs_client_id = $swap_assertion["ibs_client_id"];
 
         $client = new Client();
 
         // Define the request parameters
         $url = 'https://api.safehavenmfb.com/accounts?page=0&limit=100&isSubAccount=true';
 
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
            'message' => "Accounts created successfully",
            'accounts' =>  $decodedBody,
           
            
        ],200);
         
         
        


    }

    function generateRandomString($length = 10) {
        //the string generated will be 10 characters long and used as external reference parameter in create sub account endpoint
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    

    public function get_sub_account_single($_id, $user_id)
    {

        /* This method will get one single account by id
        https://safehavenmfb.readme.io/reference/create-sub-account-new

        */

         //check if user exists
         $member = Members::find($user_id);

       

         if(!$member){
            return response()->json([
                'success' =>false,
                'status' => 'USER_NOT_EXISTS',
                'message' => "User with that ID does not exist" .$user_id,
                
            ],400);
         }

     

        
         $safe_haven = new SafeHaven();
         $swap_assertion = $safe_haven->exchange_safehaven_client_assertion();
 
         $access_token = $swap_assertion["access_token"];
         $ibs_client_id = $swap_assertion["ibs_client_id"];
 
         $client = new Client();
 
         // Define the request parameters
         $url = 'https://api.safehavenmfb.com/accounts/'.$_id;;
 
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

         //update the member's safehaven account data in the database

         $member->safehaven_account_data = $decodedBody["data"];
         $member->deposit_balance = $decodedBody["data"]["accountBalance"];
         $member->save();


       

        return response()->json([
            'success' =>true,
            'status' => 'SUCCESS',
            'message' => "BVN successfully verified",
            'account_data' =>  $decodedBody,
           
            
        ],200);
         
         
        


    }

  
   
    

}
