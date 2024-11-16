<?php

namespace App\Http\Controllers;

use App\Models\Transactions;
use Illuminate\Http\Request;

class TransactionsController extends Controller
{
   
    public function get_all(Request $request){

        $txn = Transactions::all();

        return response()->json([
            'success' =>true,
            'status' => 'SUCCESS',
            'message' => "Transactions retrieved successfully",
            'transactions' =>  $txn,
            
            
        ],200);



    }

    public function get_all_user(Request $request, $user_id){

        $txn = Transactions::where('user_id', $user_id)->get();

        return response()->json([
            'success' =>true,
            'status' => 'SUCCESS',
            'message' => "Transactions retrieved successfully",
            'transactions' =>  $txn,
            
            
        ],200);



    }

    public function get_all_user_single(Request $request, $user_id, $transaction_id){

        $txn = Transactions::where('user_id', $user_id)->where('id', $transaction_id)->get();
        return response()->json([
            'success' =>true,
            'status' => 'SUCCESS',
            'message' => "Transactions retrieved successfully",
            'transactions' =>  $txn,
            
            
        ],200);

    }


}
