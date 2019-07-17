<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/


class Model_FieldsValues extends Monkeys_Db_Table_Gateway
{
    protected $_name = 'fields_values';
    protected $_primary = array('user_id', 'field_id');
    protected $_rowClass = 'Model_FieldsValue';

    public function deleteForUser(Users_Model_User $user)
    {
        $where = $this->getAdapter()->quoteInto('user_id=?', $user->id);
        $this->delete($where);
    }
}
