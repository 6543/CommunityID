<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class Monkeys_OpenId_Provider_Storage_Database extends Zend_OpenId_Provider_Storage
{
    public function addAssociation($handle, $macFunc, $secret, $expires)
    {
        $associations = new Associations();
        $association = $associations->createRow();
        $association->handle = $handle;
        $association->macfunc = $macFunc;
        $association->secret = $secret;
        $association->expires = $expires;
        $association->save();

        return true;
    }

    public function getAssociation($handle, &$macFunc, &$secret, &$expires)
    {
        $associations = new Associations();
        $association = $associations->getAssociationGivenHandle($handle);
        if (!$association) {
            return false;
        }
        if ($association->expires < time()) {
            return false;
        }

        $macFunc = $association->macfunc;
        $secret = $association->secret;
        $expires = $association->expires;

        return true;
    }

    /**
    * Always returns false, since we'll be adding user through the GUI interface only
    */
    public function addUser($id, $password)
    {
        return false;
    }

    public function hasUser($id)
    {
        $users = new Users();
        $user = $users->getUserWithOpenId($id);

        return $user? true : false;
    }

    public function checkUser($id, $password)
    {
        $auth = Zend_Auth::getInstance();
        $db = Zend_Db::factory(Zend_Registry::get('config')->database);
        $authAdapter = new Zend_Auth_Adapter_DbTable($db, 'users', 'openid', 'password');
        $authAdapter->setIdentity($id);
        $authAdapter->setCredential($password);
        $result = $auth->authenticate($authAdapter);

        if ($result->isValid()) {
            // we don't wanna login into community-id
            Zend_Auth::getInstance()->clearIdentity();

            return true;
        }

        return false;
    }

    /**
     * Returns array of all trusted/untrusted sites for given user identified
     * by $id
     *
     * @param string $id user identity URL
     * @return array
     */
    public function getTrustedSites($id)
    {
        $users = new Users();
        $user = $users->getUserWithOpenId($id);

        $sites = new Sites();

        $trustedSites = array();
        foreach ($sites->getTrusted($user) as $site) {
            $trustedSites[$site->site] = unserialize($site->trusted);
        }

        return $trustedSites;
    }

    /**
     * Stores information about trusted/untrusted site for given user
     *
     * @param string $id user identity URL
     * @param string $site site URL
     * @param mixed $trusted trust data from extension or just a boolean value. If null, delete site. I know, bad desing. Blame it on ZF.
     * @return bool
     */
    public function addSite($id, $site, $trusted)
    {
        $users = new Users();
        $user = $users->getUserWithOpenId($id);

        $sites = new Sites();
        $sites->deleteForUserSite($user, $site);

        if (!is_null($trusted)) {
            $siteObj = $sites->createRow();
            $siteObj->user_id = $user->id;
            $siteObj->site = $site;
            $siteObj->creation_date = date('Y-m-d');
            $siteObj->trusted = serialize($trusted);
            $siteObj->save();
        }

        return true;
    }
}
