<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since Textroller 0.9
* @package TextRoller
* @packager Keyboard Monkeys
*/


class InstallForm extends Zend_Form
{
    public function init()
    {
        $hostname = new Zend_Form_Element_Text('hostname');
        $hostname->setLabel('Hostname:')
                 ->setDescription('usually localhost')
                 ->setRequired(true)
                 ->setValue('localhost');

        $dbname = new Zend_Form_Element_Text('dbname');
        $dbname->setLabel('Database name:')
               ->setRequired(true)
               ->setValue(Zend_Registry::get('config')->database->params->dbname);

        $dbusername = new Zend_Form_Element_Text('dbusername');
        $dbusername->setLabel('Database username:')
                   ->setRequired(true);

        $dbpassword = new Zend_Form_Element_Password('dbpassword');
        $dbpassword->setLabel('Database password:');

        $supportemail = new Zend_Form_Element_Text('supportemail');
        $supportemail->setLabel('Support E-mail:')
                     ->setDescription('Will be used as the sender for any message sent by the system, and as the recipient for user feedback')
                     ->addFilter('StringToLower')
                     ->addValidator('EmailAddress')
                     ->setRequired(true);

        $this->addElements(array($hostname, $dbname, $dbusername, $dbpassword, $supportemail));
    }
}
