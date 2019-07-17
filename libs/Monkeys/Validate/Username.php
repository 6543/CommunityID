<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

/**
* Validates URL element syntax to avoid encoding issues, according to rfc 1738, section 2.2
*/
class Monkeys_Validate_Username extends Zend_Validate_Abstract
{
    const BAD = 'bad';

    protected $_messageTemplates = array(
        self::BAD => 'Username can only contain US-ASCII alphanumeric characters, plus any of the symbols $-_.+!*\'(), and "'
    );

    public function isValid($value, $context = null)
    {
        $this->_setValue($value);

        if (preg_match('/^[A-Za-z\$-_.\+!\*\'\(\)",]+$/', $value)) {
            return true;
        } else {
            $this->_error(self::BAD);
            return false;
        }
    }
}
