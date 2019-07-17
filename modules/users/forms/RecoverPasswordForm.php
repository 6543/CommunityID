<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since Textroller 0.9
* @package TextRoller
* @packager Keyboard Monkeys
*/


class RecoverPasswordForm extends Zend_Form
{
    public function init()
    {
        $email = new Zend_Form_Element_Text('email');
        $email->setLabel('')
              ->addFilter('StringToLower')
              ->setRequired(true)
              ->addValidator('EmailAddress');

        $this->addElement($email);
    }
}
