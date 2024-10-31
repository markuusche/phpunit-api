<?php

use Faker\Factory;
use PHPUnit\Framework\TestCase;
require_once 'tests/helpers/TestHelper.php'; 

class GetBalanceTest extends TestCase 
{
    private TestHelper $testhelper; 
    protected $faker;
    
    protected function setUp(): void
    {
        $this->testhelper = new TestHelper();
        $this->faker = $this->testhelper->getFaker();
    }

    public function responseApi ($playerId=[], $data=[])
    {
        $queryParams = [
            getenv("yummy") => $playerId
        ];

        return $this->testhelper->callApi(
            'GET',
            getenv("GB"), 
            $data, 
            queryParams: $queryParams);
    }

    public function invalids ($value=null)
    {
        $randomName = $value ?? 'invalid@@' . $this->faker->userName() . '!!test_qa$$';
        $response = $this->responseApi($randomName);
        $status = $response['status'];
        $body = $response['body'];
        $this->assertEquals(200, actual: $status);
        $this->assertEquals('E-104', $body['rs_code']);
        $this->assertEquals('invalid parameter or value', $body['rs_message']);
    }

    public function valids($player = null)
    {
        $data = $player ?? getenv('phpId');
        $response = $this->responseApi($data);
        $status = $response['status'];
        $body = $response['body'];
        $this->assertEquals(200, actual: $status);
        $this->assertEquals('S-100', $body['rs_code']);
        $this->assertEquals('success', $body['rs_message']);
        $this->assertEquals(getenv('phpId'), $body[getenv("yummy")]);
        $this->assertArrayHasKey('current_balance', $body);
    }

    public function testValidUser()
    {
        $this->valids();
    }

    public function testValidNonExistentUser()
    {
        $response = $this->responseApi($this->faker->word() . 'QATest123');
        $status = $response['status'];
        $body = $response['body'];
        $this->assertEquals(200, actual: $status);
        $this->assertEquals('S-104', $body['rs_code']);
        $this->assertEquals('player not available', $body['rs_message']);
    }

    public function testInvalidPlayerId ()
    {
        $this->invalids();
    }

    public function testInvalidPlayerIdWhiteSpace ()
    {
        $this->invalids('        ');
    }

    public function testInvalidPlayerIdEmpty()
    {
        $this->invalids('');
    }

    public function testInvalidPlayerIdWithSymbols ()
    {
        $this->invalids($this->testhelper->randomSymbols());
    }

    public function testInvalidPlayerIdBeyondMaximumCharacters ()
    {
        $this->invalids($this->testhelper->generateString(100));
    }
}
