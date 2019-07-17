<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class Stats_TopController extends Monkeys_Controller_Action
{
    public function indexAction()
    {
        $stats = new Stats();
        $this->view->sites = $stats->getTopTenSites();
    }
}