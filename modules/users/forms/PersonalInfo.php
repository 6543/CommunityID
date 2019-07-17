<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/


class Users_Form_PersonalInfo extends Zend_Form
{
    private $_sregProps;
    private $_formElements = array();

    public function __construct($options = null, $user = null, $sregProps = null)
    {
        $this->_sregProps = $sregProps;

        $fields = new Model_Fields();
        $fieldsArr = $fields->getValues($user);
        for ($i = 0; $i < count($fieldsArr); $i++) {
            $this->_formElements[$fieldsArr[$i]->openid] = array(
                'field'     => $fieldsArr[$i],
                'element'   => $fieldsArr[$i]->getFormElement(),
            );
        }

        parent::__construct($options);
    }

    public function init()
    {
        if ($this->_sregProps) {
            foreach ($this->_sregProps as $fieldName => $mandatory) {
                if (isset($this->_formElements[$fieldName])) {
                    $element = $this->_formElements[$fieldName]['element'];
                    if ($mandatory) {
                        // override label
                        $element->setLabel($this->_formElements[$fieldName]['field']->name);
                        $element->setRequired(true);
                    }
                } else {
                    $element = new Monkeys_Form_Element_Text("openid.sreg.$fieldName");
                    $element->setLabel($fieldName);
                    if ($mandatory) {
                        $element->setRequired(true);
                    }
                }

                // user openid standard notation for the field names, instead of
                // our field IDs.
                $element->setName('openid_sreg_' . $fieldName);

                $this->addElement($element);
            }
        } else {
            foreach ($this->_formElements as $formElement) {
                $this->addElement($formElement['element']);
            }
        }
    }

    /**
    * This removes the "openid_sreg_" prefix from the field names
    */
    public function getUnqualifiedValues()
    {
        $values = array();
        foreach ($this->getValues() as $key => $value) {
            $values[substr($key, 12)] = $value;
        }

        return $values;
    }
}
