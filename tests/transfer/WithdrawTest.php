<?php

require_once 'tests/WalletBaseClass.php';

class WithdrawTest extends WalletBaseClass
{
    public function setUp(): void
    {
        parent::setUp();
        $this->setApiEndpoint(getenv("phpWd"));
    }
}
