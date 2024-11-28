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

    public function responseApi ($player = null)
    {
        $queryParams = [
            getenv("yummy") => $player ?? getenv('phpId')
        ];

        return $this->testhelper->callApi('phpBase', 'GET', getenv("GB"), queryParams: $queryParams);
    }

    public function assert ($player = null, $valid = false, $nonexist = false) 
    {
        $response = $this->responseApi($player);
        $status = $response['status'];
        $body = $response['body'];
        $this->assertIsArray($body);
        $this->assertEquals(200, actual: $status);
        if ($valid) {
            $this->assertEquals('S-100', $body['rs_code']);
            $this->assertEquals('success', $body['rs_message']);
            $this->assertEquals(getenv('phpId'), $body[getenv("yummy")]);
            $this->assertArrayHasKey('current_balance', $body);
        }
        else {
            if ($nonexist) {
                $this->assertEquals('S-104', $body['rs_code']);
                $this->assertEquals('player not available', $body['rs_message']);
            }
            else {
                $this->assertEquals('E-104', $body['rs_code']);
                $this->assertEquals('invalid parameter or value', $body['rs_message']);
            }
        }
    }

    // valids

    public function testValidBalanceFetch()
    {
        $this->assert(valid: true);
    }

    public function testValidNonExistentUser()
    {
        $name = $this->faker->word() . "QATest";
        $this->assert(player: $name, nonexist: true);
    }

    // invalid player name

    public function testInvalidPlayerId ()
    {
        $name = $this->testhelper->randomSymbols();
        $this->assert(player: $name);
    }

    public function testInvalidPlayerIdWhiteSpace ()
    {
        $this->assert(player: '        ');
    }

    public function testInvalidPlayerIdEmpty()
    {
        $this->assert(player: '');
    }

    public function testInvalidPlayerIdWithSymbols ()
    {
        $this->assert(player: $this->testhelper->randomSymbols());
    }

    public function testInvalidPlayerIdBeyondMaximumCharacters ()
    {
        $this->assert(player: $this->testhelper->generateAlphaNumString(65));
    }
}
