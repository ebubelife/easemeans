<?php

namespace App\Http\Controllers;

use App\Models\Members;
use Illuminate\Http\Request;
use App\Http\Controllers\SafeHaven;
use App\Http\Controllers\CardUsersController;
use App\Http\Controllers\VirtualAccountsController;
use App\Mail\NewUserEmailCode;
use App\Mail\ForgotPasswordOtp;
use App\Models\VirtualAccounts;
use Illuminate\Support\Facades\Mail;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
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

                   $member_safehaven_info = json_decode($member->safehaven_account_data);


                   //update subaccount information from safehaven endpoint
                   $sub_account_controller = new VirtualAccountsController();
                   $safehaven_sub_account_info = $sub_account_controller->get_sub_account_single($member_safehaven_info->_id, $validated["user_id"]);


                   //get USD wallet data from sudo
                   $card_user_controller = new CardUsersController();
                   $wallet_data = $card_user_controller->getSingleSudoAccount($member->sudo_account_id, $member->id);


                   

                   

                   //refresh user data object from database
                   $member = Members::find($validated["user_id"]) ;

                   if($member->transaction_pin != $validated["pin"] ){
                    return response()->json(['success' => false, 'status' => 'SUCCESS', 'message'=>"Wrong pin",  'user_data'=>$member, "sudo_wallet_data"=> $wallet_data], 400);

                   }

                    return response()->json(['success' => true, 'status' => 'SUCCESS', 'message'=>"login successful",'user_data'=>$member, "sudo_wallet_data"=> $wallet_data], 200);
                
            }else{

                return response()->json([
                    'success' =>false,
                    'status' => 'USER_NOT_EXISTS',
                    'message' => "User with that ID does not exist" .$validated["user_id"],
                    
                ],400);

            }

        }

        public function login(Request $request)
        {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ]);
        
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }
        
            // Attempt to authenticate using the Members model
            $member = Members::where('email', $request->email)->first();
        
            if (!$member || !Hash::check($request->password, $member->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }
        
            // Generate an API token
           // $token = $member->createToken('API Token')->plainTextToken;
        
           $member_safehaven_info = json_decode($member->safehaven_account_data);


           //update subaccount information from safehaven endpoint
           $sub_account_controller = new VirtualAccountsController();
           $safehaven_sub_account_info = $sub_account_controller->get_sub_account_single($member_safehaven_info->_id, $validated["user_id"]);


           //get USD wallet data from sudo
           $card_user_controller = new CardUsersController();
           $wallet_data = $card_user_controller->getSingleSudoAccount($member->sudo_account_id, $member->id);


           //retrieve member object again
           $member = Members::where('email', $request->email)->first();
           
            // Return a successful response
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'user_data' => $member,
               // 'token' => $token,
                'data' => [
                'member' => $member,
                   // 'token' => $token
                ]
            ]);
        }

        public function get_members(){

            $all = Members::all();
            return response()->json(['success' => true, 'status' => 'SUCCESS', 'user_data'=>$all], 200);
               
        }

        public function send_verification_email(Request $request){

            $validated = $request->validate([
               
                
                'email' => 'required|string',
               
    
            ]);

             //check if email exists
             $member = Members::where('email', $validated["email"])->first();

             $name = $member->first_name;
             $otp = random_int(100000, 999999); // Generate a random OTP
             $verificationLink = "--"; // Create verification link

             //re-save otp
             $member->email_verification_code = $otp;
             $member->save();
     
             try {
             Mail::to($member->email)->send(new ForgotPasswordOtp($name, $otp, $verificationLink));
             return response()->json(['success' => true, 'status' => 'SUCCESS', 'user_data'=>$member], 200);
                
            } catch (Exception $e) {
                // Log the error message for debugging
                Log::error('Error sending email: ' . $e->getMessage());
                return response()->json(['success' => false, 'status' => 'EMAIL_NOT_SENT', 'message'=>$e->getMessage(),  'user_data'=>$member], 400);
                

            }



        }


        public function change_password(Request $request)
{
    $validated = $request->validate([
        'user_id' => 'required|string',
        'password' => [
            'required',
            'string',
            'min:8', // Minimum 8 characters
            'regex:/[a-z]/', // At least one lowercase letter
            'regex:/[A-Z]/', // At least one uppercase letter
            'regex:/[0-9]/', // At least one number
            'regex:/[@$!%*?&]/', // At least one special character
        ],
    ]);

    // Check if the member exists
    $member = Members::find($validated['user_id']);

    if ($member) {
        // Update and save the new hashed password
        $member->password = Hash::make($validated['password']);
        $member->save();

        return response()->json([
            'success' => true,
            'status' => 'SUCCESS',
            'user_data' => $member,
        ], 200);
    } else {
        return response()->json([
            'success' => false,
            'status' => 'USER_NOT_EXISTS',
            'message' => "User with ID {$validated['user_id']} does not exist.",
        ], 400);
    }
}

       public function update_user_address(Request $request){

        $validated = $request->validate([


            'user_id' => 'required|string',
            'address_line1' => 'required|string',
            'address_line2' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
           
            'phone' => 'required|string',

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

        else{

           // Convert the validated data to a JSON string
            $addressJson = json_encode($validated);

            // Save the JSON string to the member's address property
            $member->address = $addressJson;

            //create new customer and USD wallet for user after address has been saved
            $customer_controller = new CardUsersController();
            $create_new_customer_usd_wallet = $customer_controller->createCustomer($validated["user_id"]);
            
            if( !$create_new_customer_usd_wallet){
                return response()->json(['success' => false, 'status' => "COULD_NOT_CREATE_SUDO_DATA"],400);

            }

            // Save the member model to the database
            $member->save();

            return response()->json([
                'success' =>true,
                'status' => 'success',
                'message' => "Address has been updated",
                'user_data' => $member,
                //'new_customer_data' =>$create_new_customer_usd_wallet

            ],200);
        }
        }

       


        public function update_user_dob(Request $request){

            $validated = $request->validate([
    
    
                'user_id' => 'required|string',
                'dob' => 'required|string',
               
    
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
    
            else{
    
                 $member->dob = $validated["dob"];
    
                // Save the member model to the database
                $member->save();
    
                return response()->json([
                    'success' =>true,
                    'status' => 'success',
                    'message' => "Dob has been updated",
                    'user_data' => $member,
    
                ],200);
            }
            }
    
           
   
}
