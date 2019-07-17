<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/


self::$acl->addRole(new Zend_Acl_Role(User::ROLE_GUEST))
          ->addRole(new Zend_Acl_Role(User::ROLE_REGISTERED), User::ROLE_GUEST)
          ->addRole(new Zend_Acl_Role(User::ROLE_ADMIN), User::ROLE_REGISTERED);

/**************************
* ACTION CONTROLLER PRIVILEGES
*
* format: $privileges[module][controller][action] = role;
**************************/
$privileges['default']['index']['index']               = User::ROLE_GUEST;
$privileges['default']['identity']['index']            = User::ROLE_GUEST;
$privileges['default']['identity']['id']               = User::ROLE_GUEST;

$privileges['default']['error']['error']               = User::ROLE_GUEST;

$privileges['default']['openid']['provider']               = User::ROLE_GUEST;
$privileges['default']['openid']['login']                  = User::ROLE_GUEST;
$privileges['default']['openid']['authenticate']           = User::ROLE_GUEST;
$privileges['default']['openid']['trust']                  = User::ROLE_GUEST;

$privileges['default']['sites']['index']                  = User::ROLE_REGISTERED;
$privileges['default']['sites']['list']                  = User::ROLE_REGISTERED;
$privileges['default']['sites']['deny']                  = User::ROLE_REGISTERED;
$privileges['default']['sites']['allow']                  = User::ROLE_REGISTERED;
$privileges['default']['sites']['delete']                  = User::ROLE_REGISTERED;

$privileges['default']['history']['index']                  = User::ROLE_REGISTERED;
$privileges['default']['history']['list']                   = User::ROLE_REGISTERED;
$privileges['default']['history']['clear']                  = User::ROLE_REGISTERED;

$privileges['default']['messageusers']['index']  = User::ROLE_ADMIN;
$privileges['default']['messageusers']['send']   = User::ROLE_ADMIN;

$privileges['default']['maintenancemode']['enable']    = User::ROLE_ADMIN;
$privileges['default']['maintenancemode']['disable']   = User::ROLE_ADMIN;

$privileges['default']['feedback']['index']       = User::ROLE_GUEST;
$privileges['default']['feedback']['send']        = User::ROLE_GUEST;

$privileges['default']['privacy']['index']        = User::ROLE_GUEST;

$privileges['default']['about']['index']          = User::ROLE_GUEST;

$privileges['default']['learnmore']['index']      = User::ROLE_GUEST;

$privileges['install']['index']['index']                = User::ROLE_GUEST;
$privileges['install']['permissions']['index']          = User::ROLE_GUEST;
$privileges['install']['credentials']['index']          = User::ROLE_GUEST;
$privileges['install']['credentials']['save']           = User::ROLE_GUEST;
$privileges['install']['complete']['index']             = User::ROLE_GUEST;

$privileges['users']['login']['index']            = User::ROLE_GUEST;
$privileges['users']['login']['logout']           = User::ROLE_GUEST;
$privileges['users']['login']['authenticate']     = User::ROLE_GUEST;

$privileges['users']['userlist']['index']       = User::ROLE_ADMIN;

$privileges['users']['register']['index']         = User::ROLE_GUEST;
$privileges['users']['register']['save']          = User::ROLE_GUEST;
$privileges['users']['register']['eula']          = User::ROLE_GUEST;
$privileges['users']['register']['declineeula']   = User::ROLE_GUEST;
$privileges['users']['register']['accepteula']    = User::ROLE_GUEST;

$privileges['users']['profile']['index']          = User::ROLE_REGISTERED;
$privileges['users']['profile']['edit']           = User::ROLE_REGISTERED;
$privileges['users']['profile']['save']           = User::ROLE_REGISTERED;

$privileges['users']['personalinfo']['index']           = User::ROLE_REGISTERED;
$privileges['users']['personalinfo']['show']           = User::ROLE_REGISTERED;
$privileges['users']['personalinfo']['edit']           = User::ROLE_REGISTERED;
$privileges['users']['personalinfo']['save']           = User::ROLE_REGISTERED;

$privileges['users']['profilegeneral']['accountinfo']     = User::ROLE_REGISTERED;
$privileges['users']['profilegeneral']['editaccountinfo']     = User::ROLE_REGISTERED;
$privileges['users']['profilegeneral']['saveaccountinfo']     = User::ROLE_REGISTERED;
$privileges['users']['profilegeneral']['changepassword']     = User::ROLE_REGISTERED;
$privileges['users']['profilegeneral']['savepassword']     = User::ROLE_REGISTERED;
$privileges['users']['profilegeneral']['confirmdelete']     = User::ROLE_REGISTERED;
$privileges['users']['profilegeneral']['delete']            = User::ROLE_REGISTERED;

$privileges['users']['recoverpassword']['index']  = User::ROLE_GUEST;
$privileges['users']['recoverpassword']['send']  = User::ROLE_GUEST;
$privileges['users']['recoverpassword']['reset']  = User::ROLE_GUEST;

$privileges['users']['manageusers']['index']  = User::ROLE_ADMIN;
$privileges['users']['manageusers']['delete']  = User::ROLE_ADMIN;
$privileges['users']['manageusers']['deleteunconfirmed']  = User::ROLE_ADMIN;

$privileges['users']['userslist']['index']  = User::ROLE_ADMIN;

$privileges['stats']['index']['index']          = User::ROLE_ADMIN;
$privileges['stats']['registrations']['index']  = User::ROLE_ADMIN;
$privileges['stats']['registrations']['graph']  = User::ROLE_ADMIN;
$privileges['stats']['authorizations']['index'] = User::ROLE_ADMIN;
$privileges['stats']['authorizations']['graph'] = User::ROLE_ADMIN;
$privileges['stats']['sites']['index']          = User::ROLE_ADMIN;
$privileges['stats']['sites']['graph']          = User::ROLE_ADMIN;
$privileges['stats']['top']['index']            = User::ROLE_ADMIN;
$privileges['stats']['top']['graph']            = User::ROLE_ADMIN;
