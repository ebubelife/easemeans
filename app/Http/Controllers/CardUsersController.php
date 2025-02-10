<?php

namespace App\Http\Controllers;

use App\Models\CardUsers;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\Members;

class CardUsersController extends Controller
{

    
    public function createCustomer(Request $request)
    {

        $request->validate([
            'id' => 'required|string', // Can be email or username
           
        ]);

        $member =  Members::where("id", $request->id)->first();
        $member_address = json_decode($member->address);

        return $member_address->address_line1;


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
                "line1" => $member_address["address_line1"],
                "line2" => $member_address["address_line2"],
                "city" => $member_address["city"],
                "state" => $member_address["state"],
                "country" => "NG",
                "postalCode" => "800001",
            ],
        ];
    
      
            $response = $client->post($url, [
                'headers' => $headers,
                'json' => $body,
            ]);
    
            $responseBody = json_decode($response->getBody(), true);

            //create card for new card holder
            if($responseBody["statusCode"] == 200){

               $create_card = $this->createCard($responseBody["data"]["_id"]);
                return $create_card;
            }
            return response()->json($responseBody);
    
       
    }

    public function createCard($customerId)
{
    $client = new Client();

    $url = 'https://vault.sandbox.sudo.cards/cards';
    $apiKey = env('SUDO_SANDBOX_API_KEY');

    $headers = [
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer '.$apiKey,
    ];

    $body = [
        "customerId" => $customerId,
        "type" => "virtual",
       // "number" => "5061000001743021565",
        "currency" => "USD",
        "status" => "active",
    ];

    try {
        $response = $client->post($url, [
            'headers' => $headers,
            'json' => $body,
        ]);

        $responseBody = json_decode($response->getBody(), true);
        return response()->json($responseBody);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
        ], 500);
    }
}
   
}
