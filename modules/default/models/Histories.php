<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/


class Histories extends Monkeys_Db_Table_Gateway
{
    protected $_name = 'history';
    protected $_primary = 'id';
    protected $_rowClass = 'History';

    public function get(User $user, $startIndex, $results)
    {
        $select = $this->select()
                       ->where('user_id=?', $user->id);

        if ($startIndex !== false && $results !== false) {
            $select = $select->limit($results, $startIndex);
        }

        return $this->fetchAll($select);
    }

    public function getNumHistories(User $user)
    {
        $sites = $this->get($user, false, false);

        return count($sites);
    }

    public function clear(User $user)
    {
        $where = $this->getAdapter()->quoteInto('user_id=?', $user->id);
        $this->delete($where);
    }

    public function clearOldEntries()
    {
        $days = Zend_Registry::get('config')->environment->keep_history_days;

        $where = $this->getAdapter()->quoteInto('date < ?', date('Y-m-d', time() - $days * 86400));
        $this->delete($where);
    }
}
