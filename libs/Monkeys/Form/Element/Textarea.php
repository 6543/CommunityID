<?php

class Monkeys_Form_Element_Textarea extends Zend_Form_Element_Textarea
{
    private $_decorator;

    public function __construct($spec, $options = array())
    {
        $options = array_merge($options, array('disableLoadDefaultDecorators' =>true));
        parent::__construct($spec, $options);

        $this->_decorator = new Monkeys_Form_Decorator_Composite();
        $this->addDecorator($this->_decorator);
    }

    public function setDecoratorOptions(array $options)
    {
        $this->_decorator->setOptions($options);

        return $this;
    }
}

