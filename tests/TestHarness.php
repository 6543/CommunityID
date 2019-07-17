<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/


/**
* This is included by all unit test class for them to be able to be run
* independently
*/

define('APP_DIR', dirname(__FILE__) . '/..');
define('WEB_DIR', APP_DIR . '/webdir');
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

require_once APP_DIR . '/Application.php';

class TestHarness
{
    public static function setUp()
    {
        Application::setIncludePath();
        Application::setAutoLoader();

        // need the autoloader before requiring anything
        require_once 'tests/TestRequest.php';

        Application::cleanUp();
        Application::setConfig();
        Application::setErrorReporting();

        Zend_Registry::get('config')->logging->level = Zend_Log::DEBUG;
        Application::setLogger(true);

        Application::logRequest();
        Application::setDatabase();
        Application::setSession();
        Application::setAcl();
        Application::setI18N();
        Application::setLayout();
        Application::setFrontController();
        Application::$front->throwExceptions(true);

        // disable e-mailing
        require_once 'tests/Zend_Mail_Transport_Mock.php';
        Zend_Registry::get('config')->email->transport = 'mock';
    }
}

TestHarness::Setup();
