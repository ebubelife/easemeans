<?php

namespace App\Http\Controllers;

use App\Models\CardUsers;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class CardUsersController extends Controller
{

    public function createCustomer()
    {
        $client = new Client();
    
        $url = 'https://api.sandbox.sudo.cards/customers';
        $apiKey = env('SUDO_SANDBOX_API_KEY');
    
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic '.$apiKey,
        ];
    
        $body = [
            "type" => "individual",
            "name" => "John Doe",
            "status" => "active",
            "individual" => [
                "firstName" => "John",
                "lastName" => "Doe",
            ],
            "billingAddress" => [
                "line1" => "4 Barnawa Close",
                "line2" => "Off Challawa Crescent",
                "city" => "Barnawa",
                "state" => "Kaduna",
                "country" => "NG",
                "postalCode" => "800001",
            ],
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

    public function createCard()
{
    $client = new Client();

    $url = 'https://vsult.sandbox.sudo.cards/cards';
    $apiKey = env('SUDO_SANDBOX_API_KEY');

    $headers = [
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer '.$apiKey,
    ];

    $body = [
        "customerId" => "5f8b75ef12a06df84bd7aa3a",
        "type" => "physical",
        "number" => "5061000001743021565",
        "currency" => "NGN",
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
