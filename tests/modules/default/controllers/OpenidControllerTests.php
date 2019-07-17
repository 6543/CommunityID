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

class OpenidControllerTests extends PHPUnit_Framework_TestCase
{
    const USER_PASSWORD = 'secret';
    const CHECKID_QUERY = 'openid.ns=http%%3A%%2F%%2Fspecs.openid.net%%2Fauth%%2F2.0&openid.mode=checkid_setup&openid.identity=http%%3A%%2F%%2Flocalhost%%2Fcommunityid%%2Fidentity%%2Ftestuser&openid.claimed_id=http%%3A%%2F%%2Flocalhost%%2Fcommunityid%%2Fidentity%%2Ftestuser&openid.assoc_handle=%s&openid.return_to=http%%3A%%2F%%2Fwww.example.com&openid.realm=http%%3A%%2F%%2Fwww.example.com';

    private $_response;
    private $_tempDir;
    private $_user;

    // state isn't preserved accross test methods, so gotta use a static
    public static $assocHandle;

    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->_tempDir = APP_DIR . '/tests/temp';
    }

    public function setUp()
    {
        TestHarness::setUp();

        Application::$front->returnResponse(true);
        $this->_response = new Zend_Controller_Response_Http();
        $this->_response->headersSentThrowsException = false;
        Application::$front->setResponse($this->_response);

        $users = new Users_Model_Users();

        $users->deleteTestEntries();

        $this->_user = $users->createRow();
        $this->_user->test = 1;
        $this->_user->username = 'testuser';
        $this->_user->role = Users_Model_User::ROLE_REGISTERED;
        $this->_user->openid = 'http://localhost/communityid/identity/'.$this->_user->username;
        $this->_user->setClearPassword(self::USER_PASSWORD);
        $this->_user->accepted_eula = 1;
        $this->_user->firstname = 'firstnametest';
        $this->_user->lastname = 'lastnametest';
        $this->_user->email = 'usertest@mailinator.com';
        $this->_user->token = '';
        $this->_user->save();
        Zend_Registry::set('user', $this->_user);

        // php-openid lib sucks
        $GLOBALS['Auth_OpenID_registered_aliases'] = array();
        $GLOBALS['_Auth_OpenID_Request_Modes'] = array('checkid_setup', 'checkid_immediate');
        $GLOBALS['Auth_OpenID_sreg_data_fields'] = array(
                                      'fullname' => 'Full Name',
                                      'nickname' => 'Nickname',
                                      'dob' => 'Date of Birth',
                                      'email' => 'E-mail Address',
                                      'gender' => 'Gender',
                                      'postcode' => 'Postal Code',
                                      'country' => 'Country',
                                      'language' => 'Language',
                                      'timezone' => 'Time Zone');
    }

    public function testIndexAction()
    {
        Application::$front->setRequest(new TestRequest('/openid'));
        try {
            Application::dispatch();
        } catch (Monkeys_BadUrlException $e) {
            $this->assertTrue(true);
            return;
        }

        $this->fail('Expected Monkeys_BadUrlException was not raised');
    }

    public function testProviderAssociateAction()
    {
        $_GET = array(
            'openid.ns'                 => 'http://specs.openid.net/auth/2.0',
            'openid.mode'               => 'associate',
            'openid.assoc_type'         => 'HMAC-SHA256',
            'openid.session_type'       => 'DH-SHA256',
            'openid.dh_modulus'         => 'ANz5OguIOXLsDhmYmsWizjEOHTdxfo2Vcbt2I3MYZuYe91ouJ4mLBX+YkcLiemOcPym2CBRYHNOyyjmG0mg3BVd9RcLn5S3IHHoXGHblzqdLFEi/368Ygo79JRnxTkXjgmY0rxlJ5bU1zIKaSDuKdiI+XUkKJX8Fvf8W8vsixYOr',
            'openid.dh_gen'             => 'Ag==',
            'openid.dh_consumer_public' => 'MFzHUMsSa4YSQ3JrcPSqyUaTQ3Z+QWKH6knvrREW7b6zQ2qMdOrpckgnUgo0pILMQpls8Ty/3JDv+IO29qASk2PwwZwxC2kXK/MQC/om5gs/IpjPSw1wK4bz2QTUHTRSxmtTxiq0tHYmIIqadz4TTMfXohMU2VCuYBqDNMHZFpk=',
        );

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['QUERY_STRING'] = http_build_query($_GET);

        Application::$front->setRequest(new TestRequest('/openid/provider'));
        Application::dispatch();

        $this->assertEquals(
            preg_match(
                "%
                    assoc_handle:(\{HMAC-SHA256\}\{[a-f0-9]+\}\{.*==\})\\x0A
                    assoc_type:HMAC-SHA256\\x0A
                    dh_server_public:.*\\x0A
                    enc_mac_key:.*\\x0A
                    expires_in:\d+\\x0A
                    ns:http://specs\.openid\.net/auth/2\.0\\x0A
                    session_type:DH-SHA256\\x0A
                %x",
                $this->_response->getBody(),
                $matches
            ),
            1
        );
        self::$assocHandle = urlencode($matches[1]);
    }

    public function testProviderCheckidSetupAction()
    {
        Zend_Auth::getInstance()->clearIdentity();
        Zend_Registry::getInstance()->offsetUnset('user');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['QUERY_STRING'] = 'openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0&openid.mode=checkid_setup&openid.identity=http%3A%2F%2Flocalhost%2Fcommunityid%2Fidentity%2Ftestuser&openid.claimed_id=http%3A%2F%2Flocalhost%2Fcommunityid%2Fidentity%2Ftestuser&openid.assoc_handle='.self::$assocHandle.'&openid.return_to=http%3A%2F%2Fwww.example.com&openid.realm=http%3A%2F%2Fwww.example.com';

        Zend_OpenId::$exitOnRedirect = false;

        Application::$front->setRequest(new TestRequest('/openid/provider?' . $_SERVER['QUERY_STRING']));
        Application::dispatch();

        $this->assertContains('<form action="authenticate?'.$_SERVER['QUERY_STRING'].'" method="post" class="formGrid">', $this->_response->getBody());
    }

    public function testLoginAction()
    {
        Zend_Auth::getInstance()->clearIdentity();
        Zend_Registry::getInstance()->offsetUnset('user');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['QUERY_STRING'] = sprintf(self::CHECKID_QUERY, self::$assocHandle);

        Application::$front->setRequest(new TestRequest('/openid/login?' . $_SERVER['QUERY_STRING']));
        Application::dispatch();

        $this->assertContains('<form action="authenticate?'.$_SERVER['QUERY_STRING'].'" method="post" class="formGrid">', $this->_response->getBody());
    }

    public function testAuthenticateEmptyUsernameAction()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['QUERY_STRING'] = sprintf(self::CHECKID_QUERY, self::$assocHandle);

        $_POST = array(
            'openIdIdentity'    => '',
            'password'          => self::USER_PASSWORD,
        );

        Application::$front->setRequest(new TestRequest('/openid/authenticate?' . $_SERVER['QUERY_STRING']));
        Application::dispatch();

        $this->assertContains('Login', $this->_response->getBody());
    }

    public function testAuthenticateBadUsernameAction()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['QUERY_STRING'] = sprintf(self::CHECKID_QUERY, self::$assocHandle);

        $_POST = array(
            'openIdIdentity'    => 'whateva',
            'password'          => 'whatevaagain',
        );

        Application::$front->setRequest(new TestRequest('/openid/authenticate?' . $_SERVER['QUERY_STRING']));
        Application::dispatch();

        $this->assertContains('Login', $this->_response->getBody());
    }

    public function testAuthenticateBadPasswordAction()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['QUERY_STRING'] = sprintf(self::CHECKID_QUERY, self::$assocHandle);

        $_POST = array(
            'openIdIdentity'    => $this->_user->openid,
            'password'          => 'badpassword',
        );

        Application::$front->setRequest(new TestRequest('/openid/authenticate?' . $_SERVER['QUERY_STRING']));
        Application::dispatch();

        $this->assertContains('Login', $this->_response->getBody());
    }

    public function testAuthenticateSuccessfulAction()
    {
        Zend_Auth::getInstance()->clearIdentity();
        Zend_Registry::getInstance()->offsetUnset('user');

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['QUERY_STRING'] = sprintf(self::CHECKID_QUERY, self::$assocHandle);

        $_POST = array(
            'openIdIdentity'    => $this->_user->openid,
            'password'          => self::USER_PASSWORD,
        );

        Application::$front->setRequest(new TestRequest('/openid/authenticate?' . $_SERVER['QUERY_STRING']));
        Application::dispatch();

        $this->assertContains(
            'A site identifying as <a href="http://www.example.com">http://www.example.com</a> has asked for confirmation that <a href="'.$this->_user->openid.'">'.$this->_user->openid.'</a> is your identity URL.',
            $this->_response->getBody()
        );
    }

    public function testTrustAction1()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['QUERY_STRING'] = sprintf(self::CHECKID_QUERY, self::$assocHandle);

        Application::$front->setRequest(new TestRequest('/openid/provider?' . $_SERVER['QUERY_STRING']));
        Application::dispatch();

        $this->assertContains(
            'A site identifying as <a href="http://www.example.com">http://www.example.com</a> has asked for confirmation that <a href="'.$this->_user->openid.'">'.$this->_user->openid.'</a> is your identity URL.',
            $this->_response->getBody()
        );
    }

    public function testTrustAction2()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['QUERY_STRING'] = sprintf(self::CHECKID_QUERY, self::$assocHandle);

        Application::$front->setRequest(new TestRequest('/openid/trust?' . $_SERVER['QUERY_STRING']));
        Application::dispatch();

        $this->assertContains(
            'A site identifying as <a href="http://www.example.com">http://www.example.com</a> has asked for confirmation that <a href="'.$this->_user->openid.'">'.$this->_user->openid.'</a> is your identity URL.',
            $this->_response->getBody()
        );
    }

    public function testTrustWithSreg()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['QUERY_STRING'] = sprintf(self::CHECKID_QUERY, self::$assocHandle);
        $_SERVER['QUERY_STRING'] .= '&openid.ns.sreg=http%3A%2F%2Fopenid.net%2Fextensions%2Fsreg%2F1.1&openid.sreg.optional=nickname%2Cmobilenum';

        Application::$front->setRequest(new TestRequest('/openid/trust?' . $_SERVER['QUERY_STRING']));
        Application::dispatch();

        $this->assertContains('<input type="text" name="openid_sreg_nickname" id="openid_sreg_nickname" value=""', $this->_response->getBody());
    }

    public function testProceedAction()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['QUERY_STRING'] = sprintf(self::CHECKID_QUERY, self::$assocHandle);

        // required for logging
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $_POST = array(
            'action'    => 'proceed',
            'allow'     => 'Allow',
        );
        Application::$front->setRequest(new TestRequest('/openid/proceed?' . $_SERVER['QUERY_STRING']));
        Application::dispatch();

        $responseHeaders = $this->_response->getHeaders();

        $this->assertEquals(
            preg_match(
                '#
                        http://www.example.com\?
                        openid.assoc_handle='.self::$assocHandle.'
                        &openid.claimed_id=http%3A%2F%2Flocalhost%2Fcommunityid%2Fidentity%2Ftestuser
                        &openid.identity=http%3A%2F%2Flocalhost%2Fcommunityid%2Fidentity%2Ftestuser
                        &openid.mode=id_res
                        &openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0
                        &openid.op_endpoint=http%3A%2F%2F.*
                        &openid.response_nonce='.gmdate('Y-m-d\T').'.*
                        &openid.return_to=http%3A%2F%2Fwww.example.com
                        &openid.sig=.*
                        &openid.signed=assoc_handle%2Cclaimed_id%2Cidentity%2Cmode%2Cns%2Cop_endpoint%2Cresponse_nonce%2Creturn_to%2Csigned
                #x',
                $responseHeaders[0]['value']
            ),
            1
        );
    }

    public function testProceedWithSreg()
    {
        $_POST = array(
            'openid_sreg_nickname'  => 'nicktest',
            'openid_sreg_email'     => 'test_x@mailinator.com',
            'openid_sreg_fullname'  => 'Michael Jordan',
            'action'                => 'proceed',
            'allow'                 => 'Allow',
        );

        $queryString = self::CHECKID_QUERY . "&openid.ns.sreg=http%%3A%%2F%%2Fopenid.net%%2Fextensions%%2Fsreg%%2F1.1&openid.sreg.required=nickname&openid.sreg.optional=email%%2Cfullname";

        $_SERVER["REQUEST_METHOD"] = 'POST';
        $_SERVER['QUERY_STRING'] = sprintf($queryString, self::$assocHandle);

        // required for logging
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        Application::$front->setRequest(new TestRequest('/openid/proceed?' . $_SERVER['QUERY_STRING']));
        Application::dispatch();

        $responseHeaders = $this->_response->getHeaders();

        $this->assertEquals(
            preg_match(
                '#
                    http://www.example.com\?
                    openid.assoc_handle='.self::$assocHandle.'
                    &openid.claimed_id=http%3A%2F%2Flocalhost%2Fcommunityid%2Fidentity%2Ftestuser
                    &openid.identity=http%3A%2F%2Flocalhost%2Fcommunityid%2Fidentity%2Ftestuser
                    &openid.mode=id_res
                    &openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0
                    &openid.ns.sreg=http%3A%2F%2Fopenid.net%2Fextensions%2Fsreg%2F1.1
                    &openid.op_endpoint=http%3A%2F%2F.*
                    &openid.response_nonce='.gmdate('Y-m-d\T').'.*
                    &openid.return_to=http%3A%2F%2Fwww.example.com
                    &openid.sig=.*
                    &openid.signed=assoc_handle%2Cclaimed_id%2Cidentity%2Cmode%2Cns%2Cns.sreg%2Cop_endpoint%2Cresponse_nonce%2Creturn_to%2Csigned%2Csreg.email%2Csreg.fullname%2Csreg.nickname

                    &openid.sreg.email=test_x%40mailinator.com
                    &openid.sreg.fullname=Michael\+Jordan
                    &openid.sreg.nickname=nicktest
                #x',
                $responseHeaders[0]['value']
            ),
            1
        );
    }

    public function tearDown()
    {
        $users = new Users_Model_Users();
        $this->_user->delete();
    }
}
