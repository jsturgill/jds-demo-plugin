<?php

use Codeception\Actor;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceTester extends Actor
{
    use _generated\AcceptanceTesterActions;

    public function login($name, $password)
    {
        $I = $this;
        // if snapshot exists - skipping login
        if ($I->loadSessionSnapshot('login')) {
            return;
        }

        $timeout = 10;

        // load the login page
        $I->amOnPage("/wp-login.php");
        $I->waitForElement('#user_login', $timeout);
        $I->waitForElement('#user_pass', $timeout);
        $I->waitForElement('#wp-submit', $timeout);

        // submit the form
        $I->fillField('#user_login', $name);
        $I->fillField('#user_pass', $password);
        $I->click('#wp-submit');

        // verify the things
        $I->amOnPage("/wp-admin/");
        $I->see("Welcome to WordPress!");
        $I->see("Dashboard");

        // saving snapshot
        $I->saveSessionSnapshot('login');
    }
}
