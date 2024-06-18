<?php

namespace creode\magiclogintests\acceptance;

use Craft;
use craft\web\View;
use FunctionalTester;
use craft\elements\User;
use creode\magiclogin\records\AuthRecord;

class CraftDefaultRegistrationCest
{
    // Function to try and register a user using Craft's normal registration process (without password).
    // Ensure that email is still sent.
    public function testCraftRegistrationWithNoPassword(FunctionalTester $I)
    {
        // 1 is the admin user created automatically when Craft boots up the tests.
        $I->amLoggedInAs(1);

        $I->amOnPage('admin/users/new');
        $emailAddress = 'contact@creode.co.uk';

        $I->submitForm('#main-form', [
            'username' => 'creode',
            'email' => $emailAddress,
            // 'sendActivationEmail' => '1',
        ], 'Create user');

        $I->amOnPage('/admin/users');

        $I->seeEmailIsSent(1);

        $email = $I->grabLastSentEmail();

        $I->assertArrayHasKey($emailAddress, $email->getTo());
        $I->assertEquals('account_activation', $email->key);

        // Delete user
        $user = User::findOne(['email' => 'contact@creode.co.uk']);
        Craft::$app->elements->deleteElement($user);
    }

    public function testUsersNotCreatedWithMagicLoginDontHaveAuthRecords(FunctionalTester $I)
    {
        // 1 is the admin user created automatically when Craft boots up the tests.
        $I->amLoggedInAs(1);

        $I->amOnPage('admin/users/new');
        $emailAddress = 'contact@creode.co.uk';

        $authRecords = AuthRecord::find()->all();

        $I->submitForm('#main-form', [
            'username' => 'creode',
            'email' => $emailAddress,
        ], 'Create user');

        $updatedAuthRecords = AuthRecord::find()->all();

        $I->assertEquals(count($authRecords), count($updatedAuthRecords));

        // Delete user
        $user = User::findOne(['email' => 'contact@creode.co.uk']);
        Craft::$app->elements->deleteElement($user);
    }
}
