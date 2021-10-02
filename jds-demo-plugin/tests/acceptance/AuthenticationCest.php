<?php

class AuthenticationCest
{
    public function _before(AcceptanceTester $I)
    {
    }

//    // tests
    public function tryToLoginTest(AcceptanceTester $I)
    {
        $I->login('jsturgill', 'fuq*Y@lyiB(3l(^cW3');
    }
}
