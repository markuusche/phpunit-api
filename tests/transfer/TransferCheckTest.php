<?php

use PHPUnit\Framework\TestCase;
require_once 'utils/TestHelper.php'; 
require_once 'tests/Transactions.php'; 

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

        return $this->testhelper->callApi(
            'phpBase',
            'POST',
            getenv("TC"), 
            $data, 
            queryParams: []);
    }

    public function valid ($transaction)
    {
        $response = $this->responseApi($transaction);
        $body = $response['body'];
        $this->assertIsArray($body);
        $this->assertEquals(200, actual: $response['status']);
        $this->assertEquals('S-100', actual: $body['rs_code']);
        $this->assertEquals('success', actual: $body['rs_message']);
    }

    public function invalid ($transaction, $nonExistent = false)
    {
        $response = $this->responseApi($transaction);
        $body = $response['body'];
        $this->assertIsArray($body);
        $this->assertEquals(200, actual: $response['status']);
        if ($nonExistent){
            $this->assertEquals('S-119', actual: $body['rs_code']);
            $this->assertEquals('transaction is not existed', actual: $body['rs_message']);
        }
        else {
            $this->assertEquals('E-104', actual: $body['rs_code']);
            $this->assertEquals('invalid parameter or value', actual: $body['rs_message']);
        }
    }

    public function testValidDepositTransaction ()
    {
        $this->valid($GLOBALS['depositTransaction']);
    }
    public function testValidWithdrawTransaction ()
    {
        $this->valid($GLOBALS['withdrawTransaction']);
    }

    public function testValidNonExistentTransaction ()
    {
        $this->invalid($this->testhelper->generateAlphaNumString(96), true);
    }

    public function testInvalidTransactionWithWhiteSpace ()
    {
        $this->invalid('        ');
    }

    public function testInvalidTransactionWithEmpty ()
    {
        $this->invalid('');
    }

    public function testInvalidTransactionWithSymbols ()
    {
        $symbols = $this->testhelper->randomSymbols();
        $this->invalid($symbols);
    }

    public function testInvalidTransactionBelowMinimumCharacters ()
    {
        $data = $this->testhelper->generateUUid(7);
        $this->invalid($data);
    }

    public function testInvalidTransactionBeyondMinimumCharacters ()
    {
        $this->invalid($this->testhelper->generateAlphaNumString(97));
    }
}
