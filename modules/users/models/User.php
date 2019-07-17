<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/


class Users_Model_User extends Zend_Db_Table_Row_Abstract
{
    const ROLE_GUEST = 'guest';
    const ROLE_REGISTERED = 'registered';
    const ROLE_ADMIN = 'admin';
    
    /**
    * To identify the app that owns the user obj in the session.
    * Useful when sharing the user between apps.
    */

    public function getFullName()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    public function generateRandomPassword()
    {
        return substr(md5($this->getFullName() . time()), 0, 6);
    }

    /**
    * Password is stored using md5($this->openid.$password) because
    * that's what's used in Zend_OpenId
    */
    public function setClearPassword($password)
    {
        $this->password = md5($this->openid.$password);
        $this->password_changed = date('Y-m-d');
    }

    public function isAllowed($resource, $privilege)
    {
        $acl = Zend_Registry::get('acl');
        return $acl->isAllowed($this->role, $resource, $privilege);
    }

    public static function generateToken()
    {
        $token = '';
        for ($i = 0; $i < 50; $i++) {
            $token .= chr(rand(48, 122));
        }
        
        return md5($token.time());
    }
}
