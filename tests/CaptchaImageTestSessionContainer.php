<?php
/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/


/*
* Originally Zend_Captcha_ImageTest_SessionContainer, taken from Zend's test classes I believe.
*/
class CaptchaImageTestSessionContainer
{
    public static $word;

    public function __get($name)
    {
        if ('word' == $name) {
            return self::$word;
        }

        return null;
    }

    public function __set($name, $value)
    {
        if ('word' == $name) {
            self::$word = $value;
        } else {
            $this->$name = $value;
        }
    }

    public function __isset($name)
    {
        if (('word' == $name) && (null !== self::$word))  {
            return true;
        }

        return false;
    }

    public function __call($method, $args)
    {
        switch ($method) {
            case 'setExpirationHops':
            case 'setExpirationSeconds':
                $this->$method = array_shift($args);
                break;
            default:
        }
    }
}
