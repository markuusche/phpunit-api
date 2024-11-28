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

    public function assert ($noQuery = false, $gi = null, $gt = null, $gn = null, $valid = false, $noData = false)
    {
        $response = $this->responseApi($noQuery, $gi, $gt, $gn);
        $body = $response['body'];
        $this->assertIsArray($body);
        $this->assertEquals(200, actual: $response['status']);

        if ($valid) {
            $this->assertEquals('S-100', actual: $body['rs_code']);
            $this->assertEquals('success', actual: $body['rs_message']);
            $this->assertIsArray($body['records']);
            $this->assertArrayHasKey('records', $body);
            
            foreach ($body['records'] as $item){
                $GLOBALS['gameIds'][] = $item['game_id'];
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
                $this->assertTrue(filter_var($item['image'], FILTER_VALIDATE_URL) !== false);
            }
        }
        else {
            if ($noData){
                $this->assertEquals('S-115', actual: $body['rs_code']);
                $this->assertEquals('no data found', actual: $body['rs_message']);
            }
            else {
                $this->assertEquals('E-104', actual: $body['rs_code']);
                $this->assertEquals('invalid parameter or value', actual: $body['rs_message']);
            }
        }
    }

    // valids
    
    public function testValidGameListNoParams ()
    {
        $this->assert(noQuery: true, valid: true);
    }

    public function testValidGameListTag ()
    {
        $tag = $this->testhelper->randomArrayChoice(self::$tag);
        $this->assert(gi: $tag, valid: true);
    }

    public function testValidGameListType ()
    {
        $type = $this->testhelper->randomArrayChoice(self::$type);
        $this->assert(gt: $type, valid: true);
    }

    public function testValidGameListName ()
    {
        $games = $this->testhelper->randomArrayChoice(self::$games);
        $this->assert(gn: $games, valid: true);
    }

    // valid no data

    public function testValidNoDataGameType ()
    {
        $symbols = $this->testhelper->randomSymbols();
        $this->assert(gt: $symbols, noData: true);
    }

    public function testValidNoDataGameName ()
    {
        $symbols = $this->testhelper->randomSymbols();
        $this->assert(gn: $symbols, noData: true);
    }

    // invalid tag

    public function testInvalidGameTagWithSymbols ()
    {
        $symbols = $this->testhelper->randomSymbols();
        $this->assert(gi: $symbols);
    }

    public function testInvalidGameTagEmpty ()
    {
        $this->assert(gi: '');
    }

    public function testInvalidGameTagWithWhiteSpace ()
    {
        $this->assert(gi: '    ');
    }

    public function testInvalidGameTagWithLetters ()
    {
        $letters = $this->testhelper->generateUniqueName();
        $this->assert(gi: $letters);
    }

    public function testInvalidGameTagBeyondMaxCharacters ()
    {
        $letters = $this->testhelper->generateLongNumbers(20);
        $this->assert(gi: $letters);
    }

    // invalid type

    public function testInvalidGameTypeEmpty ()
    {
        $this->assert(gt: '');
    }

    public function testInvalidGameTypeWithWhiteSpace ()
    {
        $this->assert(gt: '    ');
    }

    // invalid name

    public function testInvalidGameNameEmpty ()
    {
        $this->assert(gn: '');
    }

    public function testInvalidGameNameWithWhiteSpace ()
    {
        $this->assert(gn: '    ');
    }
}