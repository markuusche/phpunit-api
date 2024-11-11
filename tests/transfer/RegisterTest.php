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

        return $this->testhelper->callApi(
            'POST',
            getenv("RG"), 
            $data, 
            queryParams: []);
    }

    public function valid ($player = null, $nickname = null, $timestamp = null, $exist = false)
    {
        $response = $this->responseApi($player, $nickname, $timestamp);
        $status = $response['status'];
        $body = $response['body'];
        $this->assertEquals(200, $status);
        if ($exist)
        {
            $this->assertEquals('S-121', $body['rs_code']);
            $this->assertEquals('player already exists', $body['rs_message']);
        }
        else
        {
            $this->assertEquals('S-100', $body['rs_code']);
            $this->assertEquals('success', $body['rs_message']);
        }
    }
    
    public function invalid ($player = null, $nickname = null, $timestamp = null)
    {
        $response = $this->responseApi($player, $nickname, $timestamp);
        $status = $response['status'];
        $body = $response['body'];
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
        $this->valid(getenv("phpId"), null, null, true);
    }

    public function testValidPlayerIdMinimumCharacters()
    {
        $player = $this->testhelper->generateUUid(3);
        $this->valid($player);
    }

    public function testValidPlayerIdMaximumCharacters ()
    {
        $words = $this->testhelper->generateString(60);
        $player = $this->testhelper->generateUUid() . $words;
        $this->valid($player);
    }

    // valid nickname test cases

    public function testValidNicknameMinimumCharacters ()
    {
        $characters = $this->testhelper->generateUUid(8);
        $this->valid(null, $characters);
    }

    public function testValidNicknameMaximumCharacters ()
    {
        $characters = $this->testhelper->generateString(64);
        $this->valid(null, $characters);
    }

    // valid timestamp test cases

    public function testValidTimestamp ()
    {
        $this->valid(null, null, null);
    }

    public function testValidTimestampSingleDigit ()
    {
        $random = rand(1, 9);
        $this->valid(null, null, $random);
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
        $words = $this->testhelper->generateString(65);
        $this->invalid($words);
    }

    // invalid nickname test cases

    public function testInvalidNicknameWithSymbols ()
    {
        $symbols = $this->testhelper->randomSymbols();
        $this->invalid(null, $symbols);
    }

    public function testInvalidNicknameEmpty ()
    {
        $this->invalid(null, '');
    }

    public function testInvalidNicknameWhiteSpace ()
    {
        $this->invalid(null, '  ');
    }

    public function testInvalidNicknameBelowMinimumCharacters ()
    {
        $name = $this->testhelper->generateUUid(7);
        $this->invalid(null, $name);
    }

    public function testInvalidNicknameBeyondMaximumCharacters ()
    {
        $characters = $this->testhelper->generateString(65);
        $this->invalid($characters);
    }

    // invalid timestamp test cases

    public function testInvalidTimestampWithSymbols ()
    {
        $symbols = $this->testhelper->randomSymbols();
        $this->invalid(null, null,  $symbols);
    }

    public function testInvalidTimestampEmpty ()
    {
        $this->invalid(null, null,  '');
    }

    public function testInvalidTimestampWhiteSpace ()
    {
        $this->invalid(null, null,  '    ');
    }

    public function testInvalidTimestampWithLetters ()
    {
        $string = $this->testhelper->generateUniqueName();
        $this->invalid(null, null,  $string);
    }
}
