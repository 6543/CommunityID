<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

/**
* This scripts creates fake user (non-admin) accounts, with the test flag set to 1.
*/

define('APP_DIR', dirname(__FILE__) . '/..');

/**
* Number of users to create
*/
define('NUM_USERS', 1000);

require APP_DIR . '/Setup.php';

Setup::setIncludePath();
Setup::setAutoLoader();
Setup::setConfig();
Setup::setLogger();
Setup::setDatabase();

class GenerateRandomUsers
{
    private $_names;
    private $_numNames;

    public function __construct()
    {
        $this->_names = file(dirname(__FILE__).'/../libs/Monkeys/tests/names.txt');
        $this->_numNames = count($this->_names);
    }

    public function generate()
    {
        $users = new Users();
        for ($i = 0; $i < NUM_USERS ; $i++) {
            $firstname = trim($this->_names[rand(0, $this->_numNames)]);
            $username = strtolower(substr($firstname, 0, 4));
            $user = $users->createRow();

            $user->test                   = 1;
            $user->username               = $username;
            $user->openid                 = "http://localhost/communityid/identity/$username";
            $user->accepted_eula          = 1;
            $user->registration_date      = date('Y-m-d', time() - rand(0, 365) * 24 * 60 * 60);
            $user->firstname              = $firstname;
            $user->lastname               = trim($this->_names[rand(0, $this->_numNames)]);
            $user->email                  = "$username@mailinator.com";
            $user->role                   = 'registered';
            $user->token                  = '';
            $user->save();
        }
    }
}

$generate = new GenerateRandomUsers();
$generate->generate();
