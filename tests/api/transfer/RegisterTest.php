<?php

use Faker\Factory;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
require_once 'tests/helpers/TestHelper.php';

class RegisterTest extends TestCase 
{
    private TestHelper $testhelper; 
    private $faker;

    protected function setUp(): void
    {
        $this->testhelper = new TestHelper();
        $this->faker = $this->testhelper->getFaker();
    }

    public function responseApi ($playerId = null, $nickname = null)
    {
        $data = [
            getenv("yummy") => $playerId ?? getenv("phpId"),
            "nickname" => $nickname ?? 'test_ogqa',
            "timestamp" => time()
        ];

        return $this->testhelper->callApi(
            'POST',
            getenv("RG"), 
            $data, 
            queryParams: []);
    }

    public function invalids ($value=null)
    {
        $randomValue = $value ?? $this->testhelper->randomSymbols();
        $response = $this->responseApi(null, $randomValue);
        $status = $response['status'];
        $body = $response['body'];
        $this->assertEquals(200, actual: $status);
        $this->assertEquals('E-104', $body['rs_code']);
        $this->assertEquals('invalid parameter or value', $body['rs_message']);
    }

    public function valids ($playerId)
    {
        $response = $this->responseApi($playerId);
        $status = $response['status'];
        $body = $response['body'];
        $this->assertEquals(200, $status);
        $this->assertTrue(in_array($body['rs_code'], ['S-100', 'S-121']));
        $this->assertTrue(in_array($body['rs_message'], ['success', 'player already exists']));
    }

    // player name test cases
    public function testValidUser()
    {
        $uuid = Uuid::uuid4()->toString();
        $slicedUuid = substr($uuid, 0, 4);
        $player = 'valid_' . $this->faker->word() . $slicedUuid . '_test_qa';
        $this->valids($player);
    }

    public function testValidPlayerIdMinimumCharacters()
    {
        $uuid = Uuid::uuid4()->toString();
        $player = substr($uuid, 0, 3);
        $this->valids($player);
    }

    public function testValidPlayerMaximumCharacters ()
    {
        $uuid = Uuid::uuid4()->toString();
        $words = $this->testhelper->generateString(60);
        $player = substr($uuid, 0, 4) . $words;
        $this->valids($player);
    }

    public function testInvalidPlayerIdWithSymbols ()
    {
        $this->invalids();
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
            $uuid = Uuid::uuid4()->toString();
            $player = substr($uuid, 0, 2);
            $this->invalids($player);
    }

    public function testInvalidPlayerIdBeyondMaximumCharacters ()
    {
            $uuid = Uuid::uuid4()->toString();
            $words = $this->testhelper->generateString(65);
            $player = substr($uuid, 0, 4) . $words;
            $this->invalids($player);
    }

    // nickname test cases
    public function testInvalidNicknameWithSymbols ()
    {
        $this->invalids();
    }

    public function testInvalidNicknameEmpty ()
    {
        $this->invalids('');
    }

    public function testInvalidNicknameWhiteSpace ()
    {
        $this->invalids('  ');
    }

    public function testInvalidNicknameBelowMinimumCharacters ()
    {
            $uuid = Uuid::uuid4()->toString();
            $player = substr($uuid, 0, 7);
            $this->invalids($player);
    }

    public function testInvalidNicknameBeyondMaximumCharacters ()
    {
            $uuid = Uuid::uuid4()->toString();
            $words = $this->testhelper->generateString(62);
            $player = substr($uuid, 0, 4) . $words;
            $this->invalids($player);
    }
}
