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
require_once dirname(__FILE__) . '/../../../CaptchaImageTestSessionContainer.php';

class FeedbackControllerTests extends PHPUnit_Framework_TestCase
{
    private $_response;

    public function setUp()
    {
        TestHarness::setUp();
        Setup::$front->returnResponse(true);
        $this->_response = new Zend_Controller_Response_Http();
        Setup::$front->setResponse($this->_response);
    }

    public function testIndexAction()
    {
        Setup::$front->setRequest(new TestRequest('/feedback'));
        Setup::dispatch();

        $this->assertContains('<form id="feedbackForm" method="post" action', $this->_response->getBody());
    }

    /**
    * @dataProvider provideBadFormInput
    */
    public function testSendWithEmptyFieldsAction($name, $email, $feedback)
    {
        $_POST = array(
            'name'      => $name,
            'email'     => $email,
            'feedback'  => $feedback,
        );

        Setup::$front->setRequest(new TestRequest('/feedback/send'));
        Setup::dispatch();

        $this->assertContains('Value is empty, but a non-empty value is required', $this->_response->getBody());
    }

    public function testSendWithBadEmailAction()
    {
        $_POST = array(
            'name'      => 'john doe',
            'email'     => 'john.doe.mailinator.com',
            'feedback'  => 'whateva',
        );

        Setup::$front->setRequest(new TestRequest('/feedback/send'));
        Setup::dispatch();

        $this->assertContains('is not a valid email address', $this->_response->getBody());
    }

    public function testSendWithBadCaptchaAction()
    {
        $_POST = array(
            'name'      => 'john doe',
            'email'     => 'john.doe@mailinator.com',
            'feedback'  => 'whateva',
            'captcha'   => 'whatever',
        );

        Setup::$front->setRequest(new TestRequest('/feedback/send'));
        Setup::dispatch();

        $this->assertContains('Captcha value is wrong', $this->_response->getBody());
    }

    public function testSuccessSendAction()
    {
        // I gotta render the form first to generate the captcha
        $sessionStub = new CaptchaImageTestSessionContainer();
        Zend_Registry::set('appSession', $sessionStub);
        Setup::$front->setRequest(new TestRequest('/feedback/send'));
        Setup::dispatch();
        $this->assertEquals(preg_match('/name="captcha\[id\]" value="([0-9a-f]+)"/', $this->_response->__toString(), $matches), 1);

        $email = 'john_' . rand(0, 1000) . '@mailinator.com';
        $_POST = array(
            'name'              => 'john',
            'email'             => $email,
            'feedback'          => 'whateva',
            'captcha'           => array(
                                        'input' => CaptchaImageTestSessionContainer::$word,
                                        'id'    => $matches[1],
                                   )
        );

        Setup::$front->setRequest(new TestRequest('/feedback/send'));

        Setup::$mockLogger->events = array();
        try {
            Setup::dispatch();
        } catch (Zend_Controller_Response_Exception $e) {
            // I still don't know how to avoid the "headers already sent" problem here...
        }
        $lastLog = array_pop(Setup::$mockLogger->events);
        $this->assertEquals("redirected to ''", $lastLog['message']);
    }

    public function testGetMail()
    {
        require_once APP_DIR . '/modules/default/controllers/FeedbackController.php';
        $mail = FeedbackController::getMail('John Black', 'john@mailinator.com', 'whateva');
        $this->assertType('Zend_Mail', $mail);
        $mailBody = $mail->getBodyText(true);
        $mailBody = str_replace("=\n", '', $mailBody);  // remove line splitters
        $this->assertContains('Dear Administrator', $mailBody);
        $this->assertContains('John Black', $mailBody);
        $this->assertContains('john@mailinator.com', $mailBody);
        $this->assertContains('whateva', $mailBody);
    }

    public function provideBadFormInput()
    {
        return array(
            array(
                'name'      => '',
                'email'     => 'john@mailinator.com',
                'feedback'  => 'whateva',
            ),
            array(
                'name'      => 'john doe',
                'email'     => '',
                'feedback'  => 'whateva',
            ),
            array(
                'name'      => 'john doe',
                'email'     => 'john@mailinator.com',
                'feedback'  => '',
            ),
        );
    }
}
