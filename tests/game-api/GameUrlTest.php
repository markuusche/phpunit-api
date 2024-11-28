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

    public function assert (
    $player = null, 
    $nickname = null, 
    $lang = null, 
    $tk = null,
    $gi = null,
    $limit = null,
    $timestamp = null,
    $valid = false,
    $nonexistent = false
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

        if ($valid) {
            $this->assertEquals('success', actual: $body['rs_message']);
            $this->assertTrue(filter_var($body[getenv("phpURL")], FILTER_VALIDATE_URL) !== false);
        }
        else {
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

    }

    // valid test

    public function testValidGameUrl()
    {
        $this->assert(valid: true);
    }

    // player 
    public function testValidNonAvailablePlayer ()
    {
        $player = $this->testhelper->generateAlphaNumString(6);
        $this->assert(player: $player, nonexistent: true);
    }

    public function testValidPlayerMinimumCharacters ()
    {
        $player = $this->testhelper->generateAlphaNumString(3);
        $this->assert(player: $player, nonexistent: true);
    }

    public function testValidPlayerMaximumCharacters ()
    {
        $player = $this->testhelper->generateAlphaNumString(64);
        $this->assert(player: $player, nonexistent: true);
    }

    //  nickname

    public function testValidNicknameMinimumCharacters ()
    {
        $nickname = $this->testhelper->generateAlphaNumString(8);
        $this->assert(nickname: $nickname, valid: true);
    }

    public function testValidNicknameMaximumCharacters ()
    {
        $nickname = $this->testhelper->generateAlphaNumString(64);
        $this->assert(nickname: $nickname, valid: true);
    }

    // limit

    public function testValidLimit ()
    {
        $limit = $this->testhelper->randomArrayChoice($GLOBALS['limit']);
        $this->assert(limit: $limit, valid: true);
    }

    // invalid test
    // player

    public function testInvalidPlayerWithSymbols ()
    {
        $this->assert(player: $this->testhelper->randomSymbols());
    }

    public function testInvalidPlayerEmpty ()
    {
        $this->assert('');
    }
    
    public function testInvalidPlayerWithWhiteSpaces ()
    {
        $this->assert('   ');
    }

    public function testInvalidPlayerBelowMinimumCharacter ()
    {
        $player = $this->testhelper->generateUUid(2);
        $this->assert(player: $player);
    }

    public function testInvalidPlayerBeyondMaximumCharacter ()
    {
        $string = $this->testhelper->generateAlphaNumString(65);
        $this->assert(player: $string);
    }

    // nickname
    
    public function testInvalidNicknameWithSymbols ()
    {
        $this->assert(nickname: $this->testhelper->randomSymbols());
    }

    public function testInvalidNicknameEmpty ()
    {
        $this->assert(nickname: '');
    }
    
    public function testInvalidNicknameWithWhiteSpaces ()
    {
        $this->assert(nickname: '   ');
    }

    public function testInvalidNicknameBelowMinimumCharacter ()
    {
        $player = $this->testhelper->generateUUid(7);
        $this->assert(nickname: $player);
    }

    public function testInvalidNicknameBeyondMaximumCharacter ()
    {
        $string = $this->testhelper->generateAlphaNumString(65);
        $this->assert(nickname: $string);
    }

    // lang

    public function testInvalidLangWithSymbols ()
    {
        $this->assert(lang: $this->testhelper->randomSymbols());
    }

    public function testInvalidLangEmpty ()
    {
        $this->assert(lang: '');
    }
    
    public function testInvalidLangWithWhiteSpaces ()
    {
        $this->assert(lang: '   ');
    }

    public function testInvalidLangUpperCaseCharacters ()
    {
        $string = strtoupper(($this->faker->word()));
        $this->assert(lang: $string);
    }

    public function testInvalidLangOneCharacter ()
    {
        $string = $this->testhelper->generateRandomLetters(1);
        $this->assert(lang: $string);
    }

    // token

    public function testInvalidTkEmpty ()
    {
        $this->assert(tk: '');
    }
    
    public function testInvalidTkWithWhiteSpaces ()
    {
        $this->assert(tk: '   ');
    }

    // limit

    public function testInvalidLimitWithSymbols ()
    {
        $this->assert(limit: $this->testhelper->randomSymbols(5));
    }

    public function testInvalidLimitEmpty ()
    {
        $this->assert(limit: '');
    }
    
    public function testInvalidLimitWithWhiteSpaces ()
    {
        $this->assert(limit: '   ');
    }

    public function testInvalidLimitUpperCaseCharacters ()
    {
        $string = strtoupper(($this->faker->word()));
        $this->assert(limit: $string);
    }

    public function testInvalidLimitLowerCaseCharacters ()
    {
        $string = strtolower(($this->faker->word()));
        $this->assert(limit: $string);
    }
}