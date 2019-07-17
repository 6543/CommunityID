<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

define('APP_DIR', dirname(__FILE__) . '/..');
require APP_DIR . '/Application.php';

Application::setIncludePath();
Application::setAutoLoader();
Application::setConfig();
Application::setLogger();
Application::setDatabase();

$news = new News_Model_News();
$news->deleteTestEntries();
