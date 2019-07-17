<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/


class Associations extends Monkeys_Db_Table_Gateway
{
    protected $_name = 'associations';
    protected $_primary = 'handle';
    protected $_rowClass = 'Association';

    public function getAssociationGivenHandle($handle)
    {
        $select = $this->select()
                       ->where('handle=?', $handle);

        return $this->fetchRow($select);
    }
}