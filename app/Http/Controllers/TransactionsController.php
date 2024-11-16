<?php

namespace App\Http\Controllers;

use App\Models\Transactions;
use Illuminate\Http\Request;

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

        $txn = Transactions::where('user_id', $user_id)->get();

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


        $txn = Transactions::where('user_id', $user_id)->where('id', $transaction_id)->get();
        return response()->json([
            'success' =>true,
            'status' => 'SUCCESS',
            'message' => "Transactions retrieved successfully",
            'transactions' =>  $txn,
            
            
        ],200);

    }


}
