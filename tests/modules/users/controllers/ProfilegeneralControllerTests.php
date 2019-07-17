<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since Textroller 0.9
* @package TextRoller
* @packager Keyboard Monkeys
*/


require_once dirname(__FILE__) . '/../../../TestHarness.php';

class Users_ProfilegeneralControllerTests extends PHPUnit_Framework_TestCase
{
    private $_response;

    public function setUp()
    {
        TestHarness::setUp();
        Setup::$front->returnResponse(true);
        $this->_response = new Zend_Controller_Response_Http();
        Setup::$front->setResponse($this->_response);
    }

    public function testChangepasswordAction()
    {
        $users = new Users();
        $user = $users->createRow();
        $user->id = 23;
        $user->role = User::ROLE_REGISTERED;
        Zend_Registry::set('user', $user);

        $targetUser = $users->createRow();
        $targetUser->id = 24;
        Zend_Registry::set('targetUser', $targetUser);

        Setup::$front->setRequest(new TestRequest('/users/profilegeneral/changepassword'));
        try {
            Setup::dispatch();
            $this->fail();
        } catch (Exception $e) {
            $this->assertType('Monkeys_AccessDeniedException', $e);
        }

        $targetUser = clone $user;
        Zend_Registry::set('targetUser', $targetUser);
        Setup::dispatch();
        $this->assertContains('<form name="changePasswordForm"', $this->_response->getBody());
    }
}
