<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

require 'Setup.php';

Setup::setIncludePath();
Setup::setAutoLoader();
Setup::setConfig();
Setup::setErrorReporting();
Setup::setLogger();
Setup::logRequest();
Setup::setDatabase();
Setup::setSession();
Setup::setAcl();
Setup::setI18N();
Setup::setLayout();
Setup::setFrontController();
Setup::dispatch();
