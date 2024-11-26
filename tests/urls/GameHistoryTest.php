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

    public function valid (
    $noQuery = false, 
    $player = null, 
    $id = null, 
    $limit = null,
    $fetch = null,
    $tr = null,
    $round = null
    )
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
        $this->assertEquals('S-100', actual: $body['rs_code']);
        $this->assertEquals('success', actual: $body['rs_message']);
        $this->assertArrayHasKey('last_' . getenv("study"), $body);
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
    }

    public function invalid (
        $noQuery = false, 
        $player = null, 
        $id = null, 
        $limit = null,
        $fetch = null,
        $tr = null,
        $round = null,
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
            if ($noData) {
                $this->assertEquals('S-115', actual: $body['rs_code']);
                $this->assertEquals('no data found', actual: $body['rs_message']);
            } else {
                $this->assertEquals('E-104', actual: $body['rs_code']);
                $this->assertEquals('invalid parameter or value', actual: $body['rs_message']);
            }
        }

    // valids

    public function testValidGameHistory ()
    {
        $this->valid();
    }

    public function testValidPlayer ()
    {
        $this->valid(player: getenv("phpId"));
    }

    public function testValidGi ()
    {
        $gi = $this->testhelper->randomArrayChoice(self::$gi);
        $this->valid(id: $gi);
    }

    public function testValidFetch ()
    {
        $fetch = $this->testhelper->randomArrayChoice(self::$fetch);
        $this->valid(fetch: $fetch);
    }

    public function testValidTr ()
    {
        $tr = $this->testhelper->randomArrayChoice(self::$tr);
        $this->valid(tr: $tr);
    }

    public function testValidTrNoDataFound ()
    {
        $len = rand(10, 100);
        $characters = $this->testhelper->generateLongNumbers($len);
        $numbers = $this->testhelper->generateLongNumbers($len);
        $values = [$characters, $numbers];
        $random = array_rand($values);
        $input = $values[$random];
        $this->invalid(tr: $input, noData: true);
    }

    public function testValidRound ()
    {
        $round = $this->testhelper->randomArrayChoice(self::$round);
        $this->valid(round: $round);
    }

    public function testValidRoundNoDataFound ()
    {
        $len = rand(10, 100);
        $characters = $this->testhelper->generateRandomLetters($len);
        $numbers = $this->testhelper->generateLongNumbers($len);
        $values = [$characters, $numbers];
        $random = array_rand($values);
        $input = $values[$random];
        $this->invalid(round: $input, noData: true);
    }

    // invalids

    // player

    public function testInvalidPlayerEmpty ()
    {
        $this->invalid(player: '');
    }
    
    public function testInvalidPlayerWithWhiteSpaces ()
    {
        $this->invalid(player: '     ');
    }

    // limit

    public function testInvalidLimitEmpty ()
    {
        $this->invalid(limit: '');
    }
    
    public function testInvalidLimitWithWhiteSpaces ()
    {
        $this->invalid(limit: '     ');
    }

    public function testInvalidLimitWithSymbols ()
    {
        $symbols = $this->testhelper->randomSymbols();
        $this->invalid(limit: $symbols);
    }

    public function testInvalidLimitBeyondMaximumCharacters ()
    {
        $numbers = $this->testhelper->generateLongNumbers(5);
        $this->invalid(limit: $numbers);
    }

    // gi

    public function testinvalidGiEmpty ()
    {
        $this->invalid(id: '');
    }

    public function testinvalidGiWithWhiteSpaces ()
    {
        $this->invalid(id: '   ');
    }

    public function testinvalidGiWithSymbols ()
    {
        $symbols = $this->testhelper->randomSymbols();
        $this->invalid(id: $symbols);
    }

    public function testinvalidGiBeyondMaximumCharacters ()
    {
        $numbers = $this->testhelper->generateLongNumbers(20);
        $this->invalid(id: $numbers);
    }

    // fetch

    public function testinvalidFetchEmpty ()
    {
        $this->invalid(fetch: '');
    }

    public function testinvalidFetchWithWhiteSpaces ()
    {
        $this->invalid(fetch: '     ');
    }

    public function testinvalidFetchWithSymbols ()
    {
        $symbols = $this->testhelper->randomSymbols();
        $this->invalid(fetch: $symbols);
    }

    public function testinvalidFetchBeyondMaximumCharacters ()
    {
        $numbers = $this->testhelper->generateLongNumbers(20);
        $this->invalid(fetch: $numbers);
    }

    // tr

    public function testinvalidTrEmpty ()
    {
        $this->invalid(tr: '');
    }

    public function testinvalidTrWithWhiteSpaces ()
    {
        $this->invalid(tr: '    ');
    }

    public function testinvalidTrWithSymbols ()
    {
        $symbols = $this->testhelper->randomSymbols();
        $this->invalid(tr: $symbols);
    }

    // round

    public function testinvalidRoundEmpty ()
    {
        $this->invalid(round: '');
    }

    public function testinvalidRoundWithWhiteSpaces ()
    {
        $this->invalid(round: '    ');
    }
}
