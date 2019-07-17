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

class IdentityControllerTests extends PHPUnit_Framework_TestCase
{
    private $_response;

    public function setUp()
    {
        TestHarness::setUp();
        Setup::$front->returnResponse(true);
        $this->_response = new Zend_Controller_Response_Http();
        Setup::$front->setResponse($this->_response);
        
        // guest user
        $users = new Users();
        $user = $users->createRow();
        Zend_Registry::set('user', $user);
    }

    /**
    * @expectedException Monkeys_BadUrlException
    */
    public function testIndexNoIdentityAction()
    {
        Setup::$front->setRequest(new TestRequest('/identity'));
        Setup::dispatch();
    }

    public function testIdAction()
    {
        Setup::$front->setRequest(new TestRequest('/identity/whateva'));
        $_SERVER['SCRIPT_URI'] = 'http://localhost/communityid/identity/whateva';
        Setup::dispatch();
        $this->assertContains('<link href="http://localhost/communityid/openid/provider" rel="openid2.provider" />',
                              $this->_response->getBody());
        $this->assertContains('<h2 style="text-align:center">http://localhost/communityid/identity/whateva</h2>',
                              $this->_response->getBody());
    }
}
