<?php

use PHPUnit\Framework\TestCase;
require_once 'utils/TestHelper.php'; 
require_once 'tests/DataGlobals.php'; 

class TransferCheckTest extends TestCase 
{
    private TestHelper $testhelper; 
    
    protected function setUp(): void
    {
        $this->testhelper = new TestHelper();
    }

    public function responseApi ($transaction)
    {
        $data = [
            getenv("td") => $transaction
        ];

        return $this->testhelper->callApi('phpBase', 'POST', getenv("TC"), $data);
    }

    public function assert ($transaction, $valid = false, $nonExistent = false)
    {
        $response = $this->responseApi($transaction);
        $body = $response['body'];
        $this->assertIsArray($body);
        $this->assertEquals(200, actual: $response['status']);
        if ($valid) {
            $this->assertEquals('S-100', actual: $body['rs_code']);
            $this->assertEquals('success', actual: $body['rs_message']);
        } else {
            if ($nonExistent){
                $this->assertEquals('S-119', actual: $body['rs_code']);
                $this->assertEquals('transaction is not existed', actual: $body['rs_message']);
            }
            else {
                $this->assertEquals('E-104', actual: $body['rs_code']);
                $this->assertEquals('invalid parameter or value', actual: $body['rs_message']);
            }
        }
    }

    public function testValidDepositTransaction ()
    {
        $this->assert($GLOBALS['depositTransaction'], valid: true);
    }
    public function testValidWithdrawTransaction ()
    {
        $this->assert($GLOBALS['withdrawTransaction'], valid: true);
    }

    public function testValidNonExistentTransaction ()
    {
        $this->assert($this->testhelper->generateAlphaNumString(96), nonExistent: true);
    }

    public function testInvalidTransactionWithWhiteSpace ()
    {
        $this->assert('        ');
    }

    public function testInvalidTransactionWithEmpty ()
    {
        $this->assert('');
    }

    public function testInvalidTransactionWithSymbols ()
    {
        $symbols = $this->testhelper->randomSymbols();
        $this->assert($symbols);
    }

    public function testInvalidTransactionBelowMinimumCharacters ()
    {
        $data = $this->testhelper->generateUUid(7);
        $this->assert($data);
    }

    public function testInvalidTransactionBeyondMinimumCharacters ()
    {
        $this->assert($this->testhelper->generateAlphaNumString(97));
    }
}
