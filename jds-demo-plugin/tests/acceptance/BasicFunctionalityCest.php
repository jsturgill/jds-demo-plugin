<?php

use JdsDemoPlugin\Plugin;

class BasicFunctionalityCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function tryToLoginTest(AcceptanceTester $I)
    {
        $I->login();
    }

    public function verifyRandomName(AcceptanceTester $I)
    {
        $I->login();
        $I->amOnPage('/wp-admin/options-general.php?page=jds-demo-plugin-options');
        $I->see("Hello, ");
        $I->dontSee("Hello, " . Plugin::DEFAULT_AUDIENCE);
        $I->dontSee(Plugin::ERROR_MESSAGE_NAME_REPO_FAILURE);
    }
}
