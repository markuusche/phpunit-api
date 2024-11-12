<?php

use Faker\Factory;
use PHPUnit\Framework\TestCase;
require_once 'utils/TestHelper.php';
require_once 'utils/Hash.php';

class WalletBaseClass extends TestCase
{
    private TestHelper $testhelper; 
    private $faker;
    private $generate;
    private $apiEndpoint;

    protected static $value = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->testhelper = new TestHelper();
        $this->faker = $this->testhelper->getFaker();
        $this->generate = new Hash();
    }

    public function setApiEndpoint($apiEndpoint)
    {
        $this->apiEndpoint = $apiEndpoint;
    }

    public function responseApi ($player = null, $tr = null, $tra = null, $timestamp = null)
    {
        $data = [
            getenv("yummy") => $player ?? getenv('phpId'),
            getenv("tr") => $tr ?? uniqid(),
            getenv("tra") => $tra ?? $this->testhelper->generateLongNumbers(34),
            "timestamp" => $timestamp ?? time() 
        ];

        $signature = $this->generate->signature($data);
        $data["signature"] = $signature;

        return $this->testhelper->callApi(
            'POST',
            $this->apiEndpoint,
            $data,
            queryParams: []);
    }

    public function valid ($player = null, $tr = null, $tra = null, $timestamp = null)
    {
        $response = $this->responseApi($player, $tr, $tra, $timestamp);
        $body = $response['body'];
        $this->assertEquals(200, actual: $response['status']);
        $this->assertEquals('S-100', actual: $body['rs_code']);
        $this->assertEquals('success', actual: $body['rs_message']);
        $this->assertArrayHasKey('balance', $body);
        return $body['balance'];
    }

    public function invalid ($player = null, $tr = null, $tra = null, $timestamp = null, $nonexist = false)
    {
        $response = $this->responseApi($player, $tr, $tra, $timestamp);
        $status = $response['status'];
        $body = $response['body'];
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

    // valid request deposit

    public function testValidDepositOrWithdraw ()
    {
        if ($this->apiEndpoint === getenv("phpWd")){
            $this->valid(null, null, self::$value[0]);
        } else {
            $amount = $this->valid();
            self::$value[] = $amount;
        }
    }

    // valid player name

    public function testValidNonExistentPlayer ()
    {
        $name = $this->faker->word() . "QATest";
        $this->invalid($name, null, null, null, true);
    }

    public function testValidNonExistentPlayerNumberOnly ()
    {
        $name = $this->testhelper->generateRandomNumber();
        $this->invalid(strval(intval($name)), null, null, null, true);
    }

    // invalid player name 

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
        $player = $this->testhelper->randomSymbols();
        $this->invalid($player);
    }

    public function testInvalidPlayerIdMinimumCharacters()
    {
        $player = $this->testhelper->generateUUid(2);
        $this->invalid($player);
    }

    public function testInvalidPlayerIdBeyondMaximumCharacters ()
    {
        $this->invalid($this->testhelper->generateString(65));
    }

    // invalid transaction id

    public function testInvalidTransactionIdWithSymbols () 
    {
        $id = $this->testhelper->randomSymbols();
        $this->invalid(null, $id);
    }

    public function testInvalidTransactionIdEmpty () 
    {
        $this->invalid(null, '');
    }

    public function testInvalidTransactionIdWithWhiteSpaces ()
    {
        $this->invalid(null, '     ');
    }

    public function testInvalidTransactionIdBelowMinimumCharacters ()
    {
        $number = intdiv(time(), 1000);
        $this->invalid(null, $number);
    }

    public function testInvalidTransactionIdBeyondMaximumCharacters ()
    {
        $timeString = $this->testhelper->generateLongNumbers(97);
        $this->invalid(null, $timeString);
    }

    // invalid transaction amount

    public function testInvalidTransactionAmountIdWithSymbols () 
    {
        $id = $this->testhelper->randomSymbols();
        $this->invalid(null, null, $id);
    }

    public function testInvalidTransactionAmountEmpty () 
    {
        $this->invalid(null, null, '');
    }

    public function testInvalidTransactionAmountWithWhiteSpaces ()
    {
        $this->invalid(null, null, '     ');
    }

    public function testInvalidTransactionAmountBelowMinimumAmount ()
    {
        $this->invalid(null, null, '0');
    }

    public function testInvalidTransactionAmountBeyondMaximumAmount ()
    {
        $timeString = $this->testhelper->generateLongNumbers(35);
        $this->invalid(null, null, $timeString);
    }

    // invalid timestamp

    public function testInvalidTimestampWithSymbols ()
    {
        $symbols = $this->testhelper->randomSymbols();
        $this->invalid(null, null,  null, $symbols);
    }

    public function testInvalidTimestampEmpty ()
    {
        $this->invalid(null, null, null,  '');
    }

    public function testInvalidTimestampWhiteSpace ()
    {
        $this->invalid(null, null, null,  '    ');
    }

    public function testInvalidTimestampWithLetters ()
    {
        $string = $this->testhelper->generateUniqueName();
        $this->invalid(null, null, null, $string);
    }
}
