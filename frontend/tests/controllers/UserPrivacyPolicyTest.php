<?php
/**
 * Description of UserPrivacyPolicyTest
 *
 * @author juan.pablo
 */
class UserPrivacyPolicyTest extends OmegaupTestCase {
    /**
     * User reviews Privacy Consent Log
     */
    public function testUserReviewsPrivacyConsentLog() {
        // Create privacy policy
        $privacy_poilcy = UserFactory::createPrivacyStatement();

        // Create the user
        $user = UserFactory::createUser();
        $login = self::login($user);

        $response = UserController::apiLastPrivacyPolicyAccepted(new Request([
            'auth_token' => $login->auth_token
        ]));

        $this->assertFalse($response['hasAccepted'], 'User should not have already accepted privacy policy');
    }

    /**
     * User accepts for first time privacy policy
     */
    public function testUserAcceptsPrivacyPolicy() {
        // Create the user
        $user = UserFactory::createUser();
        $login = self::login($user);

        // Create privacy policy
        $privacy_poilcy = UserFactory::createPrivacyStatement();

        $response = UserController::apiAcceptPrivacyPolicy(new Request([
            'auth_token' => $login->auth_token
        ]));

        $this->assertEquals($response['status'], 'ok');

        $response = UserController::apiLastPrivacyPolicyAccepted(new Request([
            'auth_token' => $login->auth_token
        ]));

        $this->assertTrue($response['hasAccepted'], 'User should have already accepted privacy policy');
    }

    /**
     * User tries to accept policy previously accepted
     */
    public function testUserTriesToAcceptPolicyPreviouslyAccepted() {
        // Create the user
        $user = UserFactory::createUser();
        $login = self::login($user);

        // Create privacy policy
        $privacy_poilcy_version_1 = UserFactory::createPrivacyStatement();

        $response = UserController::apiAcceptPrivacyPolicy(new Request([
            'auth_token' => $login->auth_token
        ]));

        $this->assertEquals($response['status'], 'ok');

        $this->expectException('DuplicatedEntryInDatabaseException');

        $response = UserController::apiAcceptPrivacyPolicy(new Request([
            'auth_token' => $login->auth_token
        ]));
    }

    /**
     * User accepts previous policy but not latest one
     */
    public function testAcceptsPreviousPolicyButNotLatestOne() {
        // Create the user
        $user = UserFactory::createUser();
        $login = self::login($user);

        // Create privacy policy
        $privacy_poilcy_version_1 = UserFactory::createPrivacyStatement();

        $response = UserController::apiLastPrivacyPolicyAccepted(new Request([
            'auth_token' => $login->auth_token
        ]));

        $this->assertFalse($response['hasAccepted'], 'User should not have already accepted privacy policy');

        $response = UserController::apiAcceptPrivacyPolicy(new Request([
            'auth_token' => $login->auth_token
        ]));

        $this->assertEquals($response['status'], 'ok');

        // Create other privacy policy
        $privacy_poilcy_version_2 = UserFactory::createPrivacyStatement();

        $response = UserController::apiLastPrivacyPolicyAccepted(new Request([
            'auth_token' => $login->auth_token
        ]));

        $this->assertFalse($response['hasAccepted'], 'User should not have already accepted privacy policy');
    }
}
