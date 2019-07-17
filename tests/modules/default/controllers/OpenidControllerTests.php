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
    const CHECKID_QUERY = 'openid.ns=http%%3A%%2F%%2Fspecs.openid.net%%2Fauth%%2F2.0&openid.mode=checkid_setup&openid.identity=http%%3A%%2F%%2Flocalhost%%2Fcommunityid%%2Fidentity%%2Ftestuser&openid.claimed_id=http%%3A%%2F%%2Flocalhost%%2Fcommunityid%%2Fidentity%%2Ftestuser&openid.assoc_handle=%s&openid.return_to=http%%3A%%2F%%2Fwww%%2Eexample%%2Ecom&openid%%2Erealm=http%%3A%%2F%%2Fwww%%2Eexample%%2Ecom';

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
        Application::$front->setResponse($this->_response);

        $users = new Users_Model_Users();
        $this->_user = $users->createRow();
        $this->_user->test = 1;
        $this->_user->username = 'testuser';
        $this->_user->role = Users_Model_User::ROLE_REGISTERED;
        $this->_user->openid = 'http://localhost/communityid/identity/'.$this->_user->username;
        $this->_user->accepted_eula = 1;
        $this->_user->firstname = 'firstnametest';
        $this->_user->lastname = 'lastnametest';
        $this->_user->email = 'usertest@mailinator.com';
        $this->_user->token = '';
        $this->_user->save();
        Zend_Registry::set('user', $this->_user);
    }

    /**
    * @expectedException Monkeys_BadUrlException
    */
    public function testIndexAction()
    {
        Application::$front->setRequest(new TestRequest('/openid'));
        Application::dispatch();
    }

    public function testProviderAssociateAction()
    {
        $_POST = array(
            'openid_ns'                 => 'http://specs.openid.net/auth/2.0',
            'openid_mode'               => 'associate',
            'openid_assoc_type'         => 'HMAC-SHA256',
            'openid_session_type'       => 'DH-SHA256',
            'openid_dh_modulus'         => 'ANz5OguIOXLsDhmYmsWizjEOHTdxfo2Vcbt2I3MYZuYe91ouJ4mLBX+YkcLiemOcPym2CBRYHNOyyjmG0mg3BVd9RcLn5S3IHHoXGHblzqdLFEi/368Ygo79JRnxTkXjgmY0rxlJ5bU1zIKaSDuKdiI+XUkKJX8Fvf8W8vsixYOr',
            'openid_dh_gen'             => 'Ag==',
            'openid_dh_consumer_public' => 'MFzHUMsSa4YSQ3JrcPSqyUaTQ3Z+QWKH6knvrREW7b6zQ2qMdOrpckgnUgo0pILMQpls8Ty/3JDv+IO29qASk2PwwZwxC2kXK/MQC/om5gs/IpjPSw1wK4bz2QTUHTRSxmtTxiq0tHYmIIqadz4TTMfXohMU2VCuYBqDNMHZFpk=',
        );

        // needed by Zend_OpenId_Provider
        $_SERVER["REQUEST_METHOD"] = 'POST';

        Application::$front->setRequest(new TestRequest('/openid/provider'));
        Application::dispatch();

        $this->assertEquals(
            preg_match(
                "%
                    ns:http://specs\.openid\.net/auth/2\.0\\x0A
                    assoc_type:HMAC-SHA256\\x0A
                    session_type:DH-SHA256\\x0A
                    dh_server_public:.*\\x0A
                    enc_mac_key:.*\\x0A
                    assoc_handle:([a-f0-9]+)\\x0A
                    expires_in:3600\\x0A
                %x",
                $this->_response->getBody(),
                $matches
            ),
            1
        );
        self::$assocHandle = $matches[1];
    }

    public function testProviderCheckidSetupAction()
    {
        // needed by Zend_OpenId_Provider
        $_SERVER["REQUEST_METHOD"] = 'GET';

        Zend_OpenId::$exitOnRedirect = false;

        Application::$front->setRequest(new TestRequest('/openid/provider?openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0&openid.mode=checkid_setup&openid.identity=http%3A%2F%2Flocalhost%2Fcommunityid%2Fidentity%2Ftestuser&openid.claimed_id=http%3A%2F%2Flocalhost%2Fcommunityid%2Fidentity%2Ftestuser&openid.assoc_handle='.self::$assocHandle.'&openid.return_to=http%3A%2F%2Fwww%2Eexample%2Ecom&openid.realm=http%3A%2F%2Fwww%2Eexample%2Ecom'));
        Application::dispatch();

        $this->assertEquals(
            preg_match(
                '#
                    <script\ language="JavaScript"\ type="text/javascript">window\.location=\'http://.*/communityid/openid/login\?
                        openid\.ns=http%3A%2F%2Fspecs\.openid\.net%2Fauth%2F2\.0
                        &openid\.mode=checkid_setup
                        &openid\.identity=http%3A%2F%2Flocalhost%2Fcommunityid%2Fidentity%2Ftestuser
                        &openid\.claimed_id=http%3A%2F%2Flocalhost%2Fcommunityid%2Fidentity%2Ftestuser
                        &openid\.assoc_handle='.self::$assocHandle.'
                        &openid\.return_to=http%3A%2F%2Fwww\.example\.com
                        &openid\.realm=http%3A%2F%2Fwww\.example\.com
                    \';</script>
                #x',
                $this->_response->getBody()
            ),
            1
        );
    }

    public function testLoginAction()
    {
        $_SERVER['QUERY_STRING'] = sprintf(self::CHECKID_QUERY, self::$assocHandle);

        Application::$front->setRequest(new TestRequest('/openid/login?' . $_SERVER['QUERY_STRING']));
        Application::dispatch();

        $this->assertContains('<form action="authenticate?'.$_SERVER['QUERY_STRING'].'" method="post" class="formGrid">', $this->_response->getBody());
    }
    
    public function testAuthenticateEmptyUsernameAction()
    {
        $_SERVER['QUERY_STRING'] = sprintf(self::CHECKID_QUERY, self::$assocHandle);

        $_POST = array(
            'openIdIdentity'    => '',
            'password'          => 'whateva',
        );

        Application::$front->setRequest(new TestRequest('/openid/authenticate?' . $_SERVER['QUERY_STRING']));
        Application::dispatch();

        $this->assertContains('Login', $this->_response->getBody());
    }

    public function testAuthenticateBadUsernameAction()
    {
        $_SERVER['QUERY_STRING'] = sprintf(self::CHECKID_QUERY, self::$assocHandle);

        $_POST = array(
            'openIdIdentity'    => 'whateva',
            'password'          => 'whatevaagain',
        );

        Zend_OpenId::$exitOnRedirect = false;

        Application::$front->setRequest(new TestRequest('/openid/authenticate?' . $_SERVER['QUERY_STRING']));
        Application::dispatch();

        $this->assertEquals(
            preg_match(
                '#
                    <script\ language="JavaScript"\ type="text/javascript">window\.location=\'http://.*/communityid/openid/provider\?
                        openid_ns=http%3A%2F%2Fspecs\.openid\.net%2Fauth%2F2\.0
                        &openid_mode=checkid_setup
                        &openid_identity=http%3A%2F%2Flocalhost%2Fcommunityid%2Fidentity%2Ftestuser
                        &openid_claimed_id=http%3A%2F%2Flocalhost%2Fcommunityid%2Fidentity%2Ftestuser
                        &openid_assoc_handle='.self::$assocHandle.'
                        &openid_return_to=http%3A%2F%2Fwww\.example\.com
                        &openid_realm=http%3A%2F%2Fwww\.example\.com
                    \';</script>
                #x',
                $this->_response->getBody()
            ),
            1
        );
    }

    public function testAuthenticateBadPasswordAction()
    {
        $_SERVER['QUERY_STRING'] = sprintf(self::CHECKID_QUERY, self::$assocHandle);

        $_POST = array(
            'openIdIdentity'    => $this->_user->openid,
            'password'          => 'whateva',
        );

        Zend_OpenId::$exitOnRedirect = false;

        Application::$front->setRequest(new TestRequest('/openid/authenticate?' . $_SERVER['QUERY_STRING']));
        Application::dispatch();

        $this->assertEquals(
            preg_match(
                '#
                    <script\ language="JavaScript"\ type="text/javascript">window\.location=\'http://.*/communityid/openid/provider\?
                        openid_ns=http%3A%2F%2Fspecs\.openid\.net%2Fauth%2F2\.0
                        &openid_mode=checkid_setup
                        &openid_identity=http%3A%2F%2Flocalhost%2Fcommunityid%2Fidentity%2Ftestuser
                        &openid_claimed_id=http%3A%2F%2Flocalhost%2Fcommunityid%2Fidentity%2Ftestuser
                        &openid_assoc_handle='.self::$assocHandle.'
                        &openid_return_to=http%3A%2F%2Fwww\.example\.com
                        &openid_realm=http%3A%2F%2Fwww\.example\.com
                    \';</script>
                #x',
                $this->_response->getBody()
            ),
            1
        );
    }

    public function testAuthenticateSuccessfulAction()
    {
        $_SERVER['QUERY_STRING'] = sprintf(self::CHECKID_QUERY, self::$assocHandle);

        $_POST = array(
            'openIdIdentity'    => $this->_user->openid,
            'password'          => 'm',
        );

        Zend_OpenId::$exitOnRedirect = false;

        Application::$front->setRequest(new TestRequest('/openid/authenticate?' . $_SERVER['QUERY_STRING']));
        Application::dispatch();

        $this->assertEquals(
            preg_match(
                '#
                    <script\ language="JavaScript"\ type="text/javascript">window\.location=\'http://.*/communityid/openid/provider\?
                        openid_ns=http%3A%2F%2Fspecs\.openid\.net%2Fauth%2F2\.0
                        &openid_mode=checkid_setup
                        &openid_identity=http%3A%2F%2Flocalhost%2Fcommunityid%2Fidentity%2Ftestuser
                        &openid_claimed_id=http%3A%2F%2Flocalhost%2Fcommunityid%2Fidentity%2Ftestuser
                        &openid_assoc_handle='.self::$assocHandle.'
                        &openid_return_to=http%3A%2F%2Fwww\.example\.com
                        &openid_realm=http%3A%2F%2Fwww\.example\.com
                    \';</script>
                #x',
                $this->_response->getBody()
            ),
            1
        );
    }

    public function testTrustAction1()
    {
        $openIdUser = new Users_Model_OpenIdUser();
        $openIdUser->setLoggedInUser($this->_user->openid);

        $_SERVER['QUERY_STRING'] = sprintf(self::CHECKID_QUERY, self::$assocHandle);

        // needed by Zend_OpenId_Provider
        $_SERVER["REQUEST_METHOD"] = 'GET';

        Zend_OpenId::$exitOnRedirect = false;

        Application::$front->setRequest(new TestRequest('/openid/provider?' . $_SERVER['QUERY_STRING']));
        Application::dispatch();

        $this->assertEquals(
            preg_match(
                '#
                    <script\ language="JavaScript"\ type="text/javascript">window\.location=\'http://.*/communityid/openid/trust\?
                        openid.ns=http%3A%2F%2Fspecs\.openid\.net%2Fauth%2F2\.0
                        &openid.mode=checkid_setup
                        &openid.identity=http%3A%2F%2Flocalhost%2Fcommunityid%2Fidentity%2Ftestuser
                        &openid.claimed_id=http%3A%2F%2Flocalhost%2Fcommunityid%2Fidentity%2Ftestuser
                        &openid.assoc_handle='.self::$assocHandle.'
                        &openid.return_to=http%3A%2F%2Fwww\.example\.com
                        &openid.realm=http%3A%2F%2Fwww\.example\.com
                    \';</script>
                #x',
                $this->_response->getBody()
            ),
            1
        );
    }

    public function testTrustAction2()
    {
        $openIdUser = new Users_Model_OpenIdUser();
        $openIdUser->setLoggedInUser($this->_user->openid);

        $_SERVER['QUERY_STRING'] = sprintf(self::CHECKID_QUERY, self::$assocHandle);

        Application::$front->setRequest(new TestRequest('/openid/trust?' . $_SERVER['QUERY_STRING']));
        Application::dispatch();

        $this->assertContains(
            'A site identifying as <a href="http://www.example.com/">http://www.example.com/</a> has asked for confirmation that <a href="'.$this->_user->openid.'">'.$this->_user->openid.'</a> is your identity URL.',
            $this->_response->getBody()
        );
    }

    public function testProviderProceedAction()
    {
        $openIdUser = new Users_Model_OpenIdUser();
        $openIdUser->setLoggedInUser($this->_user->openid);

        $_SERVER['QUERY_STRING'] = sprintf(self::CHECKID_QUERY, self::$assocHandle);

        // required for logging
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        Zend_OpenId::$exitOnRedirect = false;

        $_POST = array(
            'action'    => 'proceed',
            'allow'     => 'Allow',
        );
        Application::$front->setRequest(new TestRequest('/openid/provider?' . $_SERVER['QUERY_STRING']));
        Application::dispatch();

        $this->assertEquals(
            preg_match(
                '#
                    <script\ language="JavaScript"\ type="text/javascript">window\.location=\'http://www.example.com\?
                        openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0
                        &openid.assoc_handle='.self::$assocHandle.'
                        &openid.return_to=http%3A%2F%2Fwww.example.com
                        &openid.claimed_id=http%3A%2F%2Flocalhost%2Fcommunityid%2Fidentity%2Ftestuser
                        &openid.identity=http%3A%2F%2Flocalhost%2Fcommunityid%2Fidentity%2Ftestuser
                        &openid.op_endpoint=http%3A%2F%2F.*
                        &openid.response_nonce='.gmdate('Y-m-d\T').'.*
                        &openid.mode=id_res
                        &openid.signed=ns%2Cassoc_handle%2Creturn_to%2Cclaimed_id%2Cidentity%2Cop_endpoint%2Cresponse_nonce%2Cmode%2Csigned
                        &openid.sig=.*
                    \';</script>
                #x',
                $this->_response->getBody()
            ),
            1
        );
    }

    public function testAlreadyTrustedWithSreg()
    {
        $sregData = array(
            'nickname'  => 'nicktest',
            'email'     => 'test_x@mailinator.com',
            'fullname'  => 'Michael Jordan',
        );
        $sreg = new Zend_OpenId_Extension_Sreg($sregData);
        $storage = new Monkeys_OpenId_Provider_Storage_Database();
        $storage->addSite($this->_user->openid, 'http://www.example.com', array('Zend_OpenId_Extension_Sreg' => $sregData));
        $openIdUser = new Users_Model_OpenIdUser();
        $openIdUser->setLoggedInUser($this->_user->openid);

        $queryString = self::CHECKID_QUERY . "&openid.ns.sreg=http%%3A%%2F%%2Fopenid.net%%2Fextensions%%2Fsreg%%2F1.1&openid.sreg.required=nickname&openid.sreg.optional=email%%2Cfullname";

        $_SERVER['QUERY_STRING'] = sprintf($queryString, self::$assocHandle);

        // required for logging
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        // needed by Zend_OpenId_Provider
        $_SERVER["REQUEST_METHOD"] = 'GET';

        Zend_OpenId::$exitOnRedirect = false;

        Application::$front->setRequest(new TestRequest('/openid/provider?' . $_SERVER['QUERY_STRING']));
        Application::dispatch();

        $this->assertEquals(
            preg_match(
                '#
                    <script\ language="JavaScript"\ type="text/javascript">window\.location=\'http://www.example.com\?
                        openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0
                        &openid.assoc_handle='.self::$assocHandle.'
                        &openid.return_to=http%3A%2F%2Fwww.example.com
                        &openid.claimed_id=http%3A%2F%2Flocalhost%2Fcommunityid%2Fidentity%2Ftestuser
                        &openid.identity=http%3A%2F%2Flocalhost%2Fcommunityid%2Fidentity%2Ftestuser
                        &openid.op_endpoint=http%3A%2F%2F.*
                        &openid.response_nonce='.gmdate('Y-m-d\T').'.*
                        &openid.mode=id_res
                        &openid.ns.sreg=http%3A%2F%2Fopenid.net%2Fextensions%2Fsreg%2F1.1
                        &openid.sreg.nickname=nicktest
                        &openid.sreg.email=test_x%40mailinator.com
                        &openid.sreg.fullname=Michael\+Jordan
                        &openid.signed=ns%2Cassoc_handle%2Creturn_to%2Cclaimed_id%2Cidentity%2Cop_endpoint%2Cresponse_nonce%2Cmode%2Cns.sreg%2Csreg.nickname%2Csreg.email%2Csreg.fullname%2Csigned
                        &openid.sig=.*
                    \';</script>
                #x',
                $this->_response->getBody()
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
