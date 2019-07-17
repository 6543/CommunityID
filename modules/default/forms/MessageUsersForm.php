<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class MessageUsersForm extends Zend_Form
{
    public function init()
    {
        $subject = new Zend_Form_Element_Text('subject');
        translate('Subject:');
        $subject->setLabel('Subject:')
                ->setRequired(true);

        $cc = new Zend_Form_Element_Text('cc');
        translate('CC:');
        $cc->setLabel('CC:');

        $bodyPlain = new Zend_Form_Element_Textarea('bodyPlain');
        translate('Body:');
        $bodyPlain->setLabel('Body:');

        $bodyHTML = new Zend_Form_Element_Textarea('bodyHTML');
        $bodyHTML->setLabel('Body:');

        $this->addElements(array($subject, $cc, $bodyPlain, $bodyHTML));
    }
}
