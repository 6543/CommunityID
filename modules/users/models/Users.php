<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/


class Users_Model_Users extends Monkeys_Db_Table_Gateway
{
    const DIR_ASC = 0;
    const DIR_DESC = 1;

    protected $_name = 'users';
    protected $_primary = 'id';
    protected $_rowClass = 'Users_Model_User';

    private $_user;

    private $_sortFields = array(
        'name'          => array('firstname', 'lastname'),
        'registration'  => array('registration_date', 'firstname', 'lastname'),
        'status'        => array('accepted_eula', 'registration_date', 'firstname', 'lastname'),
    );

    public function createRow(array $data = array(), $defaultSource = null)
    {
        return parent::createRow(array(
            'openid'                 => '',
            'password_changed'       => '0000-00-00',
            'role'                   => Users_Model_User::ROLE_GUEST,
            'passwordreset_token'    => '',
        ));
    }

    /**
    * In CID we chose from the beginning not to use SET NAMES, and instead leave the charset encodings configurations
    * to remain in the database server side (my.cnf).
    *
    * CID's strings are UTF8. If character_set_client is not UTF8 but latin1 for example (unfortunatly that's the common case), non-latin1
    * characters will appear garbled when manually browsing the db, but they should show OK in CID's web pages.
    *
    * When authenticating below, we use MySQL's MD5 function. From my tests, it looks like the argument of this function
    * gets automatically converted to the charset of that field. Sorta like if we had implicitly MD5(CONVERT(arg using charset)).
    * When the tables are build during setup, the charset of string fields are set accordingly to the my.cnf directives
    * character-set-server and collation-server.
    * If those directives don't match character_set_client, the conversion inside MD5 will in fact transform the string, and we'll
    * get the MD5 of a different string than what we had intended (well, only if the string contains non-latin1 characters).
    * For this reason we have to override that conversion, converting to the charset specified in character_set_client, as shown below.
    *
    * @return Zend_Auth_Result
    */
    public function authenticate($identity, $password, $isOpenId = false)
    {
        $auth = Zend_Auth::getInstance();
        $db = $this->getAdapter();

        $result = $db->query("SHOW VARIABLES LIKE 'character_set_client'")->fetch();
        $clientCharset = $result['Value'];
        if ($isOpenId) {
            if (!Zend_OpenId::normalize($identity)) {
                return false;
            }

            $authAdapter = new Zend_Auth_Adapter_DbTable($db, 'users', 'openid', 'password',
                'MD5(CONCAT(CONVERT(openid using ' . $clientCharset . '), CONVERT(? using ' . $clientCharset . ')))');
        } else {
            $authAdapter = new Zend_Auth_Adapter_DbTable($db, 'users', 'username', 'password',
                'MD5(CONCAT(CONVERT(openid using ' . $clientCharset . '), CONVERT(? using ' . $clientCharset . ')))');
        }

        $authAdapter->setIdentity($identity);
        $authAdapter->setCredential($password);

        $result = $auth->authenticate($authAdapter);

        if ($result->isValid()) {
            if ($isOpenId) {
                $this->_user = $this->getUserWithOpenId($identity);
            } else {
                $this->_user = $this->getUserWithUsername($identity);
            }

            $auth->getStorage()->write($this->_user);
            Zend_Registry::set('user', $this->_user);

            return true;
        }

        return false;
    }

    public function getUser()
    {
        return $this->_user;
    }

    public function getUsers($startIndex = false, $results = false, $sort = false, $dir = false, $where = false, $search = false)
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

        if ($search) {
            $select = $select->where('firstname LIKE ? OR lastname LIKE ?', "%$search%", "%$search%");
        }

        return $this->fetchAll($select);
    }

    public function getNumUsers($where = false, $search = false)
    {
        $users = $this->getUsers(false, false, false, false, $where, $search);

        return count($users);
    }

    public function getNumUnconfirmedUsers()
    {
        $users = $this->getUsers(false, false, false, false, "accepted_eula=0 AND role != '".Users_Model_User::ROLE_ADMIN."'");

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

    public function getUserWithUsername($username)
    {
        $select = $this->select()
                       ->where('username=?', $username);

        return $this->fetchRow($select);
    }

    public function getUserWithOpenId($openid)
    {
        $select = $this->select()
                       ->where('openid=?', $openid);

        return $this->fetchRow($select);
    }

    public function getUnconfirmedUsers($olderThanDays)
    {
        $date = date('Y-m-d 23:59:59', strtotime("$olderThanDays days ago"));
        $select = $this->select()
                       ->where('accepted_eula=0')
                       ->where('registration_date < ?', $date);

        return $this->fetchAll($select);
    }

    public function deleteUser(Users_Model_User $user)
    {
        $where = $this->getAdapter()->quoteInto('id=?', $user->id);
        $this->delete($where);
    }

    public function deleteTestEntries()
    {
        $this->delete('test=1');
    }

    public function deleteUnconfirmed($olderThanDays)
    {
        $olderThanDays = (int) $olderThanDays;
        $date = date('Y-m-d', strtotime("$olderThanDays days ago"));
        $this->delete("accepted_eula=0 AND role = '".Users_Model_User::ROLE_GUEST."' AND registration_date < '$date'");
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
      'reminders' => 
          array (
            'SCHEMA_NAME' => NULL,
            'TABLE_NAME' => 'users',
            'COLUMN_NAME' => 'reminders',
            'COLUMN_POSITION' => 14,
            'DATA_TYPE' => 'int',
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
    );
}
