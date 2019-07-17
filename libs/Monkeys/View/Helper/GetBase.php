<?php

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
