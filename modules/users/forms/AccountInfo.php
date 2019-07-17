<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/


class Users_Form_AccountInfo extends Zend_Form
{
    private $_targetUser;

    public function __construct($options = null, $user = null)
    {
        $this->_targetUser = $user;
        parent::__construct($options);
    }

    public function init()
    {
        $username = new Monkeys_Form_Element_Text('username');
        translate('Username');
        $username->setLabel('Username')
                 ->addValidator(new Monkeys_Validate_Username())
                 ->setRequired(true);

        $firstname = new Monkeys_Form_Element_Text('firstname');
        translate('First Name');
        $firstname->setLabel('First Name')
                  ->setRequired(true);

        $lastname = new Monkeys_Form_Element_Text('lastname');
        translate('Last Name');
        $lastname->setLabel('Last Name')
                 ->setRequired(true);

        $email = new Monkeys_Form_Element_Text('email');
        translate('E-mail');
        $email->setLabel('E-mail')
              ->addFilter('StringToLower')
              ->setRequired(true)
              ->addValidator('EmailAddress');

        $this->addElements(array($username, $firstname, $lastname, $email));

        if (!$this->_targetUser->id) {
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
}
