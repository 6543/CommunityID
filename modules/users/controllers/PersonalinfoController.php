<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class Users_PersonalinfoController extends CommunityID_Controller_Action
{
    public function preDispatch()
    {
        if ($this->user->role == Users_Model_User::ROLE_ADMIN) {
            throw new Monkeys_AccessDeniedException();
        }
    }

    public function indexAction()
    {
        $this->_helper->actionStack('index', 'login', 'users');
    }

    public function showAction()
    {
        $fields = new Model_Fields();
        $this->view->fields = $fields->getValues($this->user);
    }

    public function editAction()
    {
        $appSession = Zend_Registry::get('appSession');
        if (isset($appSession->personalInfoForm)) {
            $this->view->fields = $appSession->personalInfoForm->getElements();
            unset($appSession->personalInfoForm);
        } else {
            $personalInfoForm = new Users_Form_PersonalInfo(null, $this->user);
            $this->view->fields = $personalInfoForm->getElements();
        }
    }

    public function saveAction()
    {
        $form = new Users_Form_PersonalInfo(null, $this->user);
        $formData = $this->_request->getPost();

        $form->populate($formData);
        if (!$form->isValid($formData)) {
            $appSession = Zend_Registry::get('appSession');
            $appSession->personalInfoForm = $form;
            $this->_forward('edit');
            return;
        }

        $fieldsValues = new Model_FieldsValues();
        $fieldsValues->deleteForUser($this->user);

        foreach ($form->getValues() as $fieldName => $fieldValue) {
            if (!$fieldValue) {
                continue;
            }

            $fieldsValue = $fieldsValues->createRow();
            $fieldsValue->user_id = $this->user->id;

            list(, $fieldId) = explode('_', $fieldName);
            $fieldsValue->field_id = $fieldId;

            $fieldsValue->value = $fieldValue;

            $fieldsValue->save();
        }


        $this->_forward('show');
    }
}
