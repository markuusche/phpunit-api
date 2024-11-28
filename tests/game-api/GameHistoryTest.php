<?php

use PHPUnit\Framework\TestCase;
require_once 'utils/TestHelper.php';

class GameHistoryTest extends TestCase 
{
    private TestHelper $testhelper; 
    protected $faker;
    protected static $gi = [];
    protected static $fetch = [];
    protected static $tr = [];
    protected static $round = [];

    protected function setUp(): void
    {
        $this->testhelper = new TestHelper();
        $this->faker = $this->testhelper->getFaker();
    }

    public function responseApi (
        $noQuery = false,
        $player = null,
        $id = null, 
        $limit = null, 
        $fetch = null, 
        $tr = null,
        $round = null) {

        $params = [
            getenv("yummy") => $player,
            getenv("phpgI")  => $id,
            "limit" => $limit ?? "500",
            getenv("study") => $fetch,
            getenv("td") => $tr,
            getenv("grapes") => $round,
        ];

        return $this->testhelper->callApi('phpBase', 'GET', getenv("phpth"), queryParams: $noQuery ? null : (empty($params) ? null : $params));
    }

    public function assert (
    $noQuery = false, 
    $player = null, 
    $id = null, 
    $limit = null,
    $fetch = null,
    $tr = null,
    $round = null,
    $valid = false,
    $noData = false)
    {
        $response = $this->responseApi(
            $noQuery, 
            $player, 
            $id, 
            $limit,
            $fetch,
            $tr,
            $round
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
    
            $keysArray = getenv('expected_keys');
            $expectedKeys = explode(' ', $keysArray);
    
            foreach ($body['records'] as $record) {
                $this->assertIsArray($record);
                self::$gi[] = $record[getenv("phpgI")];
                self::$fetch[] = $record[getenv("study")];
                self::$tr[] = $record[getenv("tr")];
                self::$round[] = $record[getenv("grapes")];
            }
    
            foreach ($expectedKeys as $keys) {
                $this->assertArrayHasKey($keys, $record);
            }
            $this->assertIsArray($record['secondary_info']);
            $this->assertIsArray($record['other_info']);
            $this->assertIsArray($record['remark']);
        } else {
            if ($noData) {
                $this->assertEquals('S-115', actual: $body['rs_code']);
                $this->assertEquals('no data found', actual: $body['rs_message']);
            } else {
                $this->assertEquals('E-104', actual: $body['rs_code']);
                $this->assertEquals('invalid parameter or value', actual: $body['rs_message']);
            }
        }
    
    }

    // valids

    public function testValidGameHistory ()
    {
        $this->assert(valid: true);
    }

    public function testValidPlayer ()
    {
        $this->assert(player: getenv("phpId"), valid: true);
    }

    public function testValidGi ()
    {
        $gi = $this->testhelper->randomArrayChoice(self::$gi);
        $this->assert(id: $gi, valid: true);
    }

    public function testValidFetch ()
    {
        $fetch = $this->testhelper->randomArrayChoice(self::$fetch);
        $this->assert(fetch: $fetch, valid: true);
    }

    public function testValidTr ()
    {
        $tr = $this->testhelper->randomArrayChoice(self::$tr);
        $this->assert(tr: $tr, valid: true);
    }

    public function testValidTrNoDataFound ()
    {
        $len = rand(10, 100);
        $characters = $this->testhelper->generateLongNumbers($len);
        $numbers = $this->testhelper->generateLongNumbers($len);
        $values = [$characters, $numbers];
        $random = array_rand($values);
        $input = $values[$random];
        $this->assert(tr: $input, noData: true);
    }

    public function testValidRound ()
    {
        $round = $this->testhelper->randomArrayChoice(self::$round);
        $this->assert(round: $round, valid: true);
    }

    public function testValidRoundNoDataFound ()
    {
        $len = rand(10, 100);
        $characters = $this->testhelper->generateRandomLetters($len);
        $numbers = $this->testhelper->generateLongNumbers($len);
        $values = [$characters, $numbers];
        $random = array_rand($values);
        $input = $values[$random];
        $this->assert(round: $input, noData: true);
    }

    // invalids

    // player

    public function testInvalidPlayerEmpty ()
    {
        $this->assert(player: '');
    }
    
    public function testInvalidPlayerWithWhiteSpaces ()
    {
        $this->assert(player: '     ');
    }

    // limit

    public function testInvalidLimitEmpty ()
    {
        $this->assert(limit: '');
    }
    
    public function testInvalidLimitWithWhiteSpaces ()
    {
        $this->assert(limit: '     ');
    }

    public function testInvalidLimitWithSymbols ()
    {
        $symbols = $this->testhelper->randomSymbols();
        $this->assert(limit: $symbols);
    }

    public function testInvalidLimitBeyondMaximumCharacters ()
    {
        $numbers = $this->testhelper->generateLongNumbers(5);
        $this->assert(limit: $numbers);
    }

    // gi

    public function testInvalidGiEmpty ()
    {
        $this->assert(id: '');
    }

    public function testInvalidGiWithWhiteSpaces ()
    {
        $this->assert(id: '   ');
    }

    public function testInvalidGiWithSymbols ()
    {
        $symbols = $this->testhelper->randomSymbols();
        $this->assert(id: $symbols);
    }

    public function testInvalidGiBeyondMaximumCharacters ()
    {
        $numbers = $this->testhelper->generateLongNumbers(20);
        $this->assert(id: $numbers);
    }

    // fetch

    public function testInvalidFetchEmpty ()
    {
        $this->assert(fetch: '');
    }

    public function testInvalidFetchWithWhiteSpaces ()
    {
        $this->assert(fetch: '     ');
    }

    public function testInvalidFetchWithSymbols ()
    {
        $symbols = $this->testhelper->randomSymbols();
        $this->assert(fetch: $symbols);
    }

    public function testInvalidFetchBeyondMaximumCharacters ()
    {
        $numbers = $this->testhelper->generateLongNumbers(20);
        $this->assert(fetch: $numbers);
    }

    // tr

    public function testInvalidTrEmpty ()
    {
        $this->assert(tr: '');
    }

    public function testInvalidTrWithWhiteSpaces ()
    {
        $this->assert(tr: '    ');
    }

    public function testInvalidTrWithSymbols ()
    {
        $symbols = $this->testhelper->randomSymbols();
        $this->assert(tr: $symbols);
    }

    // round

    public function testInvalidRoundEmpty ()
    {
        $this->assert(round: '');
    }

    public function testInvalidRoundWithWhiteSpaces ()
    {
        $this->assert(round: '    ');
    }
}
