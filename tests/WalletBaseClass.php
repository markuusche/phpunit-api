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

    public function assert ($player = null, $tr = null, $tra = null, $timestamp = null, $valid = false, $nonexist = false)
    {
        $response = $this->responseApi($player, $tr, $tra, $timestamp);
        $body = $response['body'];
        $this->assertIsArray($body);
        $this->assertEquals(200, actual: $response['status']);
        if ($valid) {
            $this->assertEquals('S-100', actual: $body['rs_code']);
            $this->assertEquals('success', actual: $body['rs_message']);
            $this->assertArrayHasKey('balance', $body);
        }
        else {
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

        return $body;
    }

    // valid request deposit & withdraw

    public function testValidDepositOrWithdraw ()
    {
        if ($this->apiEndpoint === getenv("phpWd")) {
            $this->assert(valid: true, tra: self::$value[0]);
        } else {
            $amount = $this->assert(valid: true);
            self::$value[] = $amount['balance'];
        }
    }

    // valid player name

    public function testValidNonExistentPlayer ()
    {
        $name = $this->faker->word() . "QATest";
        $this->assert($name, nonexist: true);
    }

    public function testValidNonExistentPlayerNumberOnly ()
    {
        $name = $this->testhelper->generateRandomNumber();
        $this->assert(strval(intval($name)), nonexist: true);
    }

    // invalid player name 

    public function testInvalidPlayerIdWhiteSpace ()
    {
        $this->assert('        ');
    }

    public function testInvalidPlayerIdEmpty()
    {
        $this->assert('');
    }

    public function testInvalidPlayerIdWithSymbols ()
    {
        $player = $this->testhelper->randomSymbols();
        $this->assert($player);
    }

    public function testInvalidPlayerIdMinimumCharacters()
    {
        $player = $this->testhelper->generateUUid(2);
        $this->assert($player);
    }

    public function testInvalidPlayerIdBeyondMaximumCharacters ()
    {
        $this->assert($this->testhelper->generateAlphaNumString(65));
    }

    // invalid transaction id

    public function testInvalidTransactionIdWithSymbols () 
    {
        $id = $this->testhelper->randomSymbols();
        $this->assert(tr: $id);
    }

    public function testInvalidTransactionIdEmpty () 
    {
        $this->assert(tr: '');
    }

    public function testInvalidTransactionIdWithWhiteSpaces ()
    {
        $this->assert(tr: '     ');
    }

    public function testInvalidTransactionIdBelowMinimumCharacters ()
    {
        $number = intdiv(time(), 1000);
        $this->assert(tr: $number);
    }

    public function testInvalidTransactionIdBeyondMaximumCharacters ()
    {
        $timeString = $this->testhelper->generateLongNumbers(97);
        $this->assert(tr: $timeString);
    }

    // invalid transaction amount

    public function testInvalidTransactionAmountIdWithSymbols () 
    {
        $id = $this->testhelper->randomSymbols();
        $this->assert(tra: $id);
    }

    public function testInvalidTransactionAmountEmpty () 
    {
        $this->assert(tra: '');
    }

    public function testInvalidTransactionAmountWithWhiteSpaces ()
    {
        $this->assert(tra: '     ');
    }

    public function testInvalidTransactionAmountBelowMinimumAmount ()
    {
        $this->assert(tra: '0');
    }

    public function testInvalidTransactionAmountBeyondMaximumAmount ()
    {
        $timeString = $this->testhelper->generateLongNumbers(35);
        $this->assert(tra: $timeString);
    }

    // invalid timestamp

    public function testInvalidTimestampWithSymbols ()
    {
        $symbols = $this->testhelper->randomSymbols();
        $this->assert(timestamp: $symbols);
    }

    public function testInvalidTimestampEmpty ()
    {
        $this->assert(timestamp: '');
    }

    public function testInvalidTimestampWhiteSpace ()
    {
        $this->assert(timestamp: '    ');
    }

    public function testInvalidTimestampWithLetters ()
    {
        $string = $this->testhelper->generateUniqueName();
        $this->assert(timestamp: $string);
    }
}
