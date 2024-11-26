<?php

use PHPUnit\Framework\TestCase;
require_once 'utils/TestHelper.php';

class GamePromoTest extends TestCase 
{
    private TestHelper $testhelper; 
    protected $faker;

    private static $tr = [];
    private static $created = [];
    private static $updated = [];

    protected function setUp(): void
    {
        $this->testhelper = new TestHelper();
        $this->faker = $this->testhelper->getFaker();
    }

    public function responseApi (
        $noQuery = false,
        $player = null,
        $limit = null, 
        $tr = null,
        $claim = null,
        $claimAt = null,
        $promotAt = null) {

        $params = [
            getenv("yummy") => $player,
            "limit" => $limit ?? "20",
            getenv("td") => $tr,
            getenv("sc") => $claim,
            getenv("cA") => $claimAt,
            getenv("pA") => $promotAt
        ];

        return $this->testhelper->callApi('phpBase', 'GET', getenv("phptpH"), queryParams: $noQuery ? null : (empty($params) ? null : $params));
    }

    public function valid (
        $noQuery = false,
        $player = null,
        $limit = null, 
        $tr = null,
        $claim = null,
        $claimAt = null,
        $promotAt = null)
    {
        $response = $this->responseApi(
            $noQuery, 
            $player, 
            $limit, 
            $tr,
            $claim,
            $claimAt,
            $promotAt
        );
        $body = $response['body'];
        $this->assertIsArray($body); 
        $this->assertEquals(200, actual: $response['status']);
        $this->assertEquals('S-100', actual: $body['rs_code']);
        $this->assertEquals('success', actual: $body['rs_message']);
        $this->assertArrayHasKey('records', $body);

        $promoArray = getenv('prh');
        $expectedKeys = explode(' ', $promoArray);

        foreach ($body['records'] as $record) {
            $this->assertIsArray($record);
            self::$tr[] = $record[getenv("tr")];
            self::$created[] = $record[getenv("clA")];
            self::$updated[] = $record[getenv("upt")];
        }
        
        foreach ($expectedKeys as $keys) {
            $this->assertArrayHasKey($keys, $record);
        }
    }

    public function invalid (
        $noQuery = false,
        $player = null,
        $limit = null, 
        $tr = null,
        $claim = null,
        $claimAt = null,
        $promoAt = null,
        $noData = false)
    {
        $response = $this->responseApi(
            $noQuery, 
            $player, 
            $limit, 
            $tr,
            $claim,
            $claimAt,
            $promoAt
        );
        $body = $response['body'];
        $this->assertIsArray($body); 
        $this->assertEquals(200, actual: $response['status']);
        if ($noData) {
            try {
                $this->assertEquals('S-115', actual: $body['rs_code']);
                $this->assertEquals('no data found', actual: $body['rs_message']);
            } catch (Exception) {
                $this->assertEquals('S-100', actual: $body['rs_code']);
                $this->assertEquals('success', actual: $body['rs_message']);

                foreach ($body['records'] as $record) {
                    $this->assertEquals('', $record[getenv("cA")]);
                }
            }
        } else {
            $this->assertEquals('E-104', actual: $body['rs_code']);
            $this->assertEquals('invalid parameter or value', actual: $body['rs_message']);
        }
    }

    // valids

    public function testValidPromo ()
    {
        $this->valid();
    }

    public function testValidTr ()
    {
        $tr = $this->testhelper->randomArrayChoice(self::$tr);
        $this->valid(tr: $tr);
    }

    public function testValidTrNoData ()
    {
        $len = rand(10, 100);
        $characters = $this->testhelper->generateRandomLetters($len);
        $numbers = $this->testhelper->generateLongNumbers($len);
        $values = [$characters, $numbers];
        $random = array_rand($values);
        $input = $values[$random];
        $this->invalid(tr: $input, noData: true);
    }

    public function testValidPlayerNoData ()
    {
        $len = rand(10, 100);
        $characters = $this->testhelper->generateRandomLetters($len);
        $numbers = $this->testhelper->generateLongNumbers($len);
        $values = [$characters, $numbers];
        $random = array_rand($values);
        $input = $values[$random];
        $this->invalid(player: $input, noData: true);
    }

    public function testValidCreatedAt ()
    {
        $claimAt = $this->testhelper->randomArrayChoice(self::$created);
        $date = explode(' ', $claimAt)[0];
        $this->valid(claimAt: $date);
    }

    public function testValidUpdatedAt ()
    {
        $updated = $this->testhelper->randomArrayChoice(self::$updated);
        $date = explode(' ', $updated)[0];
        $this->valid(claimAt: $date);
    }

    public function testValidClaimNoData ()
    {
        $this->invalid(claim: 'false', noData: true);
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

    // tr

    public function testInvalidTrEmpty ()
    {
        $this->invalid(tr: '');
    }
    
    public function testInvalidTrWithWhiteSpaces ()
    {
        $this->invalid(tr: '     ');
    }

    // claim at

    public function testInvalidClaimAtEmpty ()
    {
        $this->invalid(claimAt: '');
    }
    
    public function testInvalidClaimAtWithWhiteSpaces ()
    {
        $this->invalid(claimAt: '     ');
    }

    public function testInvalidClaimAtWithSymbols ()
    {
        $symbols = $this->testhelper->randomSymbols();
        $this->invalid(claimAt: $symbols);
    }

    public function testInvalidClaimAtWithNumbers ()
    {
        $numbers = $this->testhelper->generateLongNumbers(20);
        $this->invalid(claimAt: $numbers);
    }

    // promo at

    public function testInvalidPromoAtEmpty ()
    {
        $this->invalid(promoAt: '');
    }
    
    public function testInvalidPromomAtWithWhiteSpaces ()
    {
        $this->invalid(promoAt: '     ');
    }

    public function testInvalidPromoAtWithSymbols ()
    {
        $symbols = $this->testhelper->randomSymbols();
        $this->invalid(promoAt: $symbols);
    }

    public function testInvalidPromoAtWithNumbers ()
    {
        $numbers = $this->testhelper->generateLongNumbers(20);
        $this->invalid(promoAt: $numbers);
    }

    // claim

    public function testInvalidClaimEmpty ()
    {
        $this->invalid(claim: '');
    }
    
    public function testInvalidClaimWithWhiteSpaces ()
    {
        $this->invalid(claim: '     ');
    }

    public function testInvalidClaimWithSymbols ()
    {
        $symbols = $this->testhelper->randomSymbols();
        $this->invalid(claim: $symbols);
    }

    public function testInvalidClaimWithNumbers ()
    {
        $numbers = $this->testhelper->generateLongNumbers(20);
        $this->invalid(claim: $numbers);
    }
}
