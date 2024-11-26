<?php

namespace App\Http\Controllers;

use App\Models\DataService;

use Illuminate\Http\Request;
use App\Http\Controllers\SafeHaven;
use GuzzleHttp\Client;
use App\Models\Members;
use App\Models\Transactions;
use App\Http\Controllers\VirtualAccountsController;


class DataServiceController extends Controller
{
   
    public function purchase(Request $request){

        $validated = $request->validate([
               
                
            'user_id' => 'required|string',
            'bundle_code' => 'required|string',
            'network'  => 'required|string',
            'amount'  => 'required|string',
            'phone'  => 'required|string',
           

        ]);

         //check if user exists
         $member = Members::find($validated["user_id"]);

         

         if(!$member){
            return response()->json([
                'success' =>false,
                'status' => 'USER_NOT_EXISTS',
                'message' => "User with that ID does not exist",
                
            ],400);
         }
         $user_safehaven_info = json_decode($member->safehaven_account_data);
         $user_account_number = $user_safehaven_info->accountNumber;

       

        $safe_haven = new SafeHaven();
        $swap_assertion = $safe_haven->exchange_safehaven_client_assertion();

        $access_token = $swap_assertion["access_token"];
        $ibs_client_id = $swap_assertion["ibs_client_id"];
       // $debitAccountNumber = "0114762128";

        //set the default service  category id to MTN's
        $service_category_id = "61efacfada92348f9dde5f9e";

        $client = new Client();

        if($validated["network"] == "MTN"){

            $service_category_id = "61efacfada92348f9dde5f9e";
        }
        if($validated["network"] == "GLO"){

            $service_category_id = "61efad06da92348f9dde5fa1";
        }
        if($validated["network"] == "AIRTEL"){
            $service_category_id = "61efad12da92348f9dde5fa4";
        }
        if($validated["network"] == "9Mobile"){
            $service_category_id = "61efad1dda92348f9dde5fa7";
        }
        if($validated["network"] == "MTN_DATA"){
            $service_category_id = "6502eb6e65463b201bf8065f";
        }



       


         // Define the request parameters
         $url = 'https://api.safehavenmfb.com/vas/pay/data';
 
         $headers = [
             'ClientID' => $ibs_client_id,
             'authorization' => 'Bearer '.$access_token,
             'accept' => 'application/json',
             'content-type' => 'application/json',
             
 
         ];

         $body = json_encode([
           
            'serviceCategoryId' => $service_category_id,
            'amount' => floatval($validated["amount"]),
            'channel' => 'WEB',
            'debitAccountNumber' => $user_account_number, //deduct the amount from the user's account on safehaven
            'phoneNumber' => $validated["phone"],
            'bundleCode' => $validated["bundle_code"],
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
               'status' => 'DATA_PURCHASE_FAILED',
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
         $transaction->service = "DATA";
         $transaction->category = $validated["network"];
         $transaction->amount = $validated["amount"];
         $transaction->tx_detail = json_encode($decodedBody["data"]);
         $transaction->status = "SUCCESS";
         $transaction->save();


        //refresh user data from db
        $member = Members::find($validated["user_id"]);




        return response()->json([
            'success' =>true,
            'status' => 'SUCCESS',
            'message' => "DATA PLAN successfully purchased",
            'data' =>  $decodedBody["data"],
            'user_data' => $member,
            'date' => $transaction->created_at
            
        ],200);
         

 

    }
}
