<?php

namespace App\Http\Controllers;

use App\Models\Members;
use App\Models\Transactions;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

class TransactionsController extends Controller
{
   
    public function get_all_tx(Request $request){

        $txn = Transactions::all();

        return response()->json([
            'success' =>true,
            'status' => 'SUCCESS',
            'message' => "Transactions retrieved successfully",
            'transactions' =>  $txn,
            
            
        ],200);



    }

    public function get_all_user_tx(Request $request, $user_id){

         //check if user exists
         $member = \App\Models\Members::find($user_id);

         

         if(!$member){
            return response()->json([
                'success' =>false,
                'status' => 'USER_NOT_EXISTS',
                'message' => "User with that ID does not exist",
                
            ],400);
         }

         $txn = Transactions::where('user_id', $user_id)
         ->orderBy('created_at', 'desc') // Order by newest first
         ->get();
     

        return response()->json([
            'success' =>true,
            'status' => 'SUCCESS',
            'message' => "Transactions retrieved successfully",
            'transactions' =>  $txn,
            
            
        ],200);



    }

    public function get_user_single_tx(Request $request, $user_id, $transaction_id){

         //check if user exists
         $member = \App\Models\Members::find($user_id);

         

         if(!$member){
            return response()->json([
                'success' =>false,
                'status' => 'USER_NOT_EXISTS',
                'message' => "User with that ID does not exist",
                
            ],400);
         }


        $txn = Transactions::where('user_id', $user_id)->where('id', $transaction_id)  ->orderBy('created_at', 'desc') // Order by newest first
        ->get();
        return response()->json([
            'success' =>true,
            'status' => 'SUCCESS',
            'message' => "Transactions retrieved successfully",
            'transactions' =>  $txn,
            
            
        ],200);

    }



    public function process_webhook(Request $request)
    {


       
        // Step 1: Retrieve the payload
        $payload = $request->all(); // Retrieves all data from the request body
    
        // Step 2: Extract specific fields (optional)
        $type = $payload['type'] ?? null;
        $data = $payload['data'] ?? [];

       
        /*get user by using the account number in payload to query the database

    Query the column holding the safehaving data, extract the JSON and get the account number and compare it to the account number in the payload*/

    $user_with_acc_number = DB::table('easemeans_members')
    ->whereRaw("JSON_EXTRACT(safehaven_account_data, '$.accountNumber') = ?", [$data['creditAccountNumber']])
    ->first();

   // return response()->json(['status' => 'success', 'message' => 'Webhook processed successfully', 'data' => $user_with_acc_number, 'payload' => $payload]);


    
        
        // Step 3: Perform business logic
       if ($type === 'transfer' && ($data['type'] ?? '') === 'Inwards') {
            // Example: Save to the database
            $transaction = new Transactions();
            $transaction->user_id = $user_with_acc_number->id;
            $transaction->service = $type;
            $transaction->category = "DEPOSIT";
            $transaction->amount = strval($data['amount']);
            $transaction->tx_detail = json_encode($data);
            $transaction->status = "SUCCESS";
            $transaction->save();
        }

        if ($type === 'transfer' && ($data['type'] ?? '') === 'Outwards') {
            // Example: Save to the database
            $transaction = new Transactions();
            $transaction->user_id = $user_with_acc_number->id;
            $transaction->service = $type;
            $transaction->category = "WITHDRAWAL";
            $transaction->amount = strval($data['amount']);
            $transaction->tx_detail = json_encode($data);
            $transaction->status = "SUCCESS";
            $transaction->save();
        }
    
        // Log the received payload for debugging

        \Illuminate\Support\Facades\Log::info('Webhook received', $payload);
    
        // Step 4: Return a response
        return response()->json(['status' => 'success', 'message' => 'Webhook processed successfully']);
    }
    

}
