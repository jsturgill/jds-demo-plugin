<?php

class AuthenticationCest
{
    public function _before(AcceptanceTester $I)
    {
    }

//    // tests
    public function tryToLoginTest(AcceptanceTester $I)
    {
        $timeout = 10;
        $I->amOnPage("/wp-login.php");
        $I->waitForElement('#user_login', $timeout);
        $I->waitForElement('#user_pass', $timeout);
        $I->waitForElement('#wp-submit', $timeout);
        $I->fillField('#user_login', 'jsturgill');
        $I->fillField('#user_pass', 'fuq*Y@lyiB(3l(^cW3');
        $I->click('#wp-submit');
        $I->amOnPage("/wp-admin/");
        $I->see("Welcome to WordPress!");
        $I->see("Dashboard");
    }
}
