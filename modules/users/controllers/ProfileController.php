<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class Users_ProfileController extends Monkeys_Controller_Action
{
    public function indexAction()
    {
        if (!$this->targetUser->id && $this->user->role != User::ROLE_ADMIN) {
            throw new Monkeys_AccessDeniedException();
        }

        $this->_helper->actionStack('index', 'login', 'users');
    }
}
