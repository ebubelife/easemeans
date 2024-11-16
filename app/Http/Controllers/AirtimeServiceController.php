<?php

namespace App\Http\Controllers;

use App\Models\AirtimeService;
use Illuminate\Http\Request;
use App\Http\Controllers\SafeHaven;
use GuzzleHttp\Client;
use App\Models\Members;
use App\Models\transactions;
use App\Http\Controllers\VirtualAccountsController;

class AirtimeServiceController extends Controller
{
    public function purchase(Request $request){

        $validated = $request->validate([
               
                
            'user_id' => 'required|string',
            'amount' => 'required|string',
            'network'  => 'required|string',
            'phone'  => 'required|string',
           

        ]);

         //check if email exists
         $member = Members::find($validated["user_id"]);

         

         if(!$member){
            return response()->json([
                'success' =>false,
                'status' => 'USER_NOT_EXISTS',
                'message' => "User with that ID does not exist",
                
            ],400);
         }

        $safe_haven = new SafeHaven();
        $swap_assertion = $safe_haven->exchange_safehaven_client_assertion();

        $access_token = $swap_assertion["access_token"];
        $ibs_client_id = $swap_assertion["ibs_client_id"];
        $debitAccountNumber = "0114762128";
        $service_category_id = "61efacbcda92348f9dde5f92";

        $client = new Client();

        if($validated["network"] == "MTN"){

            $service_category_id = "61efacbcda92348f9dde5f92";
        }
        if($validated["network"] == "GLO"){

            $service_category_id = "61efacc8da92348f9dde5f95";
        }
        if($validated["network"] == "AIRTEL"){
            $service_category_id = "61efacd3da92348f9dde5f98";
        }
        if($validated["network"] == "9Mobile"){
            $service_category_id = "61efacdeda92348f9dde5f9b";
        }



         // Define the request parameters
         $url = 'https://api.safehavenmfb.com/vas/pay/airtime';
 
         $headers = [
             'ClientID' => $ibs_client_id,
             'authorization' => 'Bearer '.$access_token,
             'accept' => 'application/json',
             'content-type' => 'application/json',
             
 
         ];

         $body = json_encode([
           
            'serviceCategoryId' => $service_category_id,
            'amount' => $validated["amount"],
            'channel' => 'WEB',
            'debitAccountNumber' => $debitAccountNumber,
            'phoneNumber' => $validated["phone"],
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

        

        if($decodedBody["statusCode"] == 400 || $decodedBody["statusCode"] == 500){
           //there was an error verifying BVN
           //return the message received and hope that Safehaven properly documented it😇

           return response()->json([
               'success' =>false,
               'status' => 'AIRTIME_PURCHASE_FAILED',
               'message' => $decodedBody["message"],
              // 'bvn_data' =>  $decodedBody
               
           ],401);

        }

         $member_safehaven_info = json_decode($member->safehaven_account_data);

         //update subaccount information from endpoint for user to reflect the changes made by purchase
         $sub_account_controller = new VirtualAccountsController();
         $safehaven_sub_account_info = $sub_account_controller->get_sub_account_single($member_safehaven_info->_id, $validated["user_id"]);

         //update transactionn history
         $transaction = new transactions();
         $transaction->user_id = $validated["user_id"];
         $transaction->service = "AIRTIME";
         $transaction->category = $validated["network"];
         $transaction->amount = $validated["amount"];
         $transaction->tx_detail = $decodedBody["data"];
         $transaction->status = "SUCCESS";
         $transaction->save();





        return response()->json([
            'success' =>true,
            'status' => 'SUCCESS',
            'message' => "AIRTIME successfully purchased",
            'data' =>  $decodedBody["data"],
            'user_data' => $member
            
        ],200);
         

 

    }
}
