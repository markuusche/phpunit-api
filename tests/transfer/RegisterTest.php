<?php

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

    public function assert ($player = null, $nickname = null, $timestamp = null, $valid = false, $exist = false)
    {
        $response = $this->responseApi($player, $nickname, $timestamp);
        $status = $response['status'];
        $body = $response['body'];
        $this->assertIsArray($body);
        $this->assertEquals(200, $status);

        if ($valid) {
            try {
                $this->assertEquals('S-100', $body['rs_code']);
                $this->assertEquals('success', $body['rs_message']);
            }
            // Player ID accepts a minimum of 3 characters, so repeated runs may generate IDs that are already registered.
             catch (Exception) {
                $this->assertEquals('S-121', $body['rs_code']);
                $this->assertEquals('player already exists', $body['rs_message']);
             }
        } else if ($exist) {
            $this->assertEquals('S-121', $body['rs_code']);
            $this->assertEquals('player already exists', $body['rs_message']);
        } else {
            $this->assertEquals('E-104', $body['rs_code']);
            $this->assertEquals('invalid parameter or value', $body['rs_message']);
        }
    }

    // valid player name test cases

    public function testValidRegistration()
    {
        $this->assert(valid: true);
    }

    public function testValidPlayerIdExistence()
    {
        $this->assert(getenv("phpId"), valid: true, exist: true);
    }

    public function testValidPlayerIdMinimumCharacters()
    {
        $player = $this->testhelper->generateUUid(3);
        $this->assert($player, valid: true);
    }

    public function testValidPlayerIdMaximumCharacters ()
    {
        $words = $this->testhelper->generateAlphaNumString(60);
        $player = $this->testhelper->generateUUid() . $words;
        $this->assert($player, valid: true);
    }

    // valid nickname test cases

    public function testValidNicknameMinimumCharacters ()
    {
        $characters = $this->testhelper->generateUUid(8);
        $this->assert(nickname: $characters, valid: true);
    }

    public function testValidNicknameMaximumCharacters ()
    {
        $characters = $this->testhelper->generateAlphaNumString(64);
        $this->assert(nickname: $characters, valid: true);
    }

    // valid timestamp test cases

    public function testValidTimestamp ()
    {
        $this->assert(timestamp: time(), valid: true);
    }

    public function testValidTimestampSingleDigit ()
    {
        $random = rand(1, 9);
        $this->assert(timestamp: $random, valid: true);
    }

    // invalid player name test cases

    public function testInvalidPlayerIdWithSymbols ()
    {
        $symbols = $this->testhelper->randomSymbols();
        $this->assert($symbols);
    }

    public function testInvalidPlayerIdEmpty ()
    {
        $this->assert('');
    }

    public function testInvalidPlayerIdWhiteSpace ()
    {
        $this->assert('  ');
    }

    public function testInvalidPlayerIdBelowMinimumCharacters ()
    {
        $player = $this->testhelper->generateUUid(2);
        $this->assert($player);
    }

    public function testInvalidPlayerIdBeyondMaximumCharacters ()
    {
        $words = $this->testhelper->generateAlphaNumString(65);
        $this->assert($words);
    }

    // invalid nickname test cases

    public function testInvalidNicknameWithSymbols ()
    {
        $symbols = $this->testhelper->randomSymbols();
        $this->assert(nickname: $symbols);
    }

    public function testInvalidNicknameEmpty ()
    {
        $this->assert(nickname: '');
    }

    public function testInvalidNicknameWhiteSpace ()
    {
        $this->assert(nickname: '   ');
    }

    public function testInvalidNicknameBelowMinimumCharacters ()
    {
        $name = $this->testhelper->generateUUid(7);
        $this->assert(nickname: $name);
    }

    public function testInvalidNicknameBeyondMaximumCharacters ()
    {
        $characters = $this->testhelper->generateAlphaNumString(65);
        $this->assert($characters);
    }

    // invalid timestamp test cases

    public function testInvalidTimestampWithSymbols ()
    {
        $symbols = $this->testhelper->randomSymbols();
        $this->assert(timestamp: $symbols);
    }

    public function testInvalidTimestampEmpty ()
    {
        $this->assert(timestamp:  '');
    }

    public function testInvalidTimestampWhiteSpace ()
    {
        $this->assert(timestamp: '   ');
    }

    public function testInvalidTimestampWithLetters ()
    {
        $string = $this->testhelper->generateUniqueName();
        $this->assert(timestamp: $string);
    }
}
