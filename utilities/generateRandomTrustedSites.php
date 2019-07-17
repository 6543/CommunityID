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
* This scripts creates fake entries in the sites table
*/

define('APP_DIR', dirname(__FILE__) . '/..');

/**
* Number of entries to create
*/
define('NUM_ENTRIES', 1000);

require APP_DIR . '/Application.php';

Application::setIncludePath();
Application::setAutoLoader();
Application::setConfig();
Application::setLogger();
Application::setDatabase();

class GenerateRandomSites
{
    private $_names;
    private $_numNames;

    public function __construct()
    {
        $this->_words = file(dirname(__FILE__).'/../libs/Monkeys/tests/words.txt');
        $this->_numWords= count($this->_words);
    }

    public function generate()
    {
        $sites = new Model_Sites();

        $stats = new Stats_Model_Stats();
        $userIds = $stats->getAllTestUsersIds();
        $numUsers = count($userIds);

        for ($i = 0; $i < NUM_ENTRIES; $i++) {
            $site = $sites->createRow();

            $site->user_id = $userIds[rand(0, $numUsers - 1)]['id'];
            $site->site = 'http://' . strtolower(trim($this->_words[rand(0, $this->_numWords)])) . '.com/'
                             . strtolower(trim($this->_words[rand(0, $this->_numWords)]));
            $site->creation_date = date('Y-m-d H:i:s', time() - rand(0, 365) * 24 * 60 * 60);
            $site->trusted = 'a:1:{s:26:"Zend_OpenId_Extension_Sreg";a:0:{}}';
            $site->save();
        }
    }
}

$generate = new GenerateRandomSites();
$generate->generate();
