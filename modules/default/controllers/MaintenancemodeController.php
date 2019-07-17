<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class MaintenancemodeController extends Monkeys_Controller_Action
{
    private $_settings;

    public function init()
    {
        parent::init();
        $this->_settings = new Settings();
    }

    public function enableAction()
    {
        $this->_settings->set(Settings::MAINTENANCE_MODE, 1);

        $this->_redirect('');
    }

    public function disableAction()
    {
        $this->_settings->set(Settings::MAINTENANCE_MODE, 0);

        $this->_redirect('');
    }
}
