<?php
require_once('StoryCompanionTestFunctions.php');

use PHPUnit\Framework\TestCase;

/**
 * Order of these test cases are important - they allow for setup - test - cleanup
 */
class UserTest extends StoryCompanionTestFunctions
{
    private $mockUser = array(
        'email' => 'kyle@example.com',
        'password' => 'testing',
    );
    private $apiKey = null;

    public function testCreationUnique()
    {
        $response = $this->apiPost("/user/creation", $this->mockUser);
        $this->assertTrue(!array_key_exists('error', $response));
        $user = $this->query("SELECT * FROM user WHERE email = '" . $this->mockUser['email'] . "';");
        $this->assertTrue($user['email'] === $this->mockUser['email']);
        $this->assertTrue($user['password'] === hash('sha512', $this->mockUser['password']));
        $this->assertTrue($user['api_key'] === hash('sha512', $this->mockUser['email'] . $this->mockUser['password']));
        $this->apiKey = $user['api_key'];
    }

    public function testCreationNotUnique()
    {
        $response = $this->apiPost("/user/creation", $this->mockUser);
        $expected = array('error' => 'User with provided email already exists');
        $this->assertTrue($response['error'] === $expected['error']);
    }

    public function testLoginSuccess()
    {
        $response = $this->apiPost("/user/login", $this->mockUser);
        $this->assertTrue(array_key_exists('success', $response));
        $this->assertTrue($this->mockUser['email'] === $response['success']['email']);
    }

    public function testLoginInvalid()
    {
        $incorrectCredentials = $this->mockUser;
        $incorrectCredentials['password'] = "IAmNotTheRightPassword";
        $expected = array('error' => 'Credentials do not match any user');
        $response = $this->apiPost("/user/login", $incorrectCredentials);
        $this->assertTrue($expected['error'] === $response['error']);
    }

    public function testPasswordReset()
    {
        $response = $this->apiPost('/user/password_reset', $this->mockUser);
        $user = $this->query("SELECT * FROM user WHERE email = '" . $this->mockUser['email'] . "';");
        $this->assertTrue(array_key_exists('success', $response));
        $this->assertTrue(hash('sha512', $this->mockUser['password']) !== $user['password']);
    }

    public function testChangePassword()
    {
        $user = $this->query("SELECT * FROM user WHERE email = '" . $this->mockUser['email'] . "';");
        $this->mockUser['apiKey'] = $this->apiKey;
        $this->mockUser['password'] = 'Iwillbechanged!';
        $this->mockUser['confirmPassword'] = 'Iwillbechanged!';
        $this->mockUser['user'] = $user['id'];
        $response = $this->apiPost('/user/change_password', $this->mockUser);
        $user = $this->query("SELECT * FROM user WHERE email = '" . $this->mockUser['email'] . "';");
        $this->assertTrue(array_key_exists('success', $response));
        $this->assertTrue($user['password'] === hash('sha512', 'Iwillbechanged!'));
    }

    public function testChangeEmail()
    {
        $user = $this->query("SELECT * FROM user where email = '" . $this->mockUser['email'] . "';");
        $this->mockUser['apiKey'] = $this->apiKey;
        $this->mockUser['email'] = 'changed@domain.com';
        $this->mockUser['user'] = $user['id'];
        $response = $this->apiPost('/user/change_email', $this->mockUser);
        $user = $this->query("SELECT * FROM user WHERE email = 'changed@domain.com';");
        $this->assertTrue(array_key_exists('success', $response));
        $this->assertTrue($user !== null || count($user) !== 0);
    }

    public function testCleanUp()
    {
        $result = $this->deleteUserWithEmail($this->mockUser['email']);
        $this->assertTrue($result);
    }
}
