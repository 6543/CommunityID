<?php

abstract class Monkeys_Db_Table_Gateway extends Zend_Db_Table_Abstract
{
    public function getRowInstance($id)
    {
        return $this->fetchRow($this->select()->where('id = ?', $id));
    }
}
