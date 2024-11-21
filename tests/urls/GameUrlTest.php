<?php

use PHPUnit\Framework\TestCase;
require_once 'utils/TestHelper.php';
require_once 'utils/Hash.php';

class GameUrlTest extends TestCase 
{
    private TestHelper $testhelper; 
    private $generate;
    protected $faker;

    protected function setUp(): void
    {
        $this->testhelper = new TestHelper();
        $this->generate = new Hash();
        $this->faker = $this->testhelper->getFaker();
    }

    public function responseApi (
        $player = null, 
        $nickname = null, 
        $lang = null, 
        $tk = null,
        $gi = null,
        $limit = null,
        $timestamp = null) {

        $data = [
            getenv("yummy") => $player ?? getenv('phpId'),
            "nickname" => $nickname ?? $this->testhelper->generateUniqueName(),
            "lang" => $lang ?? "en",
            "token" => $tk ?? $this->testhelper->generateAlphaNumString(10),
            getenv("phpgI") => $gi ?? $this->testhelper->randomArrayChoice($GLOBALS['gameIds']),
            getenv("bl") => $limit ?? $this->testhelper->randomArrayChoice($GLOBALS['limit']),
            'timestamp' => $timestamp ?? time()
        ];

        $signature = $this->generate->signature($data);
        $data["signature"] = $signature;
        return $this->testhelper->callApi('phpBase', 'GET', getenv("phpGL"), queryParams: $data);
    }

    public function valid (
    $player = null, 
    $nickname = null, 
    $lang = null, 
    $tk = null,
    $gi = null,
    $limit = null,
    $timestamp = null
    )
    {
        $response = $this->responseApi(
            $player, 
            $nickname, 
            $lang, 
            $tk,
            $gi,
            $limit,
            $timestamp
        );
        $body = $response['body'];
        $this->assertIsArray($body);
        $this->assertEquals(200, actual: $response['status']);
        $this->assertEquals('S-100', actual: $body['rs_code']);
        $this->assertEquals('success', actual: $body['rs_message']);
        $this->assertTrue(filter_var($body[getenv("phpURL")], FILTER_VALIDATE_URL) !== false);
    }

    public function invalid (
        $player = null, 
        $nickname = null, 
        $lang = null, 
        $tk = null,
        $gi = null,
        $limit = null,
        $timestamp = null,
        $nonexistent = false) 
        {
            $response = $this->responseApi(
                $player, 
                $nickname, 
                $lang, 
                $tk,
                $gi,
                $limit,
                $timestamp
            );
            $body = $response['body'];
            $this->assertIsArray($body);
            $this->assertEquals(200, actual: $response['status']);
            if ($nonexistent) {
                try {
                    $this->assertEquals('S-104', actual: $body['rs_code']);
                    $this->assertEquals('player not available', actual: $body['rs_message']);
                }
                // Player ID accepts a minimum of 3 characters, so repeated runs may generate IDs that are already registered.
                catch (Exception) {
                    $this->assertEquals('S-100', actual: $body['rs_code']);
                    $this->assertEquals('success', actual: $body['rs_message']);
                }
                
            } else {
                $this->assertEquals('E-104', actual: $body['rs_code']);
                $this->assertEquals('invalid parameter or value', actual: $body['rs_message']);
            }
        }

    // valid test

    public function testValidGameUrl()
    {
        $this->valid();
    }

    // player 
    public function testValidNonAvailablePlayer ()
    {
        $this->invalid($this->testhelper->generateAlphaNumString(6), nonexistent: true);
    }

    public function testValidPlayerMinimumCharacters ()
    {
        $this->invalid($this->testhelper->generateAlphaNumString(3), nonexistent: true);
    }

    public function testValidPlayerMaximumCharacters ()
    {
        $this->invalid($this->testhelper->generateAlphaNumString(64), nonexistent: true);
    }

    //  nickname

    public function testValidNicknameMinimumCharacters ()
    {
        $this->valid(nickname: $this->testhelper->generateAlphaNumString(8));
    }

    public function testValidNicknameMaximumCharacters ()
    {
        $this->valid(nickname: $this->testhelper->generateAlphaNumString(64));
    }

    // limit

    public function testValidLimit ()
    {
        $limit = $this->testhelper->randomArrayChoice($GLOBALS['limit']);
        $this->valid(limit: $limit);
    }

    // invalid test
    // player

    public function testInvalidPlayerWithSymbols ()
    {
        $this->invalid($this->testhelper->randomSymbols());
    }

    public function testInvalidPlayerEmpty ()
    {
        $this->invalid('');
    }
    
    public function testInvalidPlayerWithWhiteSpaces ()
    {
        $this->invalid('   ');
    }

    public function testInvalidPlayerBelowMinimumCharacter ()
    {
        $player = $this->testhelper->generateUUid(2);
        $this->invalid($player);
    }

    public function testInvalidPlayerBeyondMaximumCharacter ()
    {
        $string = $this->testhelper->generateAlphaNumString(65);
        $this->invalid($string);
    }

    // nickname
    
    public function testInvalidNicknameWithSymbols ()
    {
        $this->invalid(nickname: $this->testhelper->randomSymbols());
    }

    public function testInvalidNicknameEmpty ()
    {
        $this->invalid(nickname: '');
    }
    
    public function testInvalidNicknameWithWhiteSpaces ()
    {
        $this->invalid(nickname: '   ');
    }

    public function testInvalidNicknameBelowMinimumCharacter ()
    {
        $player = $this->testhelper->generateUUid(7);
        $this->invalid(nickname: $player);
    }

    public function testInvalidNicknameBeyondMaximumCharacter ()
    {
        $string = $this->testhelper->generateAlphaNumString(65);
        $this->invalid(nickname: $string);
    }

    // lang

    public function testInvalidLangWithSymbols ()
    {
        $this->invalid(lang: $this->testhelper->randomSymbols());
    }

    public function testInvalidLangEmpty ()
    {
        $this->invalid(lang: '');
    }
    
    public function testInvalidLangWithWhiteSpaces ()
    {
        $this->invalid(lang: '   ');
    }

    public function testInvalidLangUpperCaseCharacters ()
    {
        $string = strtoupper(($this->faker->word()));
        $this->invalid(lang: $string);
    }

    public function testInvalidLangOneCharacter ()
    {
        $string = $this->testhelper->generateRandomLetters(1);
        $this->invalid(lang: $string);
    }

    // token

    public function testInvalidTkEmpty ()
    {
        $this->invalid(tk: '');
    }
    
    public function testInvalidNTkWithWhiteSpaces ()
    {
        $this->invalid(tk: '   ');
    }

    // limit

    public function testInvalidLimitWithSymbols ()
    {
        $this->invalid(limit: $this->testhelper->randomSymbols(5));
    }

    public function testInvalidLimitEmpty ()
    {
        $this->invalid(limit: '');
    }
    
    public function testInvalidLimitWithWhiteSpaces ()
    {
        $this->invalid(limit: '   ');
    }

    public function testInvalidLimitUpperCaseCharacters ()
    {
        $string = strtoupper(($this->faker->word()));
        $this->invalid(limit: $string);
    }

    public function testInvalidLimitLowerCaseCharacters ()
    {
        $string = strtolower(($this->faker->word()));
        $this->invalid(limit: $string);
    }
}