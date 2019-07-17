<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/


class Install_Form_Install extends Zend_Form
{
    public function init()
    {
        $hostname = new Monkeys_Form_Element_Text('hostname');
        $hostname->setLabel('Hostname')
                 ->setDescription('usually localhost')
                 ->setRequired(true)
                 ->setDecoratorOptions(array('dontMarkRequired' => true))
                 ->setValue('localhost');

        $dbname = new Monkeys_Form_Element_Text('dbname');
        $dbname->setLabel('Database name')
               ->setRequired(true)
               ->setDecoratorOptions(array('dontMarkRequired' => true))
               ->setValue(Zend_Registry::get('config')->database->params->dbname);

        $dbusername = new Monkeys_Form_Element_Text('dbusername');
        $dbusername->setLabel('Database username')
                   ->setRequired(true)
                   ->setDecoratorOptions(array('dontMarkRequired' => true));

        $dbpassword = new Monkeys_Form_Element_Password('dbpassword');
        $dbpassword->setLabel('Database password');

        $supportemail = new Monkeys_Form_Element_Text('supportemail');
        $supportemail->setLabel('Support E-mail')
                     ->setDescription('Will be used as the sender for any message sent by the system, and as the recipient for user feedback')
                     ->addFilter('StringToLower')
                     ->addValidator('EmailAddress')
                     ->setRequired(true)
                     ->setDecoratorOptions(array('dontMarkRequired' => true));

        $adminUsername = new Monkeys_Form_Element_Text('adminUsername');
        $adminUsername->setLabel('Username')
                      ->setRequired(true)
                      ->setDecoratorOptions(array('dontMarkRequired' => true));

        $password1 = new Monkeys_Form_Element_Password('password1');
        $password1->setLabel('Enter password')
                  ->setRequired(true)
                  ->setDecoratorOptions(array('dontMarkRequired' => true))
                  ->addValidator(new Monkeys_Validate_PasswordConfirmation());

        $password2 = new Monkeys_Form_Element_Password('password2');
        $password2->setLabel('Enter password again')
                  ->setRequired(true)
                  ->setDecoratorOptions(array('dontMarkRequired' => true));
            

        $this->addElements(array($hostname, $dbname, $dbusername, $dbpassword, $supportemail,
            $adminUsername, $password1, $password2));
    }
}
