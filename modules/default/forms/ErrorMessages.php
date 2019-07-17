<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since Textroller 0.9
* @package TextRoller
* @packager Keyboard Monkeys
*/

/**
* This class is never called. It's only a placeholder for form error messages wrapped in translate(),
* so that Poedit (or any other message catalogs editor) can catalog these messages for translation
*/
class ErrorMessages
{
    private function _messages()
    {
        translate('Value is empty, but a non-empty value is required');
        translate('\'%value%\' is not a valid email address in the basic format local-part@hostname');
        translate('\'%hostname%\' is not a valid hostname for email address \'%value%\'');
        translate('\'%value%\' appears to be a DNS hostname but cannot match TLD against known list');
        translate('\'%value%\' appears to be a local network name but local network names are not allowed');
        translate('Captcha value is wrong');
        translate('Password confirmation does not match');
    }
}
