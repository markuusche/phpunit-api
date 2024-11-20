<?php

use PHPUnit\Framework\TestCase;
require_once 'utils/TestHelper.php'; 

class GameListTest extends TestCase 
{
    private TestHelper $testhelper; 

    protected static $tag = [];
    protected static $type = [];
    protected static $games = [];

    protected function setUp(): void
    {
        $this->testhelper = new TestHelper();
    }

    public function responseApi ($noQuery = false, $gi = null, $gt = null, $gn = null)
    {
        $params = [];

        if ($gi !== null) {
            $params[getenv("phpgI")] = $gi;
        }

        if ($gt !== null) {
            $params[getenv("phpGt")] = $gt;
        }

        if ($gn !== null) {
            $params[getenv("phpGn")] = $gn;
        }

        return $this->testhelper->callApi(
            'phpBase',
            'GET',
            getenv("phpLst"), 
            queryParams: $noQuery ? null : (empty($params) ? null : $params)
        );
    }

    public function valid ($noQuery = false, $gi = null, $gt = null, $gn = null)
    {
        $response = $this->responseApi($noQuery, $gi, $gt, $gn);
        $body = $response['body'];
        $this->assertIsArray($body['records']);
        $this->assertEquals(200, actual: $response['status']);
        $this->assertEquals('S-100', actual: $body['rs_code']);
        $this->assertEquals('success', actual: $body['rs_message']);
        $this->assertArrayHasKey('records', $body);
        
        foreach ($body['records'] as $item){
            self::$tag[] = $item['game_id'];
            self::$type[] = $item['game_type'];
            self::$games[] = $item['game_name'];
            $this->assertArrayHasKey('game_id', $item);
            $this->assertArrayHasKey('game_type', $item);
            $this->assertArrayHasKey('game_name', $item);
            $this->assertArrayHasKey('image', $item);
            $this->assertIsInt($item['game_id']);
            $this->assertIsString($item['game_type']);
            if ($item['game_name'] != null) {
                $this->assertIsString($item['game_name']);
            }
            $this->assertMatchesRegularExpression('/^https?:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}(\/.*)?$/', $item['image']);
        }
    }

    public function invalid ($noQuery = false, $gi = null, $gt = null, $gn = null, $noData = false)
    {
        $response = $this->responseApi($noQuery, $gi, $gt, $gn);
        $body = $response['body'];
        $this->assertEquals(200, actual: $response['status']);
        if ($noData){
            $this->assertEquals('S-115', actual: $body['rs_code']);
            $this->assertEquals('no data found', actual: $body['rs_message']);
        }
        else {
            $this->assertEquals('E-104', actual: $body['rs_code']);
            $this->assertEquals('invalid parameter or value', actual: $body['rs_message']);
        }
    }

    // valids
    
    public function testValidGameListNoParams ()
    {
        $this->valid(true);
    }

    public function testValidGameListTag ()
    {
        $tag = $this->testhelper->randomArrayChoice(self::$tag);
        $this->valid(false, $tag);
    }

    public function testValidGameListType ()
    {
        $type = $this->testhelper->randomArrayChoice(self::$type);
        $this->valid(false, null, $type);
    }

    public function testValidGameListName ()
    {
        $games = $this->testhelper->randomArrayChoice(self::$games);
        $this->valid(false, null, null, $games);
    }

    // valid no data

    public function testValidNoDataGameType ()
    {
        $symbols = $this->testhelper->randomSymbols();
        $this->invalid(false, null, $symbols, null, true);
    }

    public function testValidNoDataGameName ()
    {
        $symbols = $this->testhelper->randomSymbols();
        $this->invalid(false, null, null, $symbols, true);
    }

    // invalid tag

    public function testInvalidGameTagWithSymbols ()
    {
        $symbols = $this->testhelper->randomSymbols();
        $this->invalid(false, $symbols);
    }

    public function testInvalidGameTagEmpty ()
    {
        $this->invalid(false, '');
    }

    public function testInvalidGameTagWithWhiteSpace ()
    {
        $this->invalid(false, '    ');
    }

    public function testInvalidGameTagWithLetters ()
    {
        $letters = $this->testhelper->generateUniqueName();
        $this->invalid(false, $letters);
    }

    public function testInvalidGameTagBeyondMaxCharacters ()
    {
        $letters = $this->testhelper->generateLongNumbers(20);
        $this->invalid(false, $letters);
    }

    // invalid type

    public function testInvalidGameTypeEmpty ()
    {
        $this->invalid(false, null, '');
    }

    public function testInvalidGameTypeWithWhiteSpace ()
    {
        $this->invalid(false, null, '    ');
    }

    // invalid name

    public function testInvalidGameNameEmpty ()
    {
        $this->invalid(false, null, null, '');
    }

    public function testInvalidGameNameWithWhiteSpace ()
    {
        $this->invalid(false, null, null, '    ');
    }
}