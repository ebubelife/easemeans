<?php

namespace App\Http\Controllers;

use App\Models\CardUsers;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\Members;
use Illuminate\Support\Facades\Http;

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
            "name" => $member->first_name ." ".$member->last_name,
            "status" => "active",
            "individual" => [
                "firstName" => $member->first_name,
                "lastName" => $member->last_name,
            ],
            "billingAddress" => [
                "line1" => $member_address->address_line1,
                "line2" => $member_address->address_line2,
                "city" => $member_address->city,
                "state" => $member_address->state,
                "country" => "NG",
                "postalCode" => "900001",
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
            if($create_USD_wallet){

                    $member->sudo_customer_id = $responseBody["data"]["_id"];
                    $member->sudo_customer_data = json_encode($responseBody["data"]);

                    $member->sudo_account_id = $create_USD_wallet["data"]["_id"];
                    $member->sudo_account_data = json_encode($create_USD_wallet["data"]);
                    $member->save();

                    //return true
                    return true;

                    
            }else{
                return false;
            }




            
            }
            return false;
          //  return response()->json(["customer_creation_feedback"=>$responseBody,"sudo_account_creation_feedback"=>$create_USD_wallet]);
    
       
    }

    public function createCard($customerId, $user_account_number)
{


    //https://docs.sudo.africa/reference/create-card
    $client = new Client();

    $url = 'https://api.sandbox.sudo.cards/cards';
    $apiKey = env('SUDO_SANDBOX_API_KEY');

    $headers = [
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer '.$apiKey,
    ];

    $body = [
        "customerId" => $customerId,
        "type" => "virtual",
        
       "fundingSourceId" => "671a36aeeb809f713d3b4104",
        "brand" => "visa",
        "debitAccountId" => $user_account_number,
        "currency" => "USD",
        "issuerCountry" => "USA",
        "status" => "active",
    ];

    try {
        $response = $client->post($url, [
            'headers' => $headers,
            'json' => $body,
        ]);

        $responseBody = json_decode($response->getBody(), true);
        return response()->json($responseBody);

       //return true;

    } catch (\Exception $e) {
        return false;
        return response()->json([
            'error' => $e->getMessage(),
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
       // return $responseBody ;

       return true;

    } catch (\Exception $e) {
        return false;
       
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
        'error' => 'Failed to fetch funding sources',
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
        'error' => 'Failed to fetch funding sources',
        'status' => $response->status(),
        'message' => $response->body()
    ], $response->status());

}
   
}
