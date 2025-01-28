<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MembersController;
use App\Http\Controllers\SafeHaven;
use App\Http\Controllers\BVNVerify;
use App\Http\Controllers\VirtualAccountsController;
use App\Http\Controllers\AirtimeServiceController;
use App\Http\Controllers\DataServiceController;
use App\Http\Controllers\TransactionsController;
use App\Http\Controllers\CardUsersController;
use App\Models\AirtimeService;
use App\Models\VirtualAccounts;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(MembersController::class)->group(function(){
    //check if email exists
    Route::post('/v1/check_if_email_exists', 'check_if_email_exists');

    //verify bvn and get information associated with bvn
    Route::post('/v1/verify_bvn', 'verify_bvn');

    //new account with email and password
    Route::post('/v1/new_account_email_password', 'new_account_email_password');

     //verify email - send code to email
    Route::post('/v1/send_email_otp/{email}', 'send_email_verify_code');

      //resend code to email
    Route::post('/v1/resend_otp_email', 'resend_otp_email');

     //verify email 
    Route::post('/v1/verify_email', 'verify_email');

    //send email otp for forgot password
    Route::post('/v1/user/recover/send_otp', 'send_verification_email');

     //change password
     Route::post('/v1/user/change_password', 'change_password');

   

    //update transaction pin
    Route::post('/v1/update/transaction_pin', 'set_transaction_pin');

    //check transaction pin
    Route::post('/v1/check/transaction_pin', 'check_transaction_pin');

     //get users
    Route::get('/v1/users/all', 'get_members');

     //login
     Route::post('/v1/users/login', 'login');



});

Route::controller(SafeHaven::class)->group(function(){

     //generate token
     Route::get('/v1/generate_token', 'exchange_safehaven_client_assertion');

     //get safe haven services
     Route::get('/v1/get_services', 'get_services');

     //get safe haven services categories
     Route::get('/v1/get_service_categories/{service_id}', 'get_service_categories');

     //get safe haven services categories
     //to get the service category id parameter, run the request to endpoint to get all services category and use the id for  any of them
     Route::get('/v1/get_products/{service_category_id}', 'get_products');
     



});

Route::controller(BVNVerify::class)->group(function(){

    //send BVN otp
    Route::post('/v1/send_bvn_otp', 'send_bvn_otp');

    //verify BVN otp
    Route::post('/v1/verify_bvn_otp', 'verify_bvn_otp');



});


Route::controller(VirtualAccountsController::class)->group(function(){

    //create sub account
    Route::post('/v1/create_subaccount', 'create_sub_account');

    //get sub accounts
    Route::get('/v1/accounts/get', 'get_sub_accounts');


     //get  account
     Route::get('/v1/account/{_id}/{user_id}', 'get_sub_account_single');

  



});

Route::controller(AirtimeServiceController::class)->group(function(){

    //buy airtime
    Route::post('/v1/airtime/purchase', 'purchase');


    
  



});


Route::controller(DataServiceController::class)->group(function(){

    //buy airtime
    Route::post('/v1/data/purchase', 'purchase');


    
  



});


Route::controller(TransactionsController::class)->group(function(){

    //get all transactions
    Route::get('/v1/transactions/all', 'get_all_tx');

    //get all transactions by user
    Route::get('/v1/transactions/user/all/{user_id}', 'get_all_user_tx');

    //get one transactions by user
    Route::get('/v1/transactions/user/single/{user_id}', 'get_user_single_tx');

     //process transaction from webhook
     Route::post('/v1/webhook', 'process_webhook');

  

});

Route::controller(CardUsersController::class)->group(function(){
    //create new card customer & card
    Route::get('/v1/card/customer/new', 'createCustomer');


});



