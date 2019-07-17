<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class OpenidController extends CommunityID_Controller_Action
{
    protected $_numCols = 1;

    public function providerAction()
    {
        $server = $this->_getOpenIdProvider();
        $request = $server->decodeRequest();
        $sites = new Model_Sites();

        if (!$request) {
            $this->_helper->viewRenderer->setNeverRender(true);
            $this->_response->setRawHeader('HTTP/1.0 403 Forbidden');
            Zend_Registry::get('logger')->log("OpenIdController::providerAction: FORBIDDEN", Zend_Log::DEBUG);
            echo 'Forbidden';
            return;
        }

        // association and other transactions, handled automatically by the framework
        if (!in_array($request->mode, array('checkid_immediate', 'checkid_setup'))) {
            return $this->_sendResponse($server, $server->handleRequest($request));
        }

        // can't process immediate requests if user is not logged in
        if ($request->immediate && $this->user->role == Users_Model_User::ROLE_GUEST) {
            return $this->_sendResponse($server, $request->answer(false));
        }

        if ($request->idSelect()) {
            if ($this->user->role == Users_Model_User::ROLE_GUEST) {
                $this->_forward('login');
            } else {
                if ($sites->isTrusted($this->user, $request->trust_root)) {
                    $this->_forward('proceed', null, null, array('allow' => true));
                } elseif ($sites->isNeverTrusted($this->user, $request->trust_root)) {
                    $this->_forward('proceed', null, null, array('allow' => false));
                } else {
                    if ($request->immediate) {
                        return $this->_sendResponse($server, $request->answer(false));
                    }

                    $this->_forward('trust');
                }
            }
        } else {
            if (!$request->identity) {
                die('No identifier sent by OpenID relay');
            }

            if ($this->user->role == Users_Model_User::ROLE_GUEST) {
                $this->_forward('login');
            } else {
                // user is logged-in already. Check the requested identity is his
                if ($this->user->openid != $request->identity) {
                    Zend_Auth::getInstance()->clearIdentity();
                    if ($this->immediate) {
                        return $this->_sendResponse($server, $request->answer(false));
                    }

                    $this->_forward('login');
                } else {
                    if ($sites->isTrusted($this->user, $request->trust_root)) {
                        $this->_forward('proceed', null, null, array('allow' => true));
                    } elseif ($sites->isNeverTrusted($this->user, $request->trust_root)) {
                        $this->_forward('proceed', null, null, array('deny' => true));
                    } else {
                        $this->_forward('trust');
                    }
                }
            }
        }
    }

    /**
    * We don't use the session with the login form to simplify the dynamic appearance of the captcha
    */
    public function loginAction()
    {
        $server = $this->_getOpenIdProvider();
        $request = $server->decodeRequest();

        $authAttempts = new Users_Model_AuthAttempts();
        $attempt = $authAttempts->get();
        $this->view->useCaptcha = $attempt && $attempt->surpassedMaxAllowed();
        $this->view->form = new Form_OpenidLogin(null, $this->view->base, $attempt && $attempt->surpassedMaxAllowed());

        if (!$request->idSelect()) {
            $this->view->form->openIdIdentity->setValue(htmlspecialchars($request->identity));
        }

        $this->view->queryString = $this->_queryString();
    }

    public function authenticateAction()
    {
        $server = $this->_getOpenIdProvider();
        $request = $server->decodeRequest();

        $authAttempts = new Users_Model_AuthAttempts();
        $attempt = $authAttempts->get();

        $form = new Form_OpenidLogin(null, $this->view->base, $attempt && $attempt->surpassedMaxAllowed());
        $formData = $this->_request->getPost();
        $form->populate($formData);

        if (!$form->isValid($formData)) {
            $this->_forward('login');
            return;
        }

        $users = new Users_Model_Users();
        $result = $users->authenticate($form->getValue('openIdIdentity'),
            $form->getValue('password'), true);

        if ($result) {
            if ($attempt) {
                $attempt->delete();
            }
            $sites = new Model_Sites();
            if ($sites->isTrusted($users->getUser(), $request->trust_root)) {
                $this->_forward('proceed', null, null, array('allow' => true));
            } elseif ($sites->isNeverTrusted($users->getUser(), $request->trust_root)) {
                $this->_forward('proceed', null, null, array('deny' => true));
            } else {
                $this->_forward('trust');
            }
        } else {
            if (!$attempt) {
                $authAttempts->create();
            } else {
                $attempt->addFailure();
                $attempt->save();
            }
            $this->_forward('login');
        }
    }

    public function trustAction()
    {
        $server = $this->_getOpenIdProvider();
        $request = $server->decodeRequest();

        $this->view->siteRoot = $request->trust_root;
        $this->view->identityUrl = $this->user->openid;
        $this->view->queryString = $this->_queryString();

        $this->view->fields = array();
        $this->view->policyUrl = false;

        // The class Auth_OpenID_SRegRequest is included in the following file
        require_once 'libs/Auth/OpenID/SReg.php';

        $sregRequest = Auth_OpenID_SRegRequest::fromOpenIDRequest($request);
        $props = $sregRequest->allRequestedFields();
        $args  = $sregRequest->getExtensionArgs();
        if (isset($args['required'])) {
            $required = explode(',', $args['required']);
        } else {
            $required = false;
        }

        if (is_array($props) && count($props) > 0) {
            $sregProps = array();
            foreach ($props as $field) {
                $sregProps[$field] = $required && in_array($field, $required);
            }

            $personalInfoForm = new Users_Form_PersonalInfo(null, $this->user, $sregProps);
            $this->view->fields = $personalInfoForm->getElements();

            if (isset($args['policy_url'])) {
                $this->view->policyUrl = $args['policy_url'];
            }
        }
    }

    public function proceedAction()
    {
        // needed for unit tests
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNeverRender(true);

        $server = $this->_getOpenIdProvider();
        $request = $server->decodeRequest();

        if ($request->idSelect()) {
            $id = $this->user->openid;
        } else {
            $id = null;
        }

        $response = $request->answer(true, null, $id);

        // The class Auth_OpenID_SRegRequest is included in the following file
        require_once 'libs/Auth/OpenID/SReg.php';

        $sregRequest = Auth_OpenID_SRegRequest::fromOpenIDRequest($request);
        $props = $sregRequest->allRequestedFields();
        $args  = $sregRequest->getExtensionArgs();
        if (isset($args['required'])) {
            $required = explode(',', $args['required']);
        } else {
            $required = false;
        }

        if (is_array($props) && count($props) > 0) {
            $sregProps = array();
            foreach ($props as $field) {
                $sregProps[$field] = $required && in_array($field, $required);
            }

            $personalInfoForm = new Users_Form_PersonalInfo(null, $this->user, $sregProps);
            $formData = $this->_request->getPost();
            $personalInfoForm->populate($formData);

            // not planning on validating stuff here yet, but I call this
            // for the date element to be filled properly
            $foo = $personalInfoForm->isValid($formData);

            $sregResponse = Auth_OpenID_SRegResponse::extractResponse($sregRequest,
                $personalInfoForm->getUnqualifiedValues());
            $sregResponse->toMessage($response->fields);
        }

        if ($this->_getParam('allow')) {
            if ($this->_getParam('forever')) {

                $sites = new Model_Sites();
                $sites->deleteForUserSite($this->user, $request->trust_root);

                $siteObj = $sites->createRow();
                $siteObj->user_id = $this->user->id;
                $siteObj->site = $request->trust_root;
                $siteObj->creation_date = date('Y-m-d');

                if (isset($personalInfoForm)) {
                    $trusted = array();
                    // using this key name for BC pre 1.1 when we used Zend_OpenId
                    $trusted['Zend_OpenId_Extension_Sreg'] = $personalInfoForm->getUnqualifiedValues();
                } else {
                    $trusted = true;
                }
                $siteObj->trusted = serialize($trusted);

                $siteObj->save();
            }

            $this->_saveHistory($request->trust_root, Model_History::AUTHORIZED);

            $webresponse = $server->encodeResponse($response);

            foreach ($webresponse->headers as $k => $v) {
                if ($k == 'location') {
                    $this->_response->setRedirect($v);
                } else {
                    $this->_response->setHeader($k, $v);
                }
            }

            $this->_response->setHeader('Connection', 'close');
            $this->_response->appendBody($webresponse->body);
        } elseif ($this->_getParam('deny')) {
            if ($this->_getParam('forever')) {
                $sites = new Model_Sites();
                $sites->deleteForUserSite($this->user, $request->trust_root);

                $siteObj = $sites->createRow();
                $siteObj->user_id = $this->user->id;
                $siteObj->site = $request->trust_root;
                $siteObj->creation_date = date('Y-m-d');
                $siteObj->trusted = serialize(false);
                $siteObj->save();
            }

            $this->_saveHistory($request->trust_root, Model_History::DENIED);

            return $this->_sendResponse($server, $request->answer(false));
        }
    }

    private function _saveHistory($site, $result)
    {
        $histories = new Model_Histories();
        $history = $histories->createRow();
        $history->user_id = $this->user->id;
        $history->date = date('Y-m-d H:i:s');
        $history->site = $site;
        $history->ip = $_SERVER['REMOTE_ADDR'];
        $history->result = $result;
        $history->save();
    }

    private function _getOpenIdProvider()
    {
        $connection = new CommunityID_OpenId_DatabaseConnection(Zend_Registry::get('db'));
        $store = new Auth_OpenID_MySQLStore($connection, 'associations', 'nonces');
        $server = new Auth_OpenID_Server($store, $this->_helper->ProviderUrl($this->_config));

        return $server;
    }

    private function _sendResponse(Auth_OpenID_Server $server, Auth_OpenID_ServerResponse $response)
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNeverRender(true);

        $webresponse = $server->encodeResponse($response);

        if ($webresponse->code != AUTH_OPENID_HTTP_OK) {
            $this->_response->setRawHeader(sprintf("HTTP/1.1 %d ", $webresponse->code), true, $webresponse->code);
        }

        foreach ($webresponse->headers as $k => $v) {
            if ($k == 'location') {
                $this->_response->setRedirect($v);
            } else {
                $this->_response->setHeader($k, $v);
            }
        }

        $this->_response->setHeader('Connection', 'close');

        $this->_response->appendBody($webresponse->body);
    }


    /**
    * Circumvent PHP's automatic replacement of dots by underscore in var names in $_GET and $_POST
    */
    private function _queryString()
    {
        $unfilteredVars = array_merge($_GET, $_POST);
        $varsTemp = array();
        $vars = array();
        $extensions = array();
        foreach ($unfilteredVars as $key => $value) {
            if (substr($key, 0, 10) == 'openid_ns_') {
                $extensions[] = substr($key, 10);
                $varsTemp[str_replace('openid_ns_', 'openid.ns.', $key)] = $value;
            } else {
                $varsTemp[str_replace('openid_', 'openid.', $key)] = $value;
            }
        }
        foreach ($extensions as $extension) {
            foreach ($varsTemp as $key => $value) {
                if (strpos($key, "openid.$extension") === 0) {
                    $prefix = "openid.$extension.";
                    $key = $prefix . substr($key, strlen($prefix));
                }
                $vars[$key] = $value;
            }
        }
        if (!$extensions) {
            $vars = $varsTemp;
        }

        return '?' . http_build_query($vars);
    }
}
