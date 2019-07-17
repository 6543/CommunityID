<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since Textroller 0.9
* @package TextRoller
* @packager Keyboard Monkeys
*/


class Users extends Monkeys_Db_Table_Gateway
{
    protected $_name = 'users';
    protected $_primary = 'id';
    protected $_rowClass = 'User';

    const DIR_ASC = 0;
    const DIR_DESC = 1;

    private $_sortFields = array(
        'name'          => array('firstname', 'lastname'),
        'registration'  => array('registration_date', 'firstname', 'lastname'),
        'status'        => array('accepted_eula', 'registration_date', 'firstname', 'lastname'),
    );

    public function createRow()
    {
        return parent::createRow(array(
            'openid'                 => '',
            'password_changed'       => '0000-00-00',
            'role'                   => User::ROLE_GUEST,
            'passwordreset_token'    => '',
        ));
    }

    public function getUsers($startIndex = false, $results = false, $sort = false, $dir = false, $where = false)
    {
        $select = $this->select();

        if ($startIndex !== false && $results !== false) {
            $select = $select->limit($results, $startIndex);
        }
        
        if ($sort && isset($this->_sortFields[$sort])) {
            $dir = ($dir == self::DIR_ASC? 'ASC' : 'DESC');
            $sortSql = array();
            foreach ($this->_sortFields[$sort] as $field) {
                $sortSql[] = "$field $dir";
            }

            $select = $select->order($sortSql);
        }

        if ($where) {
            $select = $select->where($where);
        }

        return $this->fetchAll($select);
    }

    public function getNumUsers($where = false)
    {
        $users = $this->getUsers(false, false, false, false, $where);

        return count($users);
    }

    public function getNumUnconfirmedUsers()
    {
        $users = $this->getUsers(false, false, false, false, "accepted_eula=0 AND role != '".User::ROLE_ADMIN."'");

        return count($users);
    }

    public function getUserWithToken($token)
    {
        $select = $this->select()
                       ->where('token=?', $token);

        return $this->fetchRow($select);
    }

    public function getUserWithEmail($email)
    {
        $select = $this->select()
                       ->where('email=?', $email);

        return $this->fetchRow($select);
    }

    public function getUserWithOpenId($openid)
    {
        $select = $this->select()
                       ->where('openid=?', $openid);

        return $this->fetchRow($select);
    }

    public function getUser($identity)
    {
        $select = $this->select()->where('username=?', $identity);

        return $this->fetchRow($select);
    }

    public function deleteUser(User $user)
    {
        $where = $this->getAdapter()->quoteInto('id=?', $user->id);
        $this->delete($where);
    }

    public function deleteTestEntries()
    {
        $this->delete('test=1');
    }

    public function deleteUnconfirmed()
    {
        $this->delete("accepted_eula=0 AND role = '".User::ROLE_GUEST."'");
    }

    protected $_metadata = array(
      'id' => 
          array (
            'SCHEMA_NAME' => NULL,
            'TABLE_NAME' => 'users',
            'COLUMN_NAME' => 'id',
            'COLUMN_POSITION' => 1,
            'DATA_TYPE' => 'int',
            'DEFAULT' => NULL,
            'NULLABLE' => false,
            'LENGTH' => NULL,
            'SCALE' => NULL,
            'PRECISION' => NULL,
            'UNSIGNED' => NULL,
            'PRIMARY' => true,
            'PRIMARY_POSITION' => 1,
            'IDENTITY' => true,
          ),
      'test' => 
          array (
            'SCHEMA_NAME' => NULL,
            'TABLE_NAME' => 'users',
            'COLUMN_NAME' => 'test',
            'COLUMN_POSITION' => 2,
            'DATA_TYPE' => 'tinyint',
            'DEFAULT' => '0',
            'NULLABLE' => false,
            'LENGTH' => NULL,
            'SCALE' => NULL,
            'PRECISION' => NULL,
            'UNSIGNED' => NULL,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => NULL,
            'IDENTITY' => false,
          ),
      'username' => 
          array (
            'SCHEMA_NAME' => NULL,
            'TABLE_NAME' => 'users',
            'COLUMN_NAME' => 'username',
            'COLUMN_POSITION' => 3,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => NULL,
            'NULLABLE' => false,
            'LENGTH' => '50',
            'SCALE' => NULL,
            'PRECISION' => NULL,
            'UNSIGNED' => NULL,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => NULL,
            'IDENTITY' => false,
          ),
      'openid' => 
          array (
            'SCHEMA_NAME' => NULL,
            'TABLE_NAME' => 'users',
            'COLUMN_NAME' => 'openid',
            'COLUMN_POSITION' => 4,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => NULL,
            'NULLABLE' => false,
            'LENGTH' => '100',
            'SCALE' => NULL,
            'PRECISION' => NULL,
            'UNSIGNED' => NULL,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => NULL,
            'IDENTITY' => false,
          ),
      'accepted_eula' => 
          array (
            'SCHEMA_NAME' => NULL,
            'TABLE_NAME' => 'users',
            'COLUMN_NAME' => 'accepted_eula',
            'COLUMN_POSITION' => 5,
            'DATA_TYPE' => 'tinyint',
            'DEFAULT' => '0',
            'NULLABLE' => false,
            'LENGTH' => NULL,
            'SCALE' => NULL,
            'PRECISION' => NULL,
            'UNSIGNED' => NULL,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => NULL,
            'IDENTITY' => false,
          ),
      'registration_date' => 
          array (
            'SCHEMA_NAME' => NULL,
            'TABLE_NAME' => 'users',
            'COLUMN_NAME' => 'registration_date',
            'COLUMN_POSITION' => 6,
            'DATA_TYPE' => 'date',
            'DEFAULT' => NULL,
            'NULLABLE' => false,
            'LENGTH' => NULL,
            'SCALE' => NULL,
            'PRECISION' => NULL,
            'UNSIGNED' => NULL,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => NULL,
            'IDENTITY' => false,
          ),
      'password' => 
          array (
            'SCHEMA_NAME' => NULL,
            'TABLE_NAME' => 'users',
            'COLUMN_NAME' => 'password',
            'COLUMN_POSITION' => 7,
            'DATA_TYPE' => 'char',
            'DEFAULT' => NULL,
            'NULLABLE' => false,
            'LENGTH' => '40',
            'SCALE' => NULL,
            'PRECISION' => NULL,
            'UNSIGNED' => NULL,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => NULL,
            'IDENTITY' => false,
          ),
      'password_changed' => 
          array (
            'SCHEMA_NAME' => NULL,
            'TABLE_NAME' => 'users',
            'COLUMN_NAME' => 'password_changed',
            'COLUMN_POSITION' => 8,
            'DATA_TYPE' => 'date',
            'DEFAULT' => NULL,
            'NULLABLE' => false,
            'LENGTH' => NULL,
            'SCALE' => NULL,
            'PRECISION' => NULL,
            'UNSIGNED' => NULL,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => NULL,
            'IDENTITY' => false,
          ),
      'firstname' => 
          array (
            'SCHEMA_NAME' => NULL,
            'TABLE_NAME' => 'users',
            'COLUMN_NAME' => 'firstname',
            'COLUMN_POSITION' => 9,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => NULL,
            'NULLABLE' => false,
            'LENGTH' => '50',
            'SCALE' => NULL,
            'PRECISION' => NULL,
            'UNSIGNED' => NULL,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => NULL,
            'IDENTITY' => false,
          ),
      'lastname' => 
          array (
            'SCHEMA_NAME' => NULL,
            'TABLE_NAME' => 'users',
            'COLUMN_NAME' => 'lastname',
            'COLUMN_POSITION' => 10,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => NULL,
            'NULLABLE' => false,
            'LENGTH' => '50',
            'SCALE' => NULL,
            'PRECISION' => NULL,
            'UNSIGNED' => NULL,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => NULL,
            'IDENTITY' => false,
          ),
      'email' => 
          array (
            'SCHEMA_NAME' => NULL,
            'TABLE_NAME' => 'users',
            'COLUMN_NAME' => 'email',
            'COLUMN_POSITION' => 11,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => NULL,
            'NULLABLE' => false,
            'LENGTH' => '50',
            'SCALE' => NULL,
            'PRECISION' => NULL,
            'UNSIGNED' => NULL,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => NULL,
            'IDENTITY' => false,
          ),
      'role' => 
          array (
            'SCHEMA_NAME' => NULL,
            'TABLE_NAME' => 'users',
            'COLUMN_NAME' => 'role',
            'COLUMN_POSITION' => 12,
            'DATA_TYPE' => 'varchar',
            'DEFAULT' => NULL,
            'NULLABLE' => false,
            'LENGTH' => '50',
            'SCALE' => NULL,
            'PRECISION' => NULL,
            'UNSIGNED' => NULL,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => NULL,
            'IDENTITY' => false,
          ),
      'token' => 
          array (
            'SCHEMA_NAME' => NULL,
            'TABLE_NAME' => 'users',
            'COLUMN_NAME' => 'token',
            'COLUMN_POSITION' => 13,
            'DATA_TYPE' => 'char',
            'DEFAULT' => NULL,
            'NULLABLE' => false,
            'LENGTH' => '32',
            'SCALE' => NULL,
            'PRECISION' => NULL,
            'UNSIGNED' => NULL,
            'PRIMARY' => false,
            'PRIMARY_POSITION' => NULL,
            'IDENTITY' => false,
          ),
    );
}
