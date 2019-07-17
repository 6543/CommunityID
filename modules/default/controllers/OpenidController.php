<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class OpenidController extends Monkeys_Controller_Action
{
    public function providerAction()
    {
        if (isset($_POST['action']) && $_POST['action'] == 'proceed') {
            return $this->_proceed();
        } else {
            Zend_OpenId::$exitOnRedirect = false;

            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNeverRender(true);

            $server = $this->_getOpenIdProvider();
            $response = new Zend_Controller_Response_Http();
            $ret = $server->handle(null, new Zend_OpenId_Extension_Sreg(), $response);
            Zend_Registry::get('logger')->log("RET: ".print_r($ret, true), Zend_Log::DEBUG);
            Zend_Registry::get('logger')->log("RESPONSE: ".print_r($response->getHeaders(), true), Zend_Log::DEBUG);
            if (is_string($ret)) {
                echo $ret;
            } else if ($ret !== true) {
                header('HTTP/1.0 403 Forbidden');
                Zend_Registry::get('logger')->log("OpenIdController::providerAction: FORBIDDEN", Zend_Log::DEBUG);
                echo 'Forbidden';
            } elseif ($ret === true
                      // Zend_OpenId is messy and can change the type of the response I initially sent >:|
                      && is_a($response, 'Zend_Controller_Response_Http'))

            {
                $headers = $response->getHeaders();
                if (isset($headers[0]['name']) && $headers[0]['name'] == 'Location'
                    // redirection to the Trust page is not logged
                    && strpos($headers[0]['value'], '/openid/trust') === false
                    && strpos($headers[0]['value'], '/openid/login') === false)
                {
                    if (strpos($headers[0]['value'], 'openid.mode=cancel') !== false) {
                        $this->_saveHistory($server, History::DENIED);
                    } else {
                        $this->_saveHistory($server, History::AUTHORIZED);
                    }
                }
            }
        }
    }

    public function loginAction()
    {
        $appSession = Zend_Registry::get('appSession');
        if (isset($appSession->openidLoginForm)) {
            $this->view->form = $appSession->openidLoginForm;
            unset($appSession->openidLoginForm);
        } else {
            $this->view->form = new OpenidLoginForm();
        }
        $this->view->form->openIdIdentity->setValue(htmlspecialchars($_GET['openid_identity']));

        $this->view->queryString = $_SERVER['QUERY_STRING'];
    }

    public function authenticateAction()
    {
        $form = new OpenidLoginForm();
        $formData = $this->_request->getPost();
        $form->populate($formData);

        if (!$form->isValid($formData)) {
            $appSession = Zend_Registry::get('appSession');
            $appSession->openidLoginForm = $form;
            return $this->_forward('login', null, null);
        }

        $server = $this->_getOpenIdProvider();
        $server->login($form->getValue('openIdIdentity'), $form->getValue('password'));

        // needed for unit tests
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNeverRender(true);

        Zend_OpenId::redirect($this->view->base . '/openid/provider', $_GET);
    }

    public function trustAction()
    {
        $server = $this->_getOpenIdProvider();
        $this->view->siteRoot = $server->getSiteRoot($_GET);
        $this->view->identityUrl = $server->getLoggedInUser($_GET);
        $this->view->queryString = $_SERVER['QUERY_STRING'];

        $sreg = new Zend_OpenId_Extension_Sreg();
        $sreg->parseRequest($_GET);

        $this->view->fields = array();
        $this->view->policyUrl = false;

        $props = $sreg->getProperties();
        if (is_array($props) && count($props) > 0) {
            $personalInfoForm = new PersonalInfoForm(null, $this->user, $props);
            $this->view->fields = $personalInfoForm->getElements();

            $policy = $sreg->getPolicyUrl();
            if (!empty($policy)) {
                $this->view->policyUrl = $policy;
            }
        }
    }

    private function _proceed()
    {
        if ($this->user->role == User::ROLE_GUEST) {
            throw new Monkeys_AccessDeniedException();
        }

        // needed for unit tests
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNeverRender(true);

        $server = $this->_getOpenIdProvider();

        $sreg = new Zend_OpenId_Extension_Sreg();
        $sreg->parseRequest($_GET);
        $props = $sreg->getProperties();

        $personalInfoForm = new PersonalInfoForm(null, $this->user, $props);
        $formData = $this->_request->getPost();
        $personalInfoForm->populate($formData);

        // not planning on validating stuff here yet, but I call this
        // for the date element to be filled properly
        $personalInfoForm->isValid($formData);

        $sreg->parseResponse($personalInfoForm->getValues());
        if (isset($_POST['allow'])) {
            if (isset($_POST['forever'])) {
                $server->allowSite($server->getSiteRoot($_GET), $sreg);
            }
            unset($_GET['openid_action']);

            $this->_saveHistory($server, History::AUTHORIZED);

            $server->respondToConsumer($_GET, $sreg);
        } else if (isset($_POST['deny'])) {
            if (isset($_POST['forever'])) {
                $server->denySite($server->getSiteRoot($_GET));
            }

            $this->_saveHistory($server, History::DENIED);

            Zend_OpenId::redirect($_GET['openid_return_to'], array('openid.mode'=>'cancel'));
        }
    }
    private function _saveHistory(Zend_OpenId_Provider $server, $result)
    {
        // only log if user exists
        if ($this->user->role == User::ROLE_GUEST) {
            return;
        }

        $histories = new Histories();
        $history = $histories->createRow();
        $history->user_id = $this->user->id;
        $history->date = date('Y-m-d H:i:s');
        $history->site = $server->getSiteRoot($_GET);
        $history->ip = $_SERVER['REMOTE_ADDR'];
        $history->result = $result;
        $history->save();
    }

    private function _getOpenIdProvider()
    {
        $server = new Zend_OpenId_Provider($this->view->base . '/openid/login',
                                           $this->view->base . '/openid/trust',
                                           new OpenIdUser(),
                                           new Monkeys_OpenId_Provider_Storage_Database());

        return $server;
    }
}
