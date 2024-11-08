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

    public function invalids ($player = null, $nickname = null, $timestamp = null)
    {
        $response = $this->responseApi($player, $nickname, $timestamp);
        $status = $response['status'];
        $body = $response['body'];
        $this->assertEquals(200, actual: $status);
        $this->assertEquals('E-104', $body['rs_code']);
        $this->assertEquals('invalid parameter or value', $body['rs_message']);
    }

    public function valids ($player = null, $nickname = null, $timestamp = null)
    {
        $response = $this->responseApi($player, $nickname, $timestamp);
        $status = $response['status'];
        $body = $response['body'];
        $this->assertEquals(200, $status);
        $this->assertTrue(in_array($body['rs_code'], ['S-100', 'S-121']));
        $this->assertTrue(in_array($body['rs_message'], ['success', 'player already exists']));
    }

    // valid player name test cases

    public function testValidPlayerIdRegister()
    {
        $this->valids();
    }

    public function testValidPlayerIdExistence()
    {
        $this->valids(getenv("phpId"));
    }

    public function testValidPlayerIdMinimumCharacters()
    {
        $player = $this->testhelper->generateUUid(3);
        $this->valids($player);
    }

    public function testValidPlayerIdMaximumCharacters ()
    {
        $words = $this->testhelper->generateString(60);
        $player = $this->testhelper->generateUUid() . $words;
        $this->valids($player);
    }

    // valid nickname test cases

    public function testValidNicknameMinimumCharacters ()
    {
        $characters = $this->testhelper->generateUUid(8);
        $this->valids(null, $characters);
    }

    public function testValidNicknameMaximumCharacters ()
    {
        $characters = $this->testhelper->generateString(65);
        $this->valids(null, $characters);
    }

    // valid timestamp test cases

    public function testValidTimestamp ()
    {
        $this->valids(null, null, null);
    }

    public function testValidTimestampSingleDigit ()
    {
        $random = rand(1, 9);
        $this->valids(null, null, $random);
    }

    // invalid player name test cases

    public function testInvalidPlayerIdWithSymbols ()
    {
        $symbols = $this->testhelper->randomSymbols();
        $this->invalids($symbols);
    }

    public function testInvalidPlayerIdEmpty ()
    {
        $this->invalids('');
    }

    public function testInvalidPlayerIdWhiteSpace ()
    {
        $this->invalids('  ');
    }

    public function testInvalidPlayerIdBelowMinimumCharacters ()
    {
        $player = $this->testhelper->generateUUid(2);
        $this->invalids($player);
    }

    public function testInvalidPlayerIdBeyondMaximumCharacters ()
    {
        $words = $this->testhelper->generateString(65);
        $player = $this->testhelper->generateUuid() . $words;
        $this->invalids($player);
    }

    // invalid nickname test cases

    public function testInvalidNicknameWithSymbols ()
    {
        $symbols = $this->testhelper->randomSymbols();
        $this->invalids(null, $symbols);
    }

    public function testInvalidNicknameEmpty ()
    {
        $this->invalids(null, '');
    }

    public function testInvalidNicknameWhiteSpace ()
    {
        $this->invalids(null, '  ');
    }

    public function testInvalidNicknameBelowMinimumCharacters ()
    {
        $name = $this->testhelper->generateUUid(7);
        $this->invalids(null, $name);
    }

    public function testInvalidNicknameBeyondMaximumCharacters ()
    {
        $characters = $this->testhelper->generateString(66);
        $this->invalids($characters);
    }

    // invalid timestamp test cases

    public function testInvalidTimestampWithSymbols ()
    {
        $symbols = $this->testhelper->randomSymbols();
        $this->invalids(null, null,  $symbols);
    }

    public function testInvalidTimestampEmpty ()
    {
        $this->invalids(null, null,  '');
    }

    public function testInvalidTimestampWhiteSpace ()
    {
        $this->invalids(null, null,  '    ');
    }

    public function testInvalidTimestampWithLetters ()
    {
        $string = $this->testhelper->generateUniqueName();
        $this->invalids(null, null,  $string);
    }
}
