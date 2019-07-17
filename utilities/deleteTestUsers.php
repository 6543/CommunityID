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
require APP_DIR . '/Setup.php';

Setup::setIncludePath();
Setup::setAutoLoader();
Setup::setConfig();
Setup::setLogger();
Setup::setDatabase();

$users = new Users();
$users->deleteTestEntries();
