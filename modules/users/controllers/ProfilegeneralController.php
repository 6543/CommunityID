<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class Users_ProfilegeneralController extends Monkeys_Controller_Action
{
    private $_users;

    public function preDispatch()
    {
        if ($this->user->role != User::ROLE_ADMIN
            && $this->targetUser->id != $this->user->id)
        {
            throw new Monkeys_AccessDeniedException();
        }
    }

    public function accountinfoAction()
    {
    }

    public function editaccountinfoAction()
    {
        if ($this->targetUser->id != $this->user->id
            // this condition checks for an non-admin trying to add a new user
            && ($this->targetUser->id != 0 || $this->user->role != User::ROLE_ADMIN))
        {
            throw new Monkeys_AccessDeniedException();
        }

        $appSession = Zend_Registry::get('appSession');
        if (isset($appSession->accountInfoForm)) {
            $this->view->accountInfoForm = $appSession->accountInfoForm;
            unset($appSession->accountInfoForm);
        } else {
            $this->view->accountInfoForm = new AccountInfoForm(null, $this->targetUser);
            $this->view->accountInfoForm->populate(array(
                'username'      => $this->targetUser->username,
                'firstname'     => $this->targetUser->firstname,
                'lastname'      => $this->targetUser->lastname,
                'email'         => $this->targetUser->email,
            ));
        }
    }

    public function saveaccountinfoAction()
    {
        $isNewUser = is_null($this->targetUser->id)? true : false;

        if (!$isNewUser && $this->targetUser->id != $this->user->id) {
            // admins can add new users, but not edit existing ones
            throw new Monkeys_AccessDeniedException();
        }

        $form = new AccountInfoForm(null, $this->targetUser);
        $formData = $this->_request->getPost();

        $form->populate($formData);
        if (!$form->isValid($formData)) {
            return $this->_redirectInvalidForm($form);
        }

        $existingUsernameOrEmail = false;
        $newUsername = $form->getValue('username');
        if (($isNewUser && $this->_usernameAlreadyExists($newUsername))
            || (!$isNewUser && ($this->targetUser->username != $newUsername)
                && $this->_usernameAlreadyExists($newUsername)))
        {
            $form->username->addError($this->view->translate('This username is already in use'));
            $existingUsernameOrEmail = true;
        }

        $newEmail = $form->getValue('email');
        if (($isNewUser && $this->_emailAlreadyExists($newEmail))
            || (!$isNewUser && ($this->targetUser->email != $newEmail)
                && $this->_emailAlreadyExists($newEmail)))
        {
            $form->email->addError($this->view->translate('This E-mail is already in use'));
            $existingUsernameOrEmail = true;
        }

        if ($existingUsernameOrEmail) {
            return $this->_redirectInvalidForm($form);
        }

        $this->targetUser->username = $newUsername;
        $this->targetUser->firstname = $form->getValue('firstname');
        $this->targetUser->lastname = $form->getValue('lastname');
        $this->targetUser->email = $newEmail;
        if ($isNewUser) {
            $this->targetUser->accepted_eula = 1;
            $this->targetUser->registration_date = date('Y-m-d');
            $this->targetUser->openid = $this->_generateOpenId($this->targetUser->username);
            $this->targetUser->role = User::ROLE_REGISTERED;
            $this->targetUser->setClearPassword($form->getValue('password1'));
        }
        $this->targetUser->save();

        /**
        * When the form is submitted through a YUI request using a file, an iframe is used,
        * so the framework doesn't detected it as ajax, so we have to manually ensure the 
        * layout is not shown.
        */
        $this->_helper->layout->disableLayout();
        $this->_forward('accountinfo', null , null, array('userid' => $this->targetUser->id));
    }

    private function _usernameAlreadyExists($username)
    {
        $users = $this->_getUsers();
        return $users->getUser($username);
    }

    private function _emailAlreadyExists($email)
    {
        $users = $this->_getUsers();
        return $users->getUserWithEmail($email);
    }

    private function _redirectInvalidForm(Zend_Form $form)
    {
        $appSession = Zend_Registry::get('appSession');
        $appSession->accountInfoForm = $form;

        /**
        * When the form is submitted through a YUI request using a file, an iframe is used,
        * so the framework doesn't detected it as ajax, so we have to manually ensure the 
        * layout is not shown.
        */
        $this->_helper->layout->disableLayout();
        $this->_forward('editaccountinfo', null , null, array('userid' => $this->targetUser->id));
        return;
    }

    /**
    * Only the users themselves can change their passwords
    */
    public function changepasswordAction()
    {
        if ($this->targetUser->id != $this->user->id)
        {
            throw new Monkeys_AccessDeniedException();
        }

        $appSession = Zend_Registry::get('appSession');
        if (isset($appSession->changePasswordForm)) {
            $this->view->changePasswordForm = $appSession->changePasswordForm;
            unset($appSession->changePasswordForm);
        } else {
            $this->view->changePasswordForm = new ChangePasswordForm();
        }
    }

    public function savepasswordAction()
    {
        if ($this->targetUser->id != $this->user->id)
        {
            throw new Monkeys_AccessDeniedException();
        }

        $form = new ChangePasswordForm();
        $formData = $this->_request->getPost();
        $form->populate($formData);
        if (!$form->isValid($formData)) {
            $appSession = Zend_Registry::get('appSession');
            $appSession->changePasswordForm = $form;
            return $this->_forward('changepassword', null , null, array('userid' => $this->targetUser->id));
        }

        $this->targetUser->setClearPassword($form->getValue('password1'));
        $this->targetUser->save();

        return $this->_forward('accountinfo', null , null, array('userid' => $this->targetUser->id));
    }

    public function confirmdeleteAction()
    {
        $this->_helper->actionStack('index', 'login', 'users');
    }

    public function deleteAction()
    {
        $mail = self::getMail();
        $mail->setFrom($this->_config->email->supportemail);
        $mail->addTo($this->_config->email->supportemail);
        $mail->setSubject('Community-ID user deletion');

        $userFullname = $this->user->getFullName();

        $reasonsChecked = array();
        if ($this->_getParam('reason_test')) {
            $reasonsChecked[] = 'This was just a test account';
        }
        if ($this->_getParam('reason_foundbetter')) {
            $reasonsChecked[] = 'I found a better service';
        }
        if ($this->_getParam('reason_lackedfeatures')) {
            $reasonsChecked[] = 'Service lacked some key features I needed';
        }
        if ($this->_getParam('reason_none')) {
            $reasonsChecked[] = 'No particular reason';
        }

        if ($reasonsChecked) {
            $reasonsChecked = implode("\r\n", $reasonsChecked);
        } else {
            $reasonsChecked = 'None (no checkbox was ticked).';
        }

        $comment = $this->_getParam('reason_comments');

        $body = <<<EOT
Dear Admin:

The user $userFullname has deleted his account, giving the following feedback:

Reasons checked:
$reasonsChecked

Comment:
$comment
EOT;
        $mail->setBodyText($body);
        try {
            $mail->send();
        } catch (Zend_Mail_Protocol_Exception $e) {
            if ($this->_config->logging->level == Zend_Log::DEBUG) {
                $this->_helper->FlashMessenger->addMessage('Account was deleted, but feedback form couldn\'t be sent to admins');
            }
        }

        $users = $this->_getUsers();
        $users->deleteUser($this->user);
        Zend_Auth::getInstance()->clearIdentity();

        $this->_helper->FlashMessenger->addMessage($this->view->translate('Your acccount has been successfully deleted'));
        $this->_redirect('');
    }

    private function _generateOpenId($username)
    {
        $selfUrl = Zend_OpenId::selfUrl();
        if (!preg_match('#(.*)/users/profile.*#', $selfUrl, $matches)) {
            throw new Exception('Couldn\'t retrieve current URL');
        }

        if ($this->_config->subdomain->enabled) {
            $openid = $this->_getProtocol() . '://' . $username . '.' . $this->_config->subdomain->hostname;
        } else {
            $openid = $matches[1] . "/identity/$username";
        }

        if ($this->_config->SSL->enable_mixed_mode) {
            $openid = str_replace('http://', 'https://', $openid);
        }

        Zend_OpenId::normalizeUrl($openid);

        return $openid;
    }

    /**
    * @return Zend_Mail
    * @throws Zend_Mail_Protocol_Exception
    */
    public static function getMail()
    {
        // can't use $this->_config 'cause it's a static function
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

        return $mail;
    }

    private function _getUsers()
    {
        if (!isset($this->_users)) {
            $this->_users = new Users();
        }

        return $this->_users;
    }
}
