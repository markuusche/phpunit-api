<?php

use Faker\Factory;
use PHPUnit\Framework\TestCase;
require_once 'utils/TestHelper.php';
require_once 'utils/Hash.php';

class DepositTest extends TestCase 
{
    private TestHelper $testhelper; 
    private $faker;
    private $generate;

    protected function setUp(): void
    {
        $this->testhelper = new TestHelper();
        $this->faker = $this->testhelper->getFaker();
        $this->generate = new Hash();
    }

    public function responseApi ()
    {
        $data = [
            getenv("yummy") => getenv('phpId'),
            getenv("tr") => uniqid(),  
            getenv("tra") => $this->testhelper->generateRandomNumber(),
            "timestamp" => time() 
        ];

        $signature = $this->generate->signature($data);
        $data["signature"] = $signature;

        return $this->testhelper->callApi(
            'POST',
            getenv("phpDp"), 
            $data, 
            queryParams: []);
    }

    public function testValidDeposit ()
    {
       $response = $this->responseApi();
       $body = $response['body'];
       $this->assertEquals(200, actual: $response['status']);
       $this->assertEquals('S-100', actual: $body['rs_code']);
       $this->assertEquals('success', actual: $body['rs_message']);
       $this->assertArrayHasKey('balance', $body);
    }
}
