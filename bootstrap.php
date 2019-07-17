<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

require 'Application.php';

Application::setIncludePath();
Application::setAutoLoader();
Application::setConfig();
Application::setErrorReporting();
Application::setLogger();
Application::logRequest();
Application::setDatabase();
Application::setSession();
Application::setAcl();
Application::setI18N();
Application::setLayout();
Application::setFrontController();
Application::dispatch();
