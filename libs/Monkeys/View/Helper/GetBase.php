<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class Monkeys_View_Helper_GetBase
{
    public function getBase()
    {
        $ctrl = Zend_Controller_Front::getInstance();    
        $baseUrl = $ctrl->getBaseUrl();
        $url = rtrim($baseUrl, '/');

        if (substr($baseUrl, strlen($baseUrl) - 9) == 'index.php') {
            $url = substr($baseUrl, 0, strlen($baseUrl) - 10);
        }

        return $url; 
    }
}
