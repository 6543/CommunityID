<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

require_once 'TestHarness.php';

require 'modules/users/models/UsersTests.php';
require 'modules/users/controllers/RegisterControllerTests.php';
require 'modules/users/controllers/ProfilegeneralControllerTests.php';
require 'modules/default/controllers/MessageusersControllerTests.php';
require 'modules/default/controllers/HistoryControllerTests.php';
require 'modules/default/controllers/OpenidControllerTests.php';
require 'modules/default/controllers/IdentityControllerTests.php';
require 'modules/default/controllers/FeedbackControllerTests.php';

class AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->setName('Community-ID');

        // gotta test one by one, or else I'll get random segfaults
        // I got tired of figuring out where those come from. PHP sucks.
        //$suite->addTestSuite('UsersTests');
        //$suite->addTestSuite('Users_RegisterControllerTests');
        //$suite->addTestSuite('Users_ProfilegeneralControllerTests');
        //$suite->addTestSuite('MessageusersControllerTests');
        //$suite->addTestSuite('HistoryControllerTests');
        //$suite->addTestSuite('OpenidControllerTests');
        //$suite->addTestSuite('IdentityControllerTests');
        //$suite->addTestSuite('FeedbackControllerTests');
        return $suite;
    }
}
