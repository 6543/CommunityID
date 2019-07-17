<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

abstract class Monkeys_Db_Table_Gateway extends Zend_Db_Table_Abstract
{
    public function getRowInstance($id)
    {
        return $this->fetchRow($this->select()->where('id = ?', $id));
    }
}
