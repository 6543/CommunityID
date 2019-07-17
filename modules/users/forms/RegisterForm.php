<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since Textroller 0.9
* @package TextRoller
* @packager Keyboard Monkeys
*/


class RegisterForm extends Zend_Form
{
    private $_baseWebDir;

    public function __construct($options = null, $baseWebDir = null)
    {
        $this->_baseWebDir = $baseWebDir;
        parent::__construct($options);
    }

    public function init()
    {
        $firstName = new Monkeys_Form_Element_Text('firstname');
        translate('First Name');
        $firstName->setLabel('First Name')
                  ->setRequired(true);

        $lastName = new Monkeys_Form_Element_Text('lastname');
        translate('Last Name');
        $lastName->setLabel('Last Name')
                 ->setRequired(true);

        $email = new Monkeys_Form_Element_Text('email');
        translate('E-mail');
        $email->setLabel('E-mail')
              ->addFilter('StringToLower')
              ->setRequired(true)
              ->addValidator('EmailAddress');

        $username = new Monkeys_Form_Element_Text('username');
        translate('Username');
        $username->setLabel('Username')
                 ->setRequired(true);

        $password1 = new Monkeys_Form_Element_Password('password1');
        translate('Enter desired password');
        $password1->setLabel('Enter desired password')
                  ->setRequired(true)
                  ->addValidator(new Monkeys_Validate_PasswordConfirmation());

        $password2 = new Monkeys_Form_Element_Password('password2');
        translate('Enter password again');
        $password2->setLabel('Enter password again')
                  ->setRequired(true);

        // ZF has some bugs when using mutators here, so I have to use the config array
        translate('Please enter the text below');
        $captcha = new Monkeys_Form_Element_Captcha('captcha', array(
            'label'     => 'Please enter the text below',
            'captcha'   => array(
                'captcha'       => 'Image',
                'sessionClass'  => get_class(Zend_Registry::get('appSession')),
                'font'          => APP_DIR . '/libs/Monkeys/fonts/Verdana.ttf',
                'imgDir'        => WEB_DIR. '/captchas',
                'imgUrl'        => $this->_baseWebDir . '/captchas',
                'wordLen'       => 4,
                'fontSize'      => 30,
                'timeout'       => 300,
            )
        ));

        $this->addElements(array($firstName, $lastName, $email, $username, $password1, $password2, $captcha));
    }
}
