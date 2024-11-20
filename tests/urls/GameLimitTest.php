<?php

use PHPUnit\Framework\TestCase;
require_once 'utils/TestHelper.php'; 

class GameLimitTest extends TestCase 
{
    private TestHelper $testhelper; 
    protected static $glimit = [];
    
    protected function setUp(): void
    {
        $this->testhelper = new TestHelper();
    }

    public function responseApi ($noQuery = false, $limit = null)
    {
        $params = [];
        if ($limit !== null) {
            $params[getenv("abc")] = $limit;
        }

        return $this->testhelper->callApi(
            'phpBase',
            'GET',
            getenv("phpBl"), 
            queryParams: $noQuery ? null : (empty($params) ? null : $params));
    }

    public function valid ($noQuery = false, $limit = null)
    {
        $response = $this->responseApi($noQuery, $limit);
        $body = $response['body'];
        $this->assertIsArray($body['records']);
        $this->assertEquals(200, actual: $response['status']);
        $this->assertEquals('S-100', actual: $body['rs_code']);
        $this->assertEquals('success', actual: $body['rs_message']);
        $this->assertArrayHasKey('records', $body);

        foreach ($body['records'] as $item) {
            if ($noQuery) {
                self::$glimit[] = $item['id'];
            }
            $this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('min_limit', $item);
            $this->assertArrayHasKey('max_limit', $item);
            $this->assertNotEquals('0.00', $item['min_limit']);
            $this->assertNotEquals('0.00', $item['max_limit']);
            $this->assertIsInt($item['id']);
            $this->assertIsString($item['min_limit']);
            $this->assertIsString($item['max_limit']);
        }
    }

    public function invalid ($noQuery, $limit, $nonExistent = false)
    {
        $response = $this->responseApi($noQuery, $limit);
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

    // valid

    public function testValidGameLimitNoParams ()
    {
        $this->valid(true);
    }

    public function testValidGameLimit ()
    {
        $limit = $this->testhelper->randomArrayChoice(self::$glimit);
        $this->valid(false, $limit);
    }

    public function testValidNonExistentLimit ()
    {
        $number = $this->testhelper->generateLongNumbers(10);
        $this->invalid(false, intval($number), true);
    }

   // invalids

    public function testInvalidLimitWithSymbols ()
    {
        $this->invalid(false, $this->testhelper->randomSymbols());
    }
    
    public function testInvalidLimitEmpty ()
    {
        $this->invalid(false, '');
    }

    public function testInvalidLimitWithWhiteSpaces ()
    {
        $this->invalid(false, '     ');
    }

    public function testInvalidLimitWithLetters ()
    {
        $string = $this->testhelper->generateUniqueName();
        $this->invalid(false, $string);
    }

    public function testInvalidLimitWithAlphaNumCharacters ()
    {
        $string = $this->testhelper->generateAlphaNumString(10);
        $this->invalid(false, $string);
    }
    
    public function testInvalidLimitBeyondMaximumCharacters ()
    {
        $string = $this->testhelper->generateLongNumbers(20);
        $this->invalid(false, $string);
    }
}