<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/


class Sites extends Monkeys_Db_Table_Gateway
{
    protected $_name = 'sites';
    protected $_primary = 'id';
    protected $_rowClass = 'Site';

    public function deleteForUserSite(User $user, $site)
    {
        $where1 = $this->getAdapter()->quoteInto('user_id=?',$user->id);
        $where2 = $this->getAdapter()->quoteInto('site=?', $site);
        $this->delete("$where1 AND $where2");
    }

    public function get(User $user, $startIndex, $results)
    {
        $select = $this->select()
                       ->where('user_id=?', $user->id);

        if ($startIndex !== false && $results !== false) {
            $select = $select->limit($results, $startIndex);
        }

        return $this->fetchAll($select);
    }

    public function getNumSites(User $user)
    {
        $sites = $this->get($user, false, false);

        return count($sites);
    }

    public function getTrusted(User $user)
    {
        $select = $this->select()
                       ->where('user_id=?', $user->id);

        return $this->fetchAll($select);
    }
}
