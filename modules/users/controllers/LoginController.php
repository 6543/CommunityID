<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class Users_LoginController extends Monkeys_Controller_Action
{
    public function indexAction()
    {
        $settings = new Settings();
        $this->view->maintenanceEnabled = $settings->isMaintenanceMode();

        $appSession = Zend_Registry::get('appSession');
        if (isset($appSession->loginForm)) {
            $this->view->loginForm = $appSession->loginForm;
            unset($appSession->loginForm);
        } else {
            $this->view->loginForm = new LoginForm();
        }

        if ($this->_config->SSL->enable_mixed_mode) {
            $this->view->loginTargetBase = 'https://' . $_SERVER['HTTP_HOST'] . $this->view->base;
        } else {
            $this->view->loginTargetBase = $this->view->base;
        }

        $this->_helper->viewRenderer->setResponseSegment('sidebar');
    }

    public function authenticateAction()
    {
        $auth = Zend_Auth::getInstance();

        $form = new LoginForm();
        $formData = $this->_request->getPost();
        $form->populate($formData);
        $appSession = Zend_Registry::get('appSession');

        if (!$form->isValid($formData)) {
            $appSession->loginForm = $form;
            $this->_redirectToNormalConnection('');
        }

        $db = Zend_Db::factory($this->_config->database);
        $authAdapter = new Zend_Auth_Adapter_DbTable($db, 'users', 'username', 'password', 'MD5(CONCAT(openid, ?))');
        $authAdapter->setIdentity($this->_request->getPost('username'));
        $authAdapter->setCredential($this->_request->getPost('password'));

        $result = $auth->authenticate($authAdapter);

        if ($result->isValid()) {
            $users = new Users();
            $user = $users->getUser($result->getIdentity());

            // $user might not exist when the openid validation passed, but there's no
            // user in the system with that openid identity
            if (!$user) {
                Zend_Auth::getInstance()->clearIdentity();
                $this->_helper->FlashMessenger->addMessage('Invalid credentials');
            } else {
                $auth->getStorage()->write($user);

                if ($user->role != User::ROLE_ADMIN && $this->underMaintenance) {
                    Zend_Auth::getInstance()->clearIdentity();

                    return $this->_redirectForMaintenance(true);
                }
            }
        } else {
            $this->_helper->FlashMessenger->addMessage('Invalid credentials');
            $appSession->loginForm = $form;
        }

        $this->_redirectToNormalConnection('');
    }

    public function logoutAction()
    {
        Zend_Auth::getInstance()->clearIdentity();

        $this->_redirect('');
    }
}
