<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

abstract class CommunityID_Controller_Action extends Monkeys_Controller_Action
{
    public function init()
    {
        parent::init();
        Zend_Controller_Action_HelperBroker::addPrefix('CommunityID_Controller_Action_Helper');
    }

    protected function _setBase()
    {
        if ($this->_config->subdomain->enabled) {
            $protocol = $this->getProtocol();

            $this->view->base = "$protocol://"
                                   . ($this->_config->subdomain->use_www? 'www.' : '')
                                   . $this->_config->subdomain->hostname;
        } else {
            $this->view->base = $this->view->getBase();
        }
    }

    protected function _validateTargetUser()
    {
        if (Zend_Registry::isRegistered('targetUser')) {
            // used by unit tests to inject the target user
            $this->targetUser = Zend_Registry::get('targetUser');
        } else {
            $userId = $this->_getParam('userid');

            if (is_null($userId)) {
                $this->targetUser = $this->user;
            } elseif ($this->_getParam('userid') == 0) {
                $users = new Users_Model_Users();
                $this->targetUser = $users->createRow();
            } else {
                if ($userId != $this->user->id && $this->user->role != Users_Model_User::ROLE_ADMIN) {
                    $this->_helper->FlashMessenger->addMessage('Error: Invalid user id');
                    $this->_redirect('profile/edit');
                }
                $users = new Users_Model_Users();
                $this->targetUser = $users->getRowInstance($userId);
            }
        }

        $this->view->targetUser = $this->targetUser;
    }

    protected function _redirectToNormalConnection()
    {
        if ($this->_config->SSL->enable_mixed_mode) {
            $this->_redirect('http://' . $_SERVER['HTTP_HOST'] . $this->view->base);
        } else {
            $this->_redirect('');
        }
    }
}
