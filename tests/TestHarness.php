<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since Textroller 0.9
* @package TextRoller
* @packager Keyboard Monkeys
*/


/**
* This is included by all unit test class for them to be able to be run
* independently
*/

define('APP_DIR', dirname(__FILE__) . '/..');
define('WEB_DIR', dirname(__FILE__) . '/webdir');

require_once APP_DIR . '/Setup.php';

class TestHarness
{
    public static function setUp()
    {
        Setup::setIncludePath();
        Setup::setAutoLoader();

        // need the autoloader before requiring anything
        require_once 'tests/TestRequest.php';

        Setup::cleanUp();
        Setup::setConfig();
        Setup::setErrorReporting();

        Zend_Registry::get('config')->logging->level = Zend_Log::DEBUG;
        Setup::setLogger(true);

        Setup::logRequest();
        Setup::setDatabase();
        Setup::setSession();
        Setup::setAcl();
        Setup::setI18N();
        Setup::setLayout();
        Setup::setFrontController();
        Setup::$front->throwExceptions(true);

        // disable e-mailing
        require_once 'tests/Zend_Mail_Transport_Mock.php';
        Zend_Registry::get('config')->email->transport = 'mock';
    }
}

TestHarness::Setup();
