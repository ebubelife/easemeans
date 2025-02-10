<?php

namespace App\Http\Controllers;

use App\Models\CardUsers;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\Members;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CardUsersController extends Controller
{

    
    public function createCustomer($user_id)
    {

      /*  $request->validate([
            'id' => 'required|string', // Can be email or username
           
        ]);*/

        $member =  Members::where("id", $user_id)->first();
        $member_address = json_decode($member->address);

       // return $member_address->address_line1;


        $client = new Client();
    
        $url = 'https://api.sandbox.sudo.cards/customers';
        $apiKey = env('SUDO_SANDBOX_API_KEY');
    
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $apiKey,
        ];
    
        $body = [
            "type" => "individual",
            "name" => $member->first_name . " " . $member->last_name,
            "status" => "active",
            "emailAddress" => $member->email,
            "phoneNumber" => $member_address->phone,
            "individual" => [
                "firstName" => $member->first_name,
                "lastName" => $member->last_name,
                "dob" => $member->dob, // Ensure the format is YYYY/MM/DD
                "identity" => [
                    "type" => "BVN",
                    "number" => $member->bvn, // Ensure BVN is stored securely
                ],
            ],
            "billingAddress" => [
                "line1" => $member_address->address_line1,
                "line2" => $member_address->address_line2 ?? "",
                "city" => $member_address->city,
                "state" => $member_address->state,
                "country" => "Nigeria",
                "postalCode" =>  "300001",
            ],
            "company" => [
                "officer" => [
                    "firstName" => $member->first_name,
                    "lastName" => $member->last_name,
                ],
            ],
        ];
      
            $response = $client->post($url, [
                'headers' => $headers,
                'json' => $body,
            ]);
    
            $responseBody = json_decode($response->getBody(), true);

            //create card for new card holder
            if($responseBody["statusCode"] == 200){

              //create wallet for the new customer
            //https://docs.sudo.africa/reference/create-account

            $create_USD_wallet = $this->createUSDWallet($responseBody["data"]["_id"]);
            if($create_USD_wallet["statusCode"] == 200){

                    $member->sudo_customer_id = $responseBody["data"]["_id"];
                    $member->sudo_customer_data = json_encode($responseBody["data"]);

                    $member->sudo_account_id = $create_USD_wallet["data"]["_id"];
                    $member->sudo_account_data = json_encode($create_USD_wallet["data"]);
                    $member->save();

                    //return true
                    return true;

                    
            }else{
                Log::error('USD wallet creation failed', ['user_id' => $member->id, 'message' => $responseBody["message"] ]);
                return false;
            }




            
            }else{
                Log::error('Sudo card customer creation failed', ['user_id' => $member->id, 'message' => $responseBody["message"] ]);
                return false;
            }
           
          //  return response()->json(["customer_creation_feedback"=>$responseBody,"sudo_account_creation_feedback"=>$create_USD_wallet]);
    
       
    }

    public function createCard(Request $request)
{

    $member = Members::find($request->user_id);
    $sudo_account_id = $member->sudo_account_id;

    $sudo_customer_id = $member->sudo_customer_id;


    //https://docs.sudo.africa/reference/create-card
    $client = new Client();

    $url = 'https://api.sandbox.sudo.cards/cards';
    $apiKey = env('SUDO_SANDBOX_API_KEY');

    $headers = [
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer '.$apiKey,
    ];

    $body = [
        "customerId" => $sudo_customer_id,
        "type" => "virtual",
        
       "fundingSourceId" => "671a36aeeb809f713d3b4104",
        "brand" => "Visa",
        "debitAccountId" => $sudo_account_id,
        "currency" => "USD",
        
        "status" => "active",
        "amount" => 3,
        "expirationDate"=> "",
        "issuerCountry"=> "USA"
    ];

    try {
        $response = $client->post($url, [
            'headers' => $headers,
            'json' => $body,
        ]);

        $responseBody = json_decode($response->getBody(), true);

        if($responseBody["statusCode"]==200){
            return response()->json(["success"=>true,"new_card_data"=>$responseBody, "user_data"=>$member, "message"=>"Card created successfully", "status"=>"success"], 200);

        }
        else{
            return response()->json(["success"=>false, "user_data"=>$member, "message"=>$responseBody["message"], "status"=>"error"], 400);
            Log::error('Card creation failed', ['user_id' => $member->id, 'message' => $responseBody["message"] ]);


        }
       

       //return true;

    } catch (\Exception $e) {
        return false;
        return response()->json([
            'error' => $e->getMessage(),
            "user_data"=>$member,
            "success"=> false,
            "status"=>"error"
        ], 500);
    }
}


public function createUSDWallet($customerId)
{

    //https://docs.sudo.africa/reference/create-account
    
    $client = new Client();

    $url = 'https://api.sandbox.sudo.cards/accounts';
    $apiKey = env('SUDO_SANDBOX_API_KEY');

    $headers = [
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer '.$apiKey,
    ];

    $body = [
        "customerId" => $customerId,
        "accountType"=> "Savings",
        "currency"=>"USD",
        "type"=>"wallet",
        
      
    ];

    try {
        $response = $client->post($url, [
            'headers' => $headers,
            'json' => $body,
        ]);

        $responseBody = json_decode($response->getBody(), true);
        return $responseBody ;

      

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function getFundingSources()
{
    //this function isn't really relevant
    $response = Http::withHeaders([
        'Authorization' => env('SUDO_SANDBOX_API_KEY'),
    ])->get('https://api.sandbox.sudo.cards/fundingsources');

    if ($response->successful()) {
        return $response->json(); // Returns an associative array
    }

    return response()->json([
        'success' => false,
        'error' => 'Failed to fetch funding sources',
        'status' => $response->status(),
        'message' => $response->body()
    ], $response->status());
}

public function getCustomers()
{
    //this function isn't really relevant
    $response = Http::withHeaders([
        'Authorization' => env('SUDO_SANDBOX_API_KEY'),
    ])->get('https://api.sandbox.sudo.cards/customers');

    if ($response->successful()) {
        return $response->json(); // Returns an associative array
    }

    return response()->json([
        'success' => false,
        'error' => 'Failed to fetch customers',
        'status' => $response->status(),
        'message' => $response->body()
    ], $response->status());
}

public function getSudoAccounts(){
    //get sudo debit accounts NOT to be confused with safehaven sub accounts
    $response = Http::withHeaders([
        'Authorization' => env('SUDO_SANDBOX_API_KEY'),
    ])->get('https://api.sandbox.sudo.cards/accounts');

    if ($response->successful()) {
        return $response->json(); // Returns an associative array
    }

    return response()->json([
        'success' => false,
        'error' => 'Failed to fetch funding sources',
        'status' => $response->status(),
        'message' => $response->body()
    ], $response->status());

}
public function getSingleSudoAccount($accountId, $user_id){
    //get sudo debit accounts NOT to be confused with safehaven sub accounts
    $response = Http::withHeaders([
        'Authorization' => env('SUDO_SANDBOX_API_KEY'),
    ])->get('https://api.sandbox.sudo.cards/accounts/'.$accountId);

  

    if ($response->successful()) {

       

        $member = Members::find($user_id);

       // return response()->json(["success"=>false, "user_data"=>$member]);

        $responseBody = json_decode($response->getBody(), true);
        $member->sudo_account_data = json_encode($responseBody["data"]);
        $member->save();
        return response()->json(["success"=>false, "user_data"=>$member, "message"=>$responseBody, "status"=>"success"], 200);
    }

    return response()->json([
        'success' => false,
        'error' => 'Failed to fetch sudo account',
        'status' => $response->status(),
        'message' => $response->body()
    ], $response->status());

}

public function getCustomerCards(Request $request){

    $member = Members::find($request->user_id);
    $sudo_account_id = $member->sudo_account_id;

    $sudo_customer_id = $member->sudo_customer_id;
     //get virtual cards belonging to customer
     $response = Http::withHeaders([
        'Authorization' => env('SUDO_SANDBOX_API_KEY'),
    ])->get('https://api.sandbox.sudo.cards/cards/customer/'.$sudo_customer_id);

    if ($response->successful()) {
        return $response->json(); // Returns an associative array
    }

    return response()->json([
        'success' => false,
        'error' => 'Failed to fetch funding sources',
        'status' => $response->status(),
        'message' => $response->body()
    ], $response->status());

}
   
}
