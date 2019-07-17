<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class OpenIdLoginForm extends Zend_Form
{
    public function init()
    {
        $openIdIdentity = new Zend_Form_Element_Text('openIdIdentity');
        translate('Username');
        $openIdIdentity->setLabel('Username')
                       ->setRequired(true);

        $password = new Zend_Form_Element_Password('password');
        translate('Password');
        $password->setLabel('Password')
                 ->setRequired(true);

        $this->addElements(array($openIdIdentity, $password));
    }
}
