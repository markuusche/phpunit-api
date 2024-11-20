<?php

use Faker\Factory;
use PHPUnit\Framework\TestCase;
require_once 'utils/TestHelper.php'; 

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
            'phpBase',
            'GET',
            getenv("GB"), 
            $data, 
            queryParams: $queryParams);
    }

    public function testValidBalanceFetch ($player = null)
    {
        $data = $player ?? getenv('phpId');
        $response = $this->responseApi($data);
        $status = $response['status'];
        $body = $response['body'];
        $this->assertIsArray($body);
        $this->assertEquals(200, actual: $status);
        $this->assertEquals('S-100', $body['rs_code']);
        $this->assertEquals('success', $body['rs_message']);
        $this->assertEquals(getenv('phpId'), $body[getenv("yummy")]);
        $this->assertArrayHasKey('current_balance', $body);
    }

    public function invalid ($value = null, $nonexist = false)
    {
        $randomName = $value ?? 'invalid@@' . $this->faker->userName() . '!!test_qa$$';
        $response = $this->responseApi($randomName);
        $status = $response['status'];
        $body = $response['body'];
        $this->assertIsArray($body);
        $this->assertEquals(200, actual: $status);
        if ($nonexist)
        {
            $this->assertEquals('S-104', $body['rs_code']);
            $this->assertEquals('player not available', $body['rs_message']);
        }
        else
        {
            $this->assertEquals('E-104', $body['rs_code']);
            $this->assertEquals('invalid parameter or value', $body['rs_message']);
        }
    }

    public function testValidNonExistentUser()
    {
        $name = $this->faker->word() . "QATest";
        $this->invalid($name, true);
    }

    // invalid player name

    public function testInvalidPlayerId ()
    {
        $this->invalid();
    }

    public function testInvalidPlayerIdWhiteSpace ()
    {
        $this->invalid('        ');
    }

    public function testInvalidPlayerIdEmpty()
    {
        $this->invalid('');
    }

    public function testInvalidPlayerIdWithSymbols ()
    {
        $this->invalid($this->testhelper->randomSymbols());
    }

    public function testInvalidPlayerIdBeyondMaximumCharacters ()
    {
        $this->invalid($this->testhelper->generateAlphaNumString(65));
    }
}
