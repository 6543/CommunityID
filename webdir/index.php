<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

define('WEB_DIR', dirname(__FILE__));

// realpath is needed for Zend_Translate to appropriately work
define('APP_DIR', realpath(dirname(__FILE__) . '/..'));

require APP_DIR . '/bootstrap.php';
