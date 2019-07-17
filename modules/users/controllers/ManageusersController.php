<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class Users_ManageusersController extends Monkeys_Controller_Action
{
    public function indexAction()
    {
        $this->_helper->actionStack('index', 'login', 'users');
    }

    public function deleteAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNeverRender(true);

        $this->targetUser->delete();
        echo $this->view->translate('User has been deleted successfully');
    }

    public function deleteunconfirmedAction()
    {
        $users = new Users();
        $users->deleteUnconfirmed();
    }
}
