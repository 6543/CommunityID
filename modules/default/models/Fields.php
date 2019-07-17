<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/


class Fields extends Monkeys_Db_Table_Gateway
{
    protected $_name = 'fields';
    protected $_primary = 'id';
    protected $_rowClass = 'Field';

    private $_fieldsNames= array();

    public function getValues(User $user)
    {
        $userId = (int)$user->id;
        $select = $this->select()
                       ->setIntegrityCheck(false)
                       ->from('fields')
                       ->joinLeft('fields_values', "fields_values.field_id=fields.id AND fields_values.user_id=".$user->id);

        return $this->fetchAll($select);
    }

    public function getFieldName($fieldIdentifier)
    {
        if (!$this->_fieldsNames) {
            foreach ($this->fetchAll($this->select()) as $field) {
                $this->_fieldsNames[$field->openid] = $field->name;
            }
        }

        return $this->_fieldsNames[$fieldIdentifier];
    }

    private function _translationPlaceholders()
    {
        translate('Nickname');
        translate('E-mail');
        translate('Full Name');
        translate('Date of Birth');
        translate('Gender');
        translate('Postal Code');
        translate('Country');
        translate('Language');
        translate('Time Zone');
    }
}
