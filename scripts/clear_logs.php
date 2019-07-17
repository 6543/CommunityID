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
* This scripts clears the log entries older than the number of days set in the
* directive keep_history_days in config.ini
*
* Intended to be run by cron.
*/

define('APP_DIR', dirname(__FILE__) . '/../');

require APP_DIR . '/Application.php';

Application::setIncludePath();
Application::setAutoLoader();
Application::setConfig();
Application::setLogger();
Application::setDatabase();

require 'modules/default/models/Histories.php';

$histories = new Model_Histories();
$histories->clearOldEntries();

?>
