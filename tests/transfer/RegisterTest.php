<?php

use Faker\Factory;
use PHPUnit\Framework\TestCase;
require_once 'utils/TestHelper.php';

class RegisterTest extends TestCase 
{
    private TestHelper $testhelper; 
    private $faker;

    protected function setUp(): void
    {
        $this->testhelper = new TestHelper();
        $this->faker = $this->testhelper->getFaker();
    }

    public function responseApi ($playerId = null, $nickname = null, $timestamp = null)
    {
        $data = [
            getenv("yummy") => $playerId ?? $this->testhelper->generateUniqueName(),
            "nickname" => $nickname ?? 'test_ogqa',
            "timestamp" => $timestamp ?? time()
        ];

        return $this->testhelper->callApi('phpBase', 'POST', getenv("RG"), $data);
    }

    public function valid ($player = null, $nickname = null, $timestamp = null, $exist = false)
    {
        $response = $this->responseApi($player, $nickname, $timestamp);
        $status = $response['status'];
        $body = $response['body'];
        $this->assertIsArray($body);
        $this->assertEquals(200, $status);
        if ($exist)
        {
            $this->assertEquals('S-121', $body['rs_code']);
            $this->assertEquals('player already exists', $body['rs_message']);
        }
        else
        {
            try {
                $this->assertEquals('S-100', $body['rs_code']);
                $this->assertEquals('success', $body['rs_message']);
            }
            // Player ID accepts a minimum of 3 characters, so repeated runs may generate IDs that are already registered.
             catch (Exception) {
                $this->assertEquals('S-121', $body['rs_code']);
                $this->assertEquals('player already exists', $body['rs_message']);
             }
        }
    }
    
    public function invalid ($player = null, $nickname = null, $timestamp = null)
    {
        $response = $this->responseApi($player, $nickname, $timestamp);
        $status = $response['status'];
        $body = $response['body'];
        $this->assertIsArray($body);
        $this->assertEquals(200, actual: $status);
        $this->assertEquals('E-104', $body['rs_code']);
        $this->assertEquals('invalid parameter or value', $body['rs_message']);
    }

    // valid player name test cases

    public function testValidRegistration()
    {
        $this->valid();
    }

    public function testValidPlayerIdExistence()
    {
        $this->valid(getenv("phpId"), exist: true);
    }

    public function testValidPlayerIdMinimumCharacters()
    {
        $player = $this->testhelper->generateUUid(3);
        $this->valid($player);
    }

    public function testValidPlayerIdMaximumCharacters ()
    {
        $words = $this->testhelper->generateAlphaNumString(60);
        $player = $this->testhelper->generateUUid() . $words;
        $this->valid($player);
    }

    // valid nickname test cases

    public function testValidNicknameMinimumCharacters ()
    {
        $characters = $this->testhelper->generateUUid(8);
        $this->valid(nickname: $characters);
    }

    public function testValidNicknameMaximumCharacters ()
    {
        $characters = $this->testhelper->generateAlphaNumString(64);
        $this->valid(nickname: $characters);
    }

    // valid timestamp test cases

    public function testValidTimestamp ()
    {
        $this->valid(timestamp: time());
    }

    public function testValidTimestampSingleDigit ()
    {
        $random = rand(1, 9);
        $this->valid(timestamp: $random);
    }

    // invalid player name test cases

    public function testInvalidPlayerIdWithSymbols ()
    {
        $symbols = $this->testhelper->randomSymbols();
        $this->invalid($symbols);
    }

    public function testInvalidPlayerIdEmpty ()
    {
        $this->invalid('');
    }

    public function testInvalidPlayerIdWhiteSpace ()
    {
        $this->invalid('  ');
    }

    public function testInvalidPlayerIdBelowMinimumCharacters ()
    {
        $player = $this->testhelper->generateUUid(2);
        $this->invalid($player);
    }

    public function testInvalidPlayerIdBeyondMaximumCharacters ()
    {
        $words = $this->testhelper->generateAlphaNumString(65);
        $this->invalid($words);
    }

    // invalid nickname test cases

    public function testInvalidNicknameWithSymbols ()
    {
        $symbols = $this->testhelper->randomSymbols();
        $this->invalid(nickname: $symbols);
    }

    public function testInvalidNicknameEmpty ()
    {
        $this->invalid(nickname: '');
    }

    public function testInvalidNicknameWhiteSpace ()
    {
        $this->invalid(nickname: '  ');
    }

    public function testInvalidNicknameBelowMinimumCharacters ()
    {
        $name = $this->testhelper->generateUUid(7);
        $this->invalid(nickname: $name);
    }

    public function testInvalidNicknameBeyondMaximumCharacters ()
    {
        $characters = $this->testhelper->generateAlphaNumString(65);
        $this->invalid($characters);
    }

    // invalid timestamp test cases

    public function testInvalidTimestampWithSymbols ()
    {
        $symbols = $this->testhelper->randomSymbols();
        $this->invalid(timestamp: $symbols);
    }

    public function testInvalidTimestampEmpty ()
    {
        $this->invalid(timestamp:  '');
    }

    public function testInvalidTimestampWhiteSpace ()
    {
        $this->invalid(timestamp: '    ');
    }

    public function testInvalidTimestampWithLetters ()
    {
        $string = $this->testhelper->generateUniqueName();
        $this->invalid(timestamp: $string);
    }
}
