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
    
    public function callApi($method, $endpoint, $data = [], $queryParams = [])
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

        $response = $client->request($method, getenv('phpbase'). $endpoint, $options);

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
    
    function generateString($length)
    {
        return bin2hex(random_bytes(floor($length / 2)));
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

    function generateUniqueName()
    {
        $slicedUuid = $this->generateUUid();
        $name = 'unique' . $this->faker->word() . $slicedUuid . '_test_qa';
        return $name;
    }
}
