<?php

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
        $id = uniqid();
        if ($player === null && $tr === null && $tra === null && $timestamp === null){
            if ($this->apiEndpoint === getenv("phpDp")){
                $GLOBALS['depositTransaction'] = $id;
            }
        }
        else if ($player === null && $tr === null && $tra === self::$value[0] && $timestamp === null) {
            if ($this->apiEndpoint === getenv("phpWd")) {
                $GLOBALS['withdrawTransaction'] = $id;
            }
        }

        $data = [
            getenv("yummy") => $player ?? getenv('phpId'),
            getenv("tr") => $tr ?? $id,
            getenv("tra") => $tra ?? $this->testhelper->generateRandomNumber(),
            "timestamp" => $timestamp ?? time() 
        ];

        $signature = $this->generate->signature($data);
        $data["signature"] = $signature;

        return $this->testhelper->callApi('phpBase', 'POST', $this->apiEndpoint, $data);
    }

    public function valid ($player = null, $tr = null, $tra = null, $timestamp = null)
    {
        $response = $this->responseApi($player, $tr, $tra, $timestamp);
        $body = $response['body'];
        $this->assertIsArray($body);
        $this->assertEquals(200, actual: $response['status']);
        $this->assertEquals('S-100', actual: $body['rs_code']);
        $this->assertEquals('success', actual: $body['rs_message']);
        $this->assertArrayHasKey('balance', $body);
        return $body;
    }

    public function invalid ($player = null, $tr = null, $tra = null, $timestamp = null, $nonexist = false)
    {
        $response = $this->responseApi($player, $tr, $tra, $timestamp);
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

    // valid request deposit

    public function testValidDepositOrWithdraw ()
    {
        if ($this->apiEndpoint === getenv("phpWd")) {
            $this->valid(tra: self::$value[0]);
        } else {
            $amount = $this->valid();
            self::$value[] = $amount['balance'];
        }
    }

    // valid player name

    public function testValidNonExistentPlayer ()
    {
        $name = $this->faker->word() . "QATest";
        $this->invalid($name, nonexist: true);
    }

    public function testValidNonExistentPlayerNumberOnly ()
    {
        $name = $this->testhelper->generateRandomNumber();
        $this->invalid(strval(intval($name)), nonexist: true);
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
        $this->invalid($this->testhelper->generateAlphaNumString(65));
    }

    // invalid transaction id

    public function testInvalidTransactionIdWithSymbols () 
    {
        $id = $this->testhelper->randomSymbols();
        $this->invalid(tr: $id);
    }

    public function testInvalidTransactionIdEmpty () 
    {
        $this->invalid(tr: '');
    }

    public function testInvalidTransactionIdWithWhiteSpaces ()
    {
        $this->invalid(tr: '     ');
    }

    public function testInvalidTransactionIdBelowMinimumCharacters ()
    {
        $number = intdiv(time(), 1000);
        $this->invalid(tr: $number);
    }

    public function testInvalidTransactionIdBeyondMaximumCharacters ()
    {
        $timeString = $this->testhelper->generateLongNumbers(97);
        $this->invalid(tr: $timeString);
    }

    // invalid transaction amount

    public function testInvalidTransactionAmountIdWithSymbols () 
    {
        $id = $this->testhelper->randomSymbols();
        $this->invalid(tra: $id);
    }

    public function testInvalidTransactionAmountEmpty () 
    {
        $this->invalid(tra: '');
    }

    public function testInvalidTransactionAmountWithWhiteSpaces ()
    {
        $this->invalid(tra: '     ');
    }

    public function testInvalidTransactionAmountBelowMinimumAmount ()
    {
        $this->invalid(tra: '0');
    }

    public function testInvalidTransactionAmountBeyondMaximumAmount ()
    {
        $timeString = $this->testhelper->generateLongNumbers(35);
        $this->invalid(tra: $timeString);
    }

    // invalid timestamp

    public function testInvalidTimestampWithSymbols ()
    {
        $symbols = $this->testhelper->randomSymbols();
        $this->invalid(timestamp: $symbols);
    }

    public function testInvalidTimestampEmpty ()
    {
        $this->invalid(timestamp: '');
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
