<?php

require_once 'tests/WalletBaseClass.php';

class DepositTest extends WalletBaseClass
{
    public function setUp(): void
    {
        parent::setUp();
        $this->setApiEndpoint(getenv("phpDp"));
    }
}
