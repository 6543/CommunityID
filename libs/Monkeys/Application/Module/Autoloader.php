<?php

class Monkeys_Application_Module_Autoloader extends Zend_Application_Module_Autoloader
{
    public function __construct($options)
    {
        parent::__construct($options);
        $this->addResourceType('controllerHelpers', 'controllers', 'Controller');
    }
}
