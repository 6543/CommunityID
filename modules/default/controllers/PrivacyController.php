<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class PrivacyController extends Monkeys_Controller_Action
{
    protected $_numCols = 1;

    public function indexAction()
    {
        $locale = Zend_Registry::get('Zend_Locale');
        $localeElements = explode('_', $locale);

        if (file_exists(APP_DIR . "/resources/$locale/privacy.txt")) {
            $file = APP_DIR . "/resources/$locale/privacy.txt";
        } else if (count($localeElements == 2)
                && file_exists(APP_DIR . "/resources/".$localeElements[0]."/privacy.txt")) {
            $file = APP_DIR . "/resources/".$localeElements[0]."/privacy.txt";
        } else {
            $file = APP_DIR . "/resources/en/privacy.txt";
        }

        $this->view->privacyPolicy = nl2br(file_get_contents($file));
    }
}
