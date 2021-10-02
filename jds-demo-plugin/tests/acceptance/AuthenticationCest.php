<?php

class AuthenticationCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function tryToLoginTest(AcceptanceTester $I)
    {
        $I->login();
    }
}
