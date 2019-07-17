<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class MessageusersController extends Monkeys_Controller_Action
{
    public function indexAction()
    {
        $appSession = Zend_Registry::get('appSession');
        if (isset($appSession->messageUsersForm)) {
            $this->view->messageUsersForm = $appSession->messageUsersForm;
            unset($appSession->messageUsersForm);
        } else {
            $this->view->messageUsersForm = new MessageUsersForm();
        }

        $this->_helper->actionStack('index', 'login', 'users');
    }

    public function sendAction()
    {
        $form = new MessageUsersForm();
        $formData = $this->_request->getPost();

        $form->populate($formData);
        if (!$form->isValid($formData)) {
            return $this->_redirectFaultyForm($form);
        }

        $cc = $form->getValue('cc');
        $ccArr = array();
        if (trim($cc) != '') {
            $validator = new Zend_Validate_EmailAddress();
            $ccArr = explode(',', $cc);
            for ($i = 0; $i < count($ccArr); $i++) {
                $ccArr[$i] = trim($ccArr[$i]);
                if (!$validator->isValid($ccArr[$i])) {
                    foreach ($validator->getMessages() as $messageId => $message) {
                        $form->cc->addError($this->view->translate('CC field must be a comma-separated list of valid E-mails'));
                        return $this->_redirectFaultyForm($form);
                    }
                }
            }
        }

        $mail = self::getMail(
                        $form->getValue('subject'),
                        $this->_getParam('messageType'),
                        $this->_getParam('messageType') == 'plain'?
                            $form->getValue('bodyPlain')
                            : $form->getValue('bodyHTML')
        );

        $mail->setSubject($form->getValue('subject'));
        if ($this->_getParam('messageType') == 'plain') {
            $mail->setBodyText($form->getValue('bodyPlain'));
        } else {
            $mail->setBodyHtml($form->getValue('bodyHTML'));
        }

        $users = new Users();
        foreach ($users->getUsers() as $user) {
            $mail->addTo($user->email);
        }

        foreach ($ccArr as $cc) {
            $mail->addCC($cc);
        }

        try {
            $mail->send();
            $this->_helper->FlashMessenger->addMessage('Message has been sent');
        } catch (Zend_Mail_Protocol_Exception $e) {
            $this->_helper->FlashMessenger->addMessage('There was an error trying to send the message');
            if ($this->_config->logging->level == Zend_Log::DEBUG) {
                $this->_helper->FlashMessenger->addMessage($e->getMessage());

                return $this->_redirectFaultyForm($form);
            }
        }

        $this->_redirect('');
    }

    private function _redirectFaultyForm(Zend_Form $form)
    {
        $appSession = Zend_Registry::get('appSession');
        $appSession->messageUsersForm = $form;

        return $this->_forward('index');
    }

    /**
    * @return Zend_Mail
    * @throws Zend_Mail_Protocol_Exception
    */
    public static function getMail()
    {
        // can't use $this->_config 'cause we're in a static function
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

        $mail = new Zend_Mail('UTF-8');
        $mail->setFrom('support@community-id.org');

        return $mail;
    }
}

