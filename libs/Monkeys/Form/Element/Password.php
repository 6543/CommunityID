<?php

class Monkeys_Form_Element_Password extends Zend_Form_Element_Password
{
    public function __construct($spec, $options = array())
    {
        $options = array_merge($options, array('disableLoadDefaultDecorators' =>true));
        parent::__construct($spec, $options);

        $this->addDecorator(new Monkeys_Form_Decorator_Composite());
    }
}

