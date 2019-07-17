<?php

class Monkeys_Ldap
{
    private static $_instance;

    private $_ldapConfig;

    /**
    * Ldap link identifier
    */
    private $_dp;

    private function __construct()
    {
        $this->_ldapConfig = Zend_Registry::get('config')->ldap;

        if (!$this->_dp= @ldap_connect($this->_ldapConfig->host)) {
            throw new Exception('Could not connect to LDAP server');
        }
        ldap_set_option($this->_dp, LDAP_OPT_PROTOCOL_VERSION, 3);
        if (!@ldap_bind($this->_dp, $this->_ldapConfig->username, $this->_ldapConfig->password)) {
            throw new Exception('Could not bind to LDAP server: ' . ldap_error($this->_dp));
        }
    }

    public static function getInstance()
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new Monkeys_Ldap();
        }

        return self::$_instance;
    }


    public function get($dn)
    {
        if (!$resultId = @ldap_search($this->_dp, $dn, "(&(objectClass=*))")) {
            throw new Exception('Could not retrieve record to LDAP server (1): ' . ldap_error($this->_dp));
        }

        if (!$result = @ldap_get_entries($this->_dp, $resultId)) {
            throw new Exception('Could not retrieve record to LDAP server (2): ' . ldap_error($this->_dp));
        }

        return $result[0];
    }

    /**
    * lastname (sn) is required for the "inetOrgPerson" schema
    */
    public function add(Users_Model_User $user)
    {
        $dn = 'cn=' . $user->username . ',' . $this->_ldapConfig->baseDn;
        $info = array(
            'cn'            => $user->username,
            'givenName'     => $user->firstname,
            'sn'            => $user->lastname,
            'mail'          => $user->email,
            'userPassword'  => $user->password,
            'objectclass'   => 'inetOrgPerson',
        );
        if (!@ldap_add($this->_dp, $dn, $info) && ldap_error($this->_dp) != 'Success') {
            throw new Exception('Could not add record to LDAP server: ' . ldap_error($this->_dp));
        }
    }

    public function modify(User $user)
    {
        $dn = 'cn=' . $user->username . ',' . $this->_ldapConfig->baseDn;
        $info = array(
            'cn'            => $user->username,
            'givenName'     => $user->firstname,
            'sn'            => $user->lastname,
            'mail'          => $user->email,
            'objectclass'   => 'inetOrgPerson',
        );
        if (!@ldap_modify($this->_dp, $dn, $info) && ldap_error($this->_dp) != 'Success') {
            throw new Exception('Could not modify record in LDAP server: ' . ldap_error($this->_dp));
        }
    }

    public function modifyUsername(User $user, $oldUsername)
    {
        $dn = 'cn=' . $oldUsername . ',' . $this->_ldapConfig->baseDn;
        $newRdn = 'cn=' . $user->username;
        if (!@ldap_rename($this->_dp, $dn, $newRdn, $this->_ldapConfig->baseDn, true) && ldap_error($this->_dp) != 'Success') {
            throw new Exception('Could not modify username in LDAP server: ' . ldap_error($this->_dp));
        }
    }

    public function delete($username)
    {
        $dn = "cn=$username," . $this->_ldapConfig->baseDn;
        if (!@ldap_delete($this->_dp, $dn) && ldap_error($this->_dp) != 'Success') {
            throw new Exception('Could not delete record from LDAP server: ' . ldap_error($this->_dp));
        }
    }
}
