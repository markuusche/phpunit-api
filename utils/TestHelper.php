<?php

use Faker\Factory;
use Ramsey\Uuid\Uuid;

class TestHelper
{
    protected $config;
    protected $faker;
    
    public function __construct()
    {
        $this->faker = Factory::create();
    }

    public function getFaker()
    {
        return $this->faker;
    }
    
    public function callApi($base, $method, $endpoint, $data = [], $queryParams = [])
    {
        $client = new GuzzleHttp\Client;
        $options = [
            'json' => $data,
            'headers' => [
                "Content-Type" => "application/json",
                getenv("okay") => getenv('phpOpKey'),
                getenv("apple") => getenv('phpOp'),
            ]
        ];

        if ($method === 'GET') {
            unset($options['json']);
            $options['query'] = $queryParams;
        }

        $response = $client->request($method, getenv($base). $endpoint, $options);

        return [
            'status' => $response->getStatusCode(),
            'body' => json_decode($response->getBody(), true)
        ];
    }

    function randomSymbols($length = 10)
    {
        $symbols = '!@#$%^&*()_+-=[]{};:,.<>?';
        return substr(str_shuffle(str_repeat($symbols, ceil($length / strlen($symbols)))), 0, $length);
    }
    
    function generateAlphaNumString($length) {
        return substr(bin2hex(random_bytes(ceil($length / 2))), 0, $length);
    }

    function generateRandomNumber($min=1000.01, $max=15000.99)
    {
        return round(mt_rand($min * 100, $max * 100) / 100, 2);
    }

    function generateUuid($length=4)
    {
        $uuid = Uuid::uuid4()->toString();
        $slicedUuid = substr($uuid, 0, $length);
        return $slicedUuid;
    }

    function generateUniqueName($length = 4)
    {
        $slicedUuid = $this->generateUUid($length);
        $name = 'unique' . $this->faker->word() . $slicedUuid . '_test_qa';
        return $name;
    }

    function generateLongNumbers ($length = 100)
    {
        $long = '';
        while (strlen($long) < $length) {
            $long .= time();
        }
        
        $long = substr($long, 0, $length);
        return $long;
    }

    function generateRandomLetters ($length = 10)
    {
        $alpha = "abcdefghijklmnopqrstuvwxyz";
        $letters = '';
        while (strlen($letters) < $length){
            $letters .= $alpha[rand(0, strlen($alpha) - 1)];
        }
        return $letters;
    }

    function randomArrayChoice($array)
    {
        $random = array_rand($array);
        $choice = $array[$random];
        return $choice;
    }
}
