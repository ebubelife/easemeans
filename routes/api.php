<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MembersController;
use App\Http\Controllers\SafeHaven;
use App\Http\Controllers\BVNVerify;

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


});

Route::controller(SafeHaven::class)->group(function(){

     //generate token
     Route::get('/v1/generate_token', 'exchange_safehaven_client_assertion');



});

Route::controller(BVNVerify::class)->group(function(){

    //send BVN otp
    Route::post('/v1/send_bvn_otp', 'send_bvn_otp');

    //verify BVN otp
    Route::post('/v1/verify_bvn_otp', 'verify_bvn_otp');



});
