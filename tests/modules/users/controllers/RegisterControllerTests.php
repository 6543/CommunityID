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
require_once dirname(__FILE__) . '/../../../CaptchaImageTestSessionContainer.php';

class Users_RegisterControllerTests extends PHPUnit_Framework_TestCase
{
    private $_response;

    public function setUp()
    {
        TestHarness::setUp();
        Application::$front->returnResponse(true);
        $this->_response = new Zend_Controller_Response_Http();
        Application::$front->setResponse($this->_response);
    }

    public function testIndexAction()
    {
        Application::$front->setRequest(new TestRequest('/users/register'));
        Application::dispatch();

        $this->assertContains('</form>', $this->_response->getBody());
    }

    /**
    * @dataProvider provideBadRegistrationInput
    */
    public function testSaveActionWithSomeEmptyFields(
        $firstname, $lastname, $email, $username, $password1, $password2
    )
    {
        $_POST = array(
            'firstname' => $firstname,
            'lastname'  => $lastname,
            'email'     => $email,
            'username'  => $username,
            'password1' => $password1,
            'password2' => $password2,
        );

        Application::$front->setRequest(new TestRequest('/users/register/save'));
        Application::dispatch();

        $this->assertContains('Value is required and can\'t be empty', $this->_response->getBody());
    }

    public function testSaveActionWithBadEmail()
    {
        $_POST = array(
                'firstname' => 'john',
                'lastname'  => 'smith',
                'email'     => 'john.mailinator.com',
                'username'  => 'johns34',
                'password1' => 'johns',
                'password2' => 'johns',
        );

        Application::$front->setRequest(new TestRequest('/users/register/save'));
        Application::dispatch();

        $this->assertContains('is not a valid email address', $this->_response->getBody());
    }

    public function testSaveActionWithUnmatchedPasswords()
    {
        $_POST = array(
            'firstname' => 'john',
            'lastname'  => 'smith',
            'email'     => 'john@mailinator.com',
            'username'  => 'johns34',
            'password1' => 'johnsa',
            'password2' => 'johns',
        );

        Application::$front->setRequest(new TestRequest('/users/register/save'));
        Application::dispatch();

        $this->assertContains('Password confirmation does not match', $this->_response->getBody());
    }

    public function testSaveActionWithBadCaptcha()
    {
        $_POST = array(
            'firstname' => 'john',
            'lastname'  => 'smith',
            'email'     => 'john@mailinator.com',
            'username'  => 'johns34',
            'password1' => 'johns',
            'password2' => 'johns',
            'captcha'   => 'whatever',
        );

        Application::$front->setRequest(new TestRequest('/users/register/save'));
        Application::dispatch();

        $this->assertContains('Captcha value is wrong', $this->_response->getBody());
    }

    public function testSuccessfullSaveAction()
    {
        // I gotta render the form first to generate the captcha
        $sessionStub = new CaptchaImageTestSessionContainer();
        Zend_Registry::set('appSession', $sessionStub);
        Application::$front->setRequest(new TestRequest('/users/register'));
        Application::dispatch();
        $this->assertEquals(preg_match('/name="captcha\[id\]" value="([0-9a-f]+)"/', $this->_response->__toString(), $matches), 1);

        $email = 'john_' . rand(0, 1000) . '@mailinator.com';
        $_POST = array(
            'firstname'         => 'john',
            'lastname'          => 'smith',
            'email'             => $email,
            'username'          => 'johns34',
            'password1'         => 'johns',
            'password2'         => 'johns',
            'captcha'           => array(
                                        'input' => CaptchaImageTestSessionContainer::$word,
                                        'id'    => $matches[1],
                                   )
        );

        // this is used to build the users's openid URL
        $_SERVER['SCRIPT_URI'] = 'http://localhost/communityid/users/register/save';
        Application::$front->setRequest(new TestRequest('/users/register/save'));

        Application::$mockLogger->events = array();
        try {
            Application::dispatch();
        } catch (Zend_Controller_Response_Exception $e) {
            // I still don't know how to avoid the "headers already sent" problem here...
        }
        $lastLog = array_pop(Application::$mockLogger->events);
        $this->assertEquals("redirected to ''", $lastLog['message']);

        $users = new Users_Model_Users();
        $user = $users->getUserWithEmail($email);
        $this->assertType('Users_Model_User', $user);
        $this->assertEquals('johns34', $user->username);
        $this->assertEquals('http://localhost/communityid/identity/johns34', $user->openid);
        $this->assertEquals(0, $user->accepted_eula);
        $this->assertEquals('john', $user->firstname);
        $this->assertEquals('smith', $user->lastname);
        $this->assertEquals($email, $user->email);
        $this->assertEquals(Users_Model_User::ROLE_GUEST, $user->role);
        $this->assertNotEquals('', $user->token);

        $user->delete();
    }

    public function testGetMail()
    {
        $user = $this->_getUser();

        // this is used to build the the registration URL
        $_SERVER['SCRIPT_URI'] = 'http://localhost/communityid/users/register/save';

        require_once APP_DIR . '/modules/users/controllers/RegisterController.php';
        $mail = Users_RegisterController::getMail($user, 'Community-ID registration confirmation');
        $this->assertType('Zend_Mail', $mail);
        $mailBody = $mail->getBodyText(true);
        $mailBody = str_replace("=\n", '', $mailBody);  // remove line splitters
        $this->assertContains('Dear ' . $user->getFullName(), $mailBody);
        $this->assertEquals(preg_match('#http://localhost/communityid/users/register/eula\?token=3D([0-9a-f=\n]+)#', $mailBody, $matches), 1);
        $token = str_replace('=0', '', $matches[1]);  // remove trailing return chars
        $token = str_replace(array('=', "\n"), '', $token);
        $this->assertEquals($token, $user->token);
    }

    public function testEulaBadTokenAction()
    {
        $_GET = array('token' => 'asdfsdf');
        Application::$front->setRequest(new TestRequest('/users/register/eula'));
        try {
            Application::dispatch();
        } catch (Zend_Controller_Response_Exception $e) {
        }
        $lastLog = array_pop(Application::$mockLogger->events);
        $this->assertEquals("redirected to ''", $lastLog['message']);
    }

    public function testEulaAction()
    {
        $user = $this->_getUser();
        $user->save();
        $_GET = array('token' => $user->token);
        Application::$front->setRequest(new TestRequest('/users/register/eula'));
        Application::dispatch();
        $fp = fopen(dirname(__FILE__) . '/../../../../resources/en/eula.txt', 'r');
        $firstLine = fgets($fp);
        $this->assertContains($firstLine, $this->_response->getBody());
        $user->delete();
    }

    public function testDeclineeulaBadTokenAction()
    {
        $_GET = array('token' => 'asdfsdf');
        Application::$front->setRequest(new TestRequest('/users/register/declineeula'));
        try {
            Application::dispatch();
        } catch (Zend_Controller_Response_Exception $e) {
        }
        $lastLog = array_pop(Application::$mockLogger->events);
        $this->assertEquals("redirected to ''", $lastLog['message']);
        $lastLog = array_pop(Application::$mockLogger->events);
        $this->assertEquals("invalid token", $lastLog['message']);
    }

    public function testDeclineeulaAction()
    {
        $user = $this->_getUser();
        $user->save();
        $token = $user->token;

        $_GET = array('token' => $user->token);
        Application::$front->setRequest(new TestRequest('/users/register/declineeula'));
        try {
            Application::dispatch();
        } catch (Zend_Controller_Response_Exception $e) {
        }
        $lastLog = array_pop(Application::$mockLogger->events);
        $this->assertEquals("redirected to ''", $lastLog['message']);

        $users = new Users_Model_Users();
        $user = $users->getUserWithToken($token);
        $this->assertNull($user);
    }

    public function testAccepteulaBadTokenAction()
    {
        $_GET = array('token' => 'asdfsdf');
        Application::$front->setRequest(new TestRequest('/users/register/accepteula'));
        try {
            Application::dispatch();
        } catch (Zend_Controller_Response_Exception $e) {
        }
        $lastLog = array_pop(Application::$mockLogger->events);
        $this->assertEquals("redirected to ''", $lastLog['message']);
    }

    public function testAccepteulaAction()
    {
        $user = $this->_getUser();
        $user->save();
        $token = $user->token;

        $_GET = array('token' => $user->token);

        Application::$front->setRequest(new TestRequest('/users/register/accepteula'));
        try {
            Application::dispatch();
        } catch (Zend_Controller_Response_Exception $e) {
        }
        $lastLog = array_pop(Application::$mockLogger->events);
        $this->assertEquals("redirected to '/users/profile'", $lastLog['message']);

        $user->delete();
    }

    public function provideBadRegistrationInput()
    {
        return array(
            array(
                'firstname' => '',
                'lastname'  => 'smith',
                'email'     => 'john@mailinator.com',
                'username'  => 'johns34',
                'password1' => 'johns',
                'password2' => 'johns',
            ),
            array(
                'firstname' => 'john',
                'lastname'  => '',
                'email'     => 'john@mailinator.com',
                'username'  => 'johns34',
                'password1' => 'johns',
                'password2' => 'johns',
            ),
            array(
                'firstname' => 'john',
                'lastname'  => 'smith',
                'email'     => 'john@mailinator.com',
                'username'  => 'johns34',
                'password1' => '',
                'password2' => '',
            ),
        );
    }

    private function _getUser()
    {
        $users = new Users_Model_Users();
        $user = $users->createRow();
        $user->firstname = 'john';
        $user->lastname = 'smith';
        $user->token = Users_Model_User::generateToken();
        $user->email = 'john@mailinator.com';

        return $user;
    }
}
