<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/


require_once dirname(__FILE__) . '/../../../TestHarness.php';

class UsersTests extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        TestHarness::setUp();
    }

    public function testUserCreating()
    {
        $users = new Users_Model_Users();

        $user = $users->getUserWithEmail('thisshouldntexist');
        $this->assertNull($user);

        $user = $users->createRow();
        $user->test = 1;
        $user->username = 'usernametest';
        $user->openid = 'http://example.com';
        $user->accepted_eula = 1;
        $user->firstname = 'firstnametest';
        $user->lastname = 'lastnametest';
        $user->email = 'usertest@mailinator.com';
        $user->role = Users_Model_User::ROLE_REGISTERED;
        $user->token = '';
        $user->save();

        $user = $users->getUserWithEmail('usertest@mailinator.com');
        $this->assertType('Users_Model_User', $user);
        $this->assertEquals('usernametest', $user->username);
        $this->assertEquals('http://example.com', $user->openid);
        $this->assertEquals(1, $user->accepted_eula);
        $this->assertEquals('firstnametest', $user->firstname);
        $this->assertEquals('lastnametest', $user->lastname);
        $this->assertEquals('usertest@mailinator.com', $user->email);
        $this->assertEquals(Users_Model_User::ROLE_REGISTERED, $user->role);
        $this->assertEquals('', $user->token);

        $user->delete();

        $user = $users->getUserWithEmail('thisshouldntexist');
        $this->assertNull($user);
    }
}
