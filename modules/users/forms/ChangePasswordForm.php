<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since Textroller 0.9
* @package TextRoller
* @packager Keyboard Monkeys
*/


class ChangePasswordForm extends Zend_Form
{
    public function init()
    {
        $password1 = new Monkeys_Form_Element_Password('password1');
        translate('Enter password');
        $password1->setLabel('Enter password')
                  ->setRequired(true)
                  ->addValidator(new Monkeys_Validate_PasswordConfirmation());

        $password2 = new Monkeys_Form_Element_Password('password2');
        translate('Enter password again');
        $password2->setLabel('Enter password again')
                  ->setRequired(true);

        $this->addElements(array($password1, $password2));
    }
}
