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

class Users_ProfilegeneralControllerTests extends PHPUnit_Framework_TestCase
{
    private $_response;

    public function setUp()
    {
        TestHarness::setUp();
        Application::$front->returnResponse(true);
        $this->_response = new Zend_Controller_Response_Http();
        Application::$front->setResponse($this->_response);
    }

    public function testChangepasswordAction()
    {
        $users = new Users_Model_Users();
        $user = $users->createRow();
        $user->id = 23;
        $user->role = Users_Model_User::ROLE_REGISTERED;
        Zend_Registry::set('user', $user);

        $targetUser = $users->createRow();
        $targetUser->id = 24;
        Zend_Registry::set('targetUser', $targetUser);

        Application::$front->setRequest(new TestRequest('/users/profilegeneral/changepassword'));
        try {
            Application::dispatch();
            $this->fail();
        } catch (Exception $e) {
            $this->assertType('Monkeys_AccessDeniedException', $e);
        }

        $targetUser = clone $user;
        Zend_Registry::set('targetUser', $targetUser);
        Application::dispatch();
        $this->assertContains('<form name="changePasswordForm"', $this->_response->getBody());
    }
}
