<?php

use PHPUnit\Framework\TestCase;
require_once 'utils/TestHelper.php'; 

class GameLimitTest extends TestCase 
{
    private TestHelper $testhelper; 
    
    protected function setUp(): void
    {
        $this->testhelper = new TestHelper();
    }

    public function responseApi ($limit = null)
    {
        $params = [
            getenv("abc") => $limit ?? getenv("limit")
        ];

        return $this->testhelper->callApi(
            'phpBase',
            'GET',
            getenv("phpBl"), 
            queryParams: $params);
    }

    public function testValidLimit ($limit = null)
    {
        $response = $this->responseApi($limit);
        $body = $response['body'];
        $this->assertEquals(200, actual: $response['status']);
        $this->assertEquals('S-100', actual: $body['rs_code']);
        $this->assertEquals('success', actual: $body['rs_message']);
        $this->assertArrayHasKey('records', $body);
        $this->assertIsArray($body['records']);
        $record = $body['records'][0];
        $this->assertEquals(getenv("limit"), $record['id']);
        $this->assertArrayHasKey(getenv("abc"), $record);
        $this->assertArrayHasKey('min_limit', $record);
        $this->assertArrayHasKey('max_limit', $record);
        $this->assertNotEquals('0.00', $record['min_limit']);
        $this->assertNotEquals('0.00', $record['max_limit']);
    }

    public function invalid ($limit, $nonExistent = false)
    {
        $response = $this->responseApi($limit);
        $status = $response['status'];
        $body = $response['body'];
        $this->assertEquals(200, actual: $status);
        if ($nonExistent){
            $this->assertEquals('S-115', $body['rs_code']);
            $this->assertEquals('no data found', $body['rs_message']);
        }
        else {
            $this->assertEquals('E-104', $body['rs_code']);
            $this->assertEquals('invalid parameter or value', $body['rs_message']);
        }
    }

    public function testValidNonExistentLimit ()
    {
        $number = $this->testhelper->generateLongNumbers(10);
        $this->invalid(intval($number), true);
    }

    public function testInvalidLimitWithSymbols ()
    {
        $this->invalid($this->testhelper->randomSymbols());
    }

    public function testInvalidLimitEmpty ()
    {
        $this->invalid('');
    }

    public function testInvalidLimitWithWhiteSpaces ()
    {
        $this->invalid('     ');
    }

    public function testInvalidLimitWithLetters ()
    {
        $string = $this->testhelper->generateUniqueName();
        $this->invalid($string);
    }

    public function testInvalidLimitWithAlphaNumCharacters ()
    {
        $string = $this->testhelper->generateAlphaNumString(10);
        $this->invalid($string);
    }
    
    public function testInvalidLimitBeyondMaximumCharacters ()
    {
        $string = $this->testhelper->generateLongNumbers(20);
        $this->invalid($string);
    }
}