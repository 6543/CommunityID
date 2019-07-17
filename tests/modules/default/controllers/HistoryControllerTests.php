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
        Setup::$front->returnResponse(true);
        $this->_response = new Zend_Controller_Response_Http();
        Setup::$front->setResponse($this->_response);

        $users = new Users();
        $user = $users->createRow();
        $user->id = 23;
        $user->role = User::ROLE_ADMIN;
        $user->username = 'testuser';
        Zend_Registry::set('user', $user);
    }

    /**
    * @expectedException Monkeys_AccessDeniedException
    */
    public function testIndexGuestUserAction()
    {
        Zend_Registry::get('user')->role = User::ROLE_GUEST;

        Setup::$front->setRequest(new TestRequest('/history'));
        Setup::dispatch();
    }

    public function testIndexAction()
    {
        Setup::$front->setRequest(new TestRequest('/history'));
        Setup::dispatch();

        $this->assertContains('COMMID.history', $this->_response->getBody());
    }

    public function testListAction()
    {
        $request = new TestRequest('/history/list?startIndex=0&results=15');
        $request->setHeader('X_REQUESTED_WITH', 'XMLHttpRequest');
        Setup::$front->setRequest($request);
        Setup::dispatch();

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
        Setup::$front->setRequest($request);
        Setup::dispatch();

        $this->assertRegExp(
            '{"code":200}',
            $this->_response->getBody()
        );
    }
}
