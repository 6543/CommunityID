<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD Licensese
* @author Keyboard Monkeys Ltd.
* @since Textroller 0.9
* @package TextRoller
* @packager Keyboard Monkeys
*/

class Monkeys_AccessDeniedException extends Zend_Exception
{
    public function __construct()
    {
        parent::__construct('Access Denied');
    }
}
