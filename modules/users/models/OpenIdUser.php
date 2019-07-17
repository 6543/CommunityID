<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since Textroller 0.9
* @package TextRoller
* @packager Keyboard Monkeys
*/


class OpenIdUser extends Zend_OpenId_Provider_User
{
    private $_auth;
    private $_user;

    public function __construct()
    {
        $this->_auth = Zend_Auth::getInstance();
    }

    public function setLoggedInUser($id)
    {

        $users = new Users();
        $this->_user = $users->getuserWithOpenId($id);
        $this->_auth->getStorage()->write($this->_user);
    }

    public function getLoggedInUser()
    {
        $users = new Users();
        if ($this->_auth->hasIdentity()) {
            $user = $this->_auth->getStorage()->read();
            $user->init();

            // reactivate row as live data
            $user->setTable($users);

            return $user->openid;
        }

        return false;
    }

    public function delLoggedInUser()
    {
        $this->_auth->clearIdentity();

        return true;
    }
}
