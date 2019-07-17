<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class Settings extends Monkeys_Db_Table_Gateway
{
    protected $_name = 'settings';
    protected $_primary = 'name';

    const MAINTENANCE_MODE = 'maintenance_mode';

    public function get($name)
    {
        $select = $this->select()
                       ->where('name=?', $name);

        $row = $this->fetchRow($select);

        return $row->value;
    }

    public function set($name, $value)
    {
        $this->update(array('value' => $value), $this->getAdapter()->quoteInto('name=?', $name));
    }

    public function isMaintenanceMode()
    {
        return $this->get(self::MAINTENANCE_MODE);
    }
}
