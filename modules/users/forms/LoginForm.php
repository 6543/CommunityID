<?php

class LoginForm extends Zend_Form
{
    public function init()
    {
        $username = new Zend_Form_Element_Text('username');
        translate('USERNAME');
        $username->setLabel('USERNAME')
                 ->setRequired(true);

        $password = new Zend_Form_Element_Password('password');
        translate('PASSWORD');
        $password->setLabel('PASSWORD')
                 ->setRequired(true);

        $rememberme = new Zend_Form_Element_Checkbox('rememberme');
        $rememberme->setLabel('Remember me');

        $this->addElements(array($username, $password, $rememberme));
    }
}
