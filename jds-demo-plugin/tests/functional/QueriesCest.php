<?php

class QueriesCest
{
    public function _before(FunctionalTester $I)
    {
    }

    public function dataIsLoadedTest(FunctionalTester $I)
    {
        $I->seeInDatabase('names', ['name' => 'Alice']);
        $I->seeInDatabase('names', ['name' => 'Bob']);
        $I->seeInDatabase('names', ['name' => 'Carol']);
    }
}
