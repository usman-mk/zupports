<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class MapController extends BaseController
{
    public function searchPlaces($q) {
        try {
            // cache key
            $cacheKey = "search-place-cache:$q";
            // cache expired (minutes)
            $expired = 60;
            $places = Cache::remember($cacheKey, $expired, function () use ($q) {
                // create client for call API
                $client = new Client();
                // get request Google API
                $response = $client->get('https://maps.googleapis.com/maps/api/place/findplacefromtext/json', [
                    'query' => [
                        'key' => env('GOOGLE_MAP_API_KEY'), // google API Key
                        'input' => $q, // keyword
                        'inputtype' => 'textquery', // search type
                        'fields' => 'formatted_address,name,geometry,place_id', // fields return
                    ],
                ]);

                $data = json_decode($response->getBody(), true);

                // check status
                if($data['status'] === 'OK'){
                    return $data['candidates'][0];
                } else{
                    return null;
                }
            });

            return $this->sendResponse($places, 'Success');
        } catch (Exception $exception) {
            return $this->sendError('Internal error', $exception->getMessage(), 500);
        }
    }

    public function getNearby(Request $request) {
        try {
            $searchLatitude = $request->search_latitude;
            $searchLongitude = $request->search_longitude;
            $currentLatitude = $request->current_latitude ?? $searchLatitude;
            $currentLongitude = $request->current_longitude ?? $searchLongitude;
            $distance = $request->distance;
            $pageToken = $request->pageToken;

            $apiKey = env('GOOGLE_MAP_API_KEY');
            // create client for call API
            $client = new Client();
            // get request Google API
            $response = $client->get('https://maps.googleapis.com/maps/api/place/nearbysearch/json', [
                'query' => [
                    'key' => $apiKey, // google API Key
                    'location' => "$searchLatitude,$searchLongitude", // search location from latitude,longitude
                    'radius' =>  $distance * 1000, // area
                    'type' => 'restaurant', // type search
                    'pagetoken' => $pageToken, // token for view more location
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            $restaurants = [];
            foreach ($data['results'] as $result) {
                $restaurant = [
                    'id' => $result['place_id'],
                    'name' => $result['name'],
                    'address' => $result['vicinity'],
                    'photo' => isset($result['photos'][0]['photo_reference']) ? 'https://maps.googleapis.com/maps/api/place/photo?maxwidth=400&photoreference=' . $result['photos'][0]['photo_reference'] . "&key=$apiKey" : null,
                    'latitude' => $result['geometry']['location']['lat'],
                    'longitude' => $result['geometry']['location']['lng'],
                    'distance' => $this->getClientDistance("$currentLatitude,$currentLongitude", $result['geometry']['location']['lat'].",".$result['geometry']['location']['lng']) // คำนวณหาระยะทางระหว่างตำแหน่งปัจจุบันกับตำแหน่งของร้านอาหาร
                ];
                // push data to array
                $restaurants[] = $restaurant;
            }

            return $this->sendResponse(['data' => $restaurants , 'pageToken' => $data['next_page_token'] ?? null], 'Success');

        } catch (Exception $exception) {
            return $this->sendError('Internal error', $exception->getMessage(), 500);
        }
    }

    public function getDistance(Request $request) {
        try {
            $origin = $request->origin;
            $destination = $request->destination;

            $data = $this->getClientDistance($origin, $destination);

            return $this->sendResponse($data, 'Success');

        } catch (Exception $exception) {
            return $this->sendError('Internal error', $exception->getMessage(), 500);
        }
    }

    private function getClientDistance($origin, $destination) {
        try {
            // create client for call API
            $client = new Client();
            // get request Google API
            $response = $client->get('https://maps.googleapis.com/maps/api/distancematrix/json', [
                'query' => [
                    'key' => env('GOOGLE_MAP_API_KEY'), // google API Key
                    'origins' => $origin, // start location
                    'destinations' =>  $destination, // end location
                    'mode' => 'driving', // mode direction
                    'units' => 'metric' // return distance km
                ],
            ]);
            $data = json_decode($response->getBody(), true);

            return $data['rows'][0]['elements'][0]['distance'] ?? null;

        } catch (Exception $exception) {
            return null;
        }
    }
}
