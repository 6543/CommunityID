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

class HistoryControllerTests extends PHPUnit_Framework_TestCase
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
        $user->role = Users_Model_User::ROLE_REGISTERED;
        $user->username = 'testuser';
        Zend_Registry::set('user', $user);
    }

    public function testIndexGuestUserAction()
    {
        Zend_Registry::get('user')->role = Users_Model_User::ROLE_GUEST;

        Application::$front->setRequest(new TestRequest('/history'));
        try {
            Application::dispatch();
        } catch (Monkeys_AccessDeniedException $e) {
            $this->assertTrue(true);
            return;
        }

        $this->fail('Expected Monkeys_AccessDeniedException was not raised');
    }

    public function testIndexAction()
    {
        Application::$front->setRequest(new TestRequest('/history'));
        Application::dispatch();

        $this->assertContains('COMMID.history', $this->_response->getBody());
    }

    public function testListAction()
    {
        $request = new TestRequest('/history/list?startIndex=0&results=15');
        $request->setHeader('X_REQUESTED_WITH', 'XMLHttpRequest');
        Application::$front->setRequest($request);
        Application::dispatch();

        $this->assertRegExp(
            '#\{("__className":"stdClass",)?"recordsReturned":\d+,"totalRecords":\d+,"startIndex":"\d+",("sort":null,)?"dir":"asc","records":\[.*\]\}#',
            $this->_response->getBody()
        );
    }

    /**
    * Weak test, till I set up a mock db obj to avoid touching the db
    */
    public function testClearAction()
    {
        $request = new TestRequest('/history/clear');
        $request->setHeader('X_REQUESTED_WITH', 'XMLHttpRequest');
        Application::$front->setRequest($request);
        Application::dispatch();

        $this->assertRegExp(
            '{"code":200}',
            $this->_response->getBody()
        );
    }

    public function tearDown()
    {
        // I know this is done again in setUp(), but if I don't do it here too,
        // hell breaks appart
        Application::cleanUp();
    }
}
