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


        $client = new Client();
    
        $url = 'https://api.sandbox.sudo.cards/customers';
        $apiKey = env('SUDO_SANDBOX_API_KEY');
    
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJfaWQiOiI2NzFhMzZhZGViODA5ZjcxM2QzYjQwZmYiLCJlbWFpbEFkZHJlc3MiOiJlYXNlbWVhbnNAZ21haWwuY29tIiwianRpIjoiNjc5OWY1ZTA2MGZiOTBmZTdiOGMwYmUyIiwibWVtYmVyc2hpcCI6eyJfaWQiOiI2NzFhMzZhZGViODA5ZjcxM2QzYjQxMDIiLCJidXNpbmVzcyI6eyJfaWQiOiI2NzFhMzZhZGViODA5ZjcxM2QzYjQwZmQiLCJuYW1lIjoiRUFTRU1FQU5TIExURCIsImlzQXBwcm92ZWQiOnRydWV9LCJ1c2VyIjoiNjcxYTM2YWRlYjgwOWY3MTNkM2I0MGZmIiwicm9sZSI6IkFQSUtleSJ9LCJpYXQiOjE3MzgxNDMyMDAsImV4cCI6MTc2OTcwMDgwMH0.lhuz6eV-qPb6Xv8LvRrlt7Tr36Cl6sj87fUuq5aDuhA',
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
