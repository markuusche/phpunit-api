<?php

use PHPUnit\Framework\TestCase;
require_once 'utils/TestHelper.php';

class GameTransferTest extends TestCase 
{
    private TestHelper $testhelper; 
    protected $faker;

    private static $tr = [];
    private static $fetch = [];

    protected function setUp(): void
    {
        $this->testhelper = new TestHelper();
        $this->faker = $this->testhelper->getFaker();
    }

    public function responseApi (
        $noQuery = false,
        $player = null,
        $limit = null, 
        $fetch = null, 
        $tr = null) {

        $params = [
            getenv("yummy") => $player,
            "limit" => $limit ?? "100",
            getenv("study") => $fetch,
            getenv("td") => $tr,
        ];

        return $this->testhelper->callApi('phpBase', 'GET', getenv("tth"), queryParams: $noQuery ? null : (empty($params) ? null : $params));
    }

    public function assert (
    $noQuery = false, 
    $player = null, 
    $limit = null,
    $fetch = null,
    $tr = null,
    $valid = false,
    $noData = false)
    {
        $response = $this->responseApi(
            $noQuery, 
            $player, 
            $limit,
            $fetch,
            $tr,
        );
        $body = $response['body'];
        $this->assertIsArray($body);
        $this->assertEquals(200, actual: $response['status']);

        if ($valid) {
            $this->assertEquals('S-100', actual: $body['rs_code']);
            $this->assertEquals('success', actual: $body['rs_message']);
            $this->assertArrayHasKey('last_' . getenv("study"), $body);
            $this->assertIsArray($body['records']);
            $this->assertArrayHasKey('records', $body);
    
            $keysArray = getenv('tthKeys');
            $expectedKeys = explode(' ', $keysArray);
    
            foreach ($body['records'] as $record) {
                $this->assertIsArray($record);
                self::$tr[] = $record[getenv("tr")];
                self::$fetch[] = $record[getenv("study")];
            }
    
            foreach ($expectedKeys as $keys) {
                $this->assertArrayHasKey($keys, $record);
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

    public function testValidGameTransfer () {$this->assert(valid: true);}

    public function testValidPlayer ()
    {
        $this->assert(player: getenv("phpId"), valid: true);
    }

    public function testValidTr ()
    {
        $tr = $this->testhelper->randomArrayChoice(self::$tr);
        $this->assert(tr: $tr, valid: true);
    }

    public function testValidFetch ()
    {
        $fetch = $this->testhelper->randomArrayChoice(self::$fetch);
        $this->assert(fetch: $fetch, valid: true);
    }

    public function testValidPlayerWithSymbols ()
    {
        $player = $this->testhelper->randomSymbols();
        $this->assert(player: $player, noData: true);
    }

    // invalids

    // player

    public function testInvalidPlayerEmpty ()
    {
        $this->assert(player: '');
    }
    
    public function testInvalidPlayerWithWhiteSpaces ()
    {
        $this->assert(player: '   ');
    }

    // limit

    public function testInvalidLimitWithSymbols ()
    {
        $this->assert(limit: $this->testhelper->randomSymbols());
    }
    
    public function testInvalidLimitEmpty ()
    {
        $this->assert(limit: '');
    }

    public function testInvalidLimitWithWhiteSpaces ()
    {
        $this->assert(limit: '    ');
    }

    public function testInvalidLimitWithLetters ()
    {
        $letters = $this->testhelper->generateUniqueName();
        $this->assert(limit: $letters);
    }

    public function testInvalidLimitWithAlphaNumCharacters ()
    {
        $alphaNumChars = $this->testhelper->generateAlphaNumString(10);
        $this->assert(limit: $alphaNumChars);
    }
    
    public function testInvalidLimitBeyondMaximumCharacters ()
    {
        $numbers = $this->testhelper->generateLongNumbers(5);
        $this->assert(limit: $numbers);
    }

}
