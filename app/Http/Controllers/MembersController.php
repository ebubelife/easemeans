<?php

namespace App\Http\Controllers;

use App\Models\Members;
use Illuminate\Http\Request;
use App\Http\Controllers\SafeHaven;
use App\Mail\NewUserEmailCode;
use Illuminate\Support\Facades\Mail;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class MembersController extends Controller
{
    function new_account_email_password(Request $request){

            //get safe haven token
       // $safehaven_api = new SafeHaven();
       // $safehaven_request = $safehaven_api->exchange_safehaven_client_assertion();


         
         $validated = $request->validate([
            'first_name' => 'required|string|',
            'last_name' => 'required|string|',
            
            'email' => 'required|string|email',
            'password' => 'required|string|min:8'

        ]);

        //check if email exists
        $member = Members::where("email",$validated["email"])->first() ;

        if($member){

            return response()->json([
                'success' =>false,
                'status' => 'USER_EXISTS',
                'message' => "That email already exists, please use another or login",
                 'user_data' => $member
            ],401);
        }



       // return $safehaven_request;

       //save user data

       $member = new Members();
       $member->first_name = $validated["first_name"];
       $member->last_name = $validated["last_name"];
       $member->email = $validated["email"];
       $member->password = Hash::make($validated["password"]);
       

       if($member->save()){

        $send_email = $this->send_email_verify_code($validated["email"], $validated["first_name"] );

        if($send_email){
            return response()->json(['success' => true, 'status' => "SUCCESS", 'user_data'=>$member],200);
           }else{

            Log::error('Error sending email: ' . "Email not sent to ".$validated["email"]);

            return response()->json(['success' => false, 'status' => "EMAIL_NOT_SENT"],400);

           }

       }else{

        return response()->json(['success' => false, 'status' => 'SERVER ERROR'], 500);
            
       }

      


    }




    public function send_email_verify_code($email, $name){
      
            //send email
            try {
                $name = $name;
                $otp = random_int(100000, 999999); // Generate a random OTP
                $verificationLink = "--"; // Create verification link
        
                Mail::to($email)->send(new NewUserEmailCode($name, $otp, $verificationLink));

                //save otp to database
                $get_member = Members::where('email', $email)->first();
                $get_member->email_verification_code = $otp; 
                $get_member->save();
            
                return true;
            } catch (Exception $e) {
                // Log the error message for debugging
                Log::error('Error sending email: ' . $e->getMessage());
                
            
                return response()->json(['success' => false,  'message' => $e->getMessage()], 400);
               return false;
            }
    
        }

          

        public function verify_email(Request $request){

            $validated = $request->validate([
               
                
                'id' => 'required|string',
                'code' => 'required|string|min:6'
    
            ]);
    
            //check if id exists
            $member = Members::find($validated["id"]) ;
    
            if($member){
    
                  if($member->email_verification_code==$validated["code"]){

                    //save email verification status
                    $member->email_verified = true;
                    $member->save();

                    return response()->json(['success' => true, 'status' => 'SUCCESS', 'user_data'=>$member], 200);
                       
                  }else{
                    return response()->json(['success' => false, 'status' => 'WRONG_CODE', 'user_data'=>$member], 400);

                  }
            }
    

        }


        public function resend_otp_email(Request $request){

            $validated = $request->validate([
               
                
                'id' => 'required|string',
               
    
            ]);

             //check if email exists
             $member = Members::find($validated["id"]);

             $name = $member->first_name;
             $otp = random_int(100000, 999999); // Generate a random OTP
             $verificationLink = "--"; // Create verification link

             //re-save otp
             $member->email_verification_code = $otp;
             $member->save();
     
             try {
             Mail::to($member->email)->send(new NewUserEmailCode($name, $otp, $verificationLink));
             return response()->json(['success' => true, 'status' => 'SUCCESS', 'user_data'=>$member], 200);
                
            } catch (Exception $e) {
                // Log the error message for debugging
                Log::error('Error sending email: ' . $e->getMessage());
                return response()->json(['success' => false, 'status' => 'EMAIL_NOT_SENT', 'message'=>$e->getMessage(),  'user_data'=>$member], 400);
                

            }





        }

        public function set_transaction_pin(Request $request){

            $validated = $request->validate([
               
                
                'user_id' => 'required|string',
                'pin' => 'required|string'
    
            ]);
    
            //check if id exists
            $member = Members::find($validated["user_id"]) ;
    
            if($member){
    
                 

                    //save email verification status
                    $member->transaction_pin = $validated["pin"];
                    $member->save();

                    return response()->json(['success' => true, 'status' => 'SUCCESS', 'user_data'=>$member], 200);
                
            }else{

                return response()->json([
                    'success' =>false,
                    'status' => 'USER_NOT_EXISTS',
                    'message' => "User with that ID does not exist" .$validated["user_id"],
                    
                ],400);

            }

        }
    

        public function check_transaction_pin(Request $request){

            $validated = $request->validate([
               
                
                'user_id' => 'required|string',
                'pin' => 'required|string'
    
            ]);
    
            //check if id exists
            $member = Members::find($validated["user_id"]) ;
    
            if($member){

                   if($member->transaction_pin != $validated["pin"] ){
                    return response()->json(['success' => false, 'status' => 'SUCCESS', 'message'=>"Wrong pin",  'user_data'=>$member], 400);

                   }

                    return response()->json(['success' => true, 'status' => 'SUCCESS', 'message'=>"login successful",'user_data'=>$member], 200);
                
            }else{

                return response()->json([
                    'success' =>false,
                    'status' => 'USER_NOT_EXISTS',
                    'message' => "User with that ID does not exist" .$validated["user_id"],
                    
                ],400);

            }

        }
    

        public function get_members(){

            $all = Members::all();
            return response()->json(['success' => true, 'status' => 'SUCCESS', 'user_data'=>$all], 400);
               
        }

       
   
}
