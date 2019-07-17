<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class Users_RegisterController extends Monkeys_Controller_Action
{
    protected $_numCols = 1;

    public function init()
    {
        parent::init();

        if ($this->user->role != User::ROLE_ADMIN && $this->underMaintenance) {
            return $this->_redirectForMaintenance();
        }

        if (!$this->_config->environment->registrations_enabled) {
            $this->_helper->FlashMessenger->addMessage($this->view->translate(
                'Sorry, registrations are currently disabled'
            ));
            return $this->_redirect('');
        }
    }

    public function indexAction()
    {
        $appSession = Zend_Registry::get('appSession');
        if (isset($appSession->registerForm)) {
            $form = $appSession->registerForm;
            unset($appSession->registerForm);
        } else {
            $form = new RegisterForm(null, $this->view->base);
        }
        $this->view->form = $form;
    }
    
    public function saveAction()
    {
        $form = new RegisterForm(null, $this->view->base);
        $formData = $this->_request->getPost();
        $form->populate($formData);

        if (!$form->isValid($formData)) {
            $appSession = Zend_Registry::get('appSession');
            $appSession->registerForm = $form;
            return $this->_forward('index', null, null);
        }

        $users = new Users();

        if ($users->getUser($form->getValue('username'))) {
            $form->username->addError($this->view->translate('This username is already in use'));
            $appSession = Zend_Registry::get('appSession');
            $appSession->registerForm = $form;
            return $this->_forward('index', null, null);
        }

        if ($users->getUserWithEmail($form->getValue('email'))) {
            $form->email->addError($this->view->translate('This E-mail is already in use'));
            $appSession = Zend_Registry::get('appSession');
            $appSession->registerForm = $form;
            return $this->_forward('index', null, null);
        }

        $user = $users->createRow();

        $user->firstname = $form->getValue('firstname');
        $user->lastname = $form->getValue('lastname');
        $user->email = $form->getValue('email');
        $user->username = $form->getValue('username');

        $currentUrl = Zend_OpenId::selfURL();
        preg_match('#(.*)/users/register/save#', $currentUrl, $matches);
        if ($this->_config->subdomain->enabled) {
            $openid = $this->_getProtocol() . '://' . $user->username . '.' . $this->_config->subdomain->hostname;
        } else {
            $openid = $matches[1] . '/identity/' . $user->username;
        }

        if ($this->_config->SSL->enable_mixed_mode) {
            $openid = str_replace('http://', 'https://', $openid);
        }
        Zend_OpenId::normalizeUrl($openid);
        $user->openid = $openid;

        $user->setClearPassword($form->getValue('password1'));
        $user->role = User::ROLE_GUEST;
        $registrationToken = User::generateToken();
        $user->token = $registrationToken;
        $user->accepted_eula = 0;
        $user->registration_date = date('Y-m-d');
        $user->save();

        $mail = $this->getMail($user);
        try {
            $mail->send();
            $this->_helper->FlashMessenger->addMessage($this->view->translate('Thank you.'));
            $this->_helper->FlashMessenger->addMessage($this->view->translate('You will receive an E-mail with instructions to activate the account.'));
        } catch (Zend_Mail_Protocol_Exception $e) {
            $this->_helper->FlashMessenger->addMessage($this->view->translate('The account was created but the E-mail could not be sent'));
            if ($this->_config->logging->level == Zend_Log::DEBUG) {
                $this->_helper->FlashMessenger->addMessage($e->getMessage());
            }
        }

        $this->_redirect('');
    }

    public function eulaAction()
    {
        $users = new Users();
        if ($this->_request->getParam('token') == ''
                || !($user = $users->getUserWithToken($this->_request->getParam('token')))) {
            $this->_helper->FlashMessenger->addMessage('Invalid token');
            $this->_redirect('');
        }

        $this->view->token = $user->token;

        $locale = Zend_Registry::get('Zend_Locale');
        $localeElements = explode('_', $locale);

        if (file_exists(APP_DIR . "/resources/$locale/eula.txt")) {
            $file = APP_DIR . "/resources/$locale/eula.txt";
        } else if (count($localeElements == 2)
                && file_exists(APP_DIR . "/resources/".$localeElements[0]."/eula.txt")) {
            $file = APP_DIR . "/resources/".$localeElements[0]."/eula.txt";
        } else {
            $file = APP_DIR . "/resources/en/eula.txt";
        }

        $this->view->eula = file_get_contents($file);
    }

    public function declineeulaAction()
    {
        $users = new Users();
        if (!($user = $users->getUserWithToken($this->_request->getParam('token'))) || $this->_request->getParam('token') == '') {
            Zend_Registry::get('logger')->log('invalid token', Zend_Log::DEBUG);
            $this->_helper->FlashMessenger->addMessage('Invalid token');
            $this->_redirect('');
        }

        $user->delete();
        $this->_helper->FlashMessenger->addMessage('Your account has been deleted');
        $this->_redirect('');
    }

    public function accepteulaAction()
    {
        $users = new Users();
        if (!($user = $users->getUserWithToken($this->_request->getParam('token'))) || $this->_request->getParam('token') == '') {
            $this->_helper->FlashMessenger->addMessage('Invalid token');
            $this->_redirect('');
        }

        $user->role = User::ROLE_REGISTERED;
        $user->accepted_eula = 1;
        $user->registration_date = date('Y-m-d');
        $user->token = '';
        $user->save();

        $auth = Zend_Auth::getInstance();
        $auth->getStorage()->write($user);

        $this->_redirect('/users/profile');
    }

    /**
    * @return Zend_Mail
    * @throws Zend_Mail_Protocol_Exception
    */
    public function getMail(User $user)
    {
        $locale = Zend_Registry::get('Zend_Locale');
        $localeElements = explode('_', $locale);
        if (file_exists(APP_DIR . "/resources/$locale/registration_mail.txt")) {
            $file = APP_DIR . "/resources/$locale/registration_mail.txt";
        } else if (count($localeElements == 2)
                && file_exists(APP_DIR . "/resources/".$localeElements[0]."/registration_mail.txt")) {
            $file = APP_DIR . "/resources/".$localeElements[0]."/registration_mail.txt";
        } else {
            $file = APP_DIR . "/resources/en/registration_mail.txt";
        }

        $emailTemplate = file_get_contents($file);
        $emailTemplate = str_replace('{userName}', $user->getFullName(), $emailTemplate);

        $currentUrl = Zend_OpenId::selfURL();
        preg_match('#(.*)/register/save#', $currentUrl, $matches);
        $emailTemplate = str_replace('{registrationURL}', $matches[1] . '/register/eula?token=' . $user->token, $emailTemplate);

        // can't use $this-_config 'cause it's a static function
        $configEmail = Zend_Registry::get('config')->email;

        switch (strtolower($configEmail->transport)) {
            case 'smtp':
                Zend_Mail::setDefaultTransport(
                    new Zend_Mail_Transport_Smtp(
                        $configEmail->host,
                        $configEmail->toArray()
                    )
                );
                break;
            case 'mock':
                Zend_Mail::setDefaultTransport(new Zend_Mail_Transport_Mock());
                break;
            default:
                Zend_Mail::setDefaultTransport(new Zend_Mail_Transport_Sendmail());
        }

        $mail = new Zend_Mail();
        $mail->setBodyText($emailTemplate);
        $mail->setFrom('support@community-id.org');
        $mail->addTo($user->email);
        $mail->setSubject($this->view->translate('Community-ID registration confirmation'));

        return $mail;
    }
}
