<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

require_once dirname(__FILE__) . '/../../../TestHarness.php';

class MessageusersControllerTests extends PHPUnit_Framework_TestCase
{
    private $_response;

    public function setUp()
    {
        TestHarness::setUp();
        Application::$front->returnResponse(true);
        $this->_response = new Zend_Controller_Response_Http();
        Application::$front->setResponse($this->_response);

        $users = new Users_Model_Users();
        $user = $users->createRow();
        $user->id = 23;
        $user->role = Users_Model_User::ROLE_ADMIN;
        $user->username = 'testadmin';
        Zend_Registry::set('user', $user);

    }

    /**
    * @expectedException Monkeys_AccessDeniedException
    */
    public function testIndexGuestUserAction()
    {
        Zend_Registry::get('user')->role = Users_Model_User::ROLE_GUEST;

        Application::$front->setRequest(new TestRequest('/messageusers'));
        Application::dispatch();
    }

    /**
    * @expectedException Monkeys_AccessDeniedException
    */
    public function testIndexRegisteredUserAction()
    {
        Zend_Registry::get('user')->role = Users_Model_User::ROLE_REGISTERED;

        Application::$front->setRequest(new TestRequest('/messageusers'));
        Application::dispatch();
    }

    public function testIndexAction()
    {
        Application::$front->setRequest(new TestRequest('/messageusers'));
        Application::dispatch();

        $this->assertContains('</form>', $this->_response->getBody());
    }

    public function testSaveActionWithEmptySubject()
    {
        $_POST = array(
            'messageType'   => 'rich',
            'subject'       => '',
            'cc'            => '',
            'bodyPlain'     => '',
            'bodyHTML'      => 'Hello <strong>world</strong>',
        );

        Application::$front->setRequest(new TestRequest('/messageusers/send'));
        Application::dispatch();

        $this->assertContains('Value is required and can\'t be empty', $this->_response->getBody());
    }

    public function testSaveActionWithBadCC()
    {
        $_POST = array(
            'messageType'   => 'rich',
            'subject'       => 'whateva',
            'cc'            => 'asdfdf',
            'bodyPlain'     => '',
            'bodyHTML'      => 'Hello <strong>world</strong>',
        );

        Application::$front->setRequest(new TestRequest('/messageusers/send'));
        Application::dispatch();

        $this->assertContains('CC field must be a comma-separated list of valid E-mails', $this->_response->getBody());
    }

    /**
    * @expectedException Monkeys_AccessDeniedException
    */
    public function testSaveGuestUser()
    {
        Zend_Registry::get('user')->role = Users_Model_User::ROLE_GUEST;

        Application::$front->setRequest(new TestRequest('/messageusers/send'));
        Application::dispatch();
    }

    /**
    * @expectedException Monkeys_AccessDeniedException
    */
    public function testSaveRegisteredUser()
    {
        Zend_Registry::get('user')->role = Users_Model_User::ROLE_REGISTERED;

        Application::$front->setRequest(new TestRequest('/messageusers/send'));
        Application::dispatch();
    }

    public function testSaveSuccessfull()
    {
        $_POST = array(
            'messageType'   => 'rich',
            'subject'       => 'whateva',
            'cc'            => 'one@mailinator.com, two@mailinator.com',
            'bodyPlain'     => '',
            'bodyHTML'      => 'Hello <strong>world</strong>',
        );

        Application::$front->setRequest(new TestRequest('/messageusers/send'));
        Application::$mockLogger->events = array();
        try {
            Application::dispatch();
        } catch (Zend_Controller_Response_Exception $e) {
            // I still don't know how to avoid the "headers already sent" problem here...
        }

        $lastLog = array_pop(Application::$mockLogger->events);
        $this->assertEquals("redirected to ''", $lastLog['message']);
    }
}
