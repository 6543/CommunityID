<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class Install_UpgradeController extends CommunityID_Controller_Action
{
    protected $_numCols = 1;

    public function indexAction()
    {
        // double check upgrade is necessary in case someone access this action directly
        if (!$this->_needsUpgrade()) {
            $this->_redirect('');
            return;
        }

        $appSession = Zend_Registry::get('appSession');
        if (isset($appSession->loginForm)) {
            $this->view->loginForm = $appSession->loginForm;
            unset($appSession->loginForm);
        } else {
            $this->view->loginForm = new Install_Form_UpgradeLogin();
        }
    }

    public function proceedAction()
    {
        // double check upgrade is necessary in case someone access this action directly
        if (!$this->_needsUpgrade()) {
            $this->_redirect('');
            return;
        }

        $form = new Install_Form_UpgradeLogin();
        $formData = $this->_request->getPost();
        $form->populate($formData);

        if (!$form->isValid($formData)) {
            $appSession = Zend_Registry::get('appSession');
            $appSession->loginForm = $form;
            $this->_forward('index');
            return;
        }

        $users = new Users_Model_Users();
        $result = $users->authenticate($this->_request->getPost('username'),
            $this->_request->getPost('password'));

        if (!$result) {
            $this->_helper->FlashMessenger->addMessage($this->view->translate('Invalid credentials'));
            $this->_redirect('index');
            return;
        }

        $user = $users->getUser();
        if ($user->role != Users_Model_User::ROLE_ADMIN) {
            Zend_Auth::getInstance()->clearIdentity();
            $this->_helper->FlashMessenger->addMessage($this->view->translate('Invalid credentials'));
            $this->_redirect('index');
            return;
        }

        $this->_runUpgrades(true);
        $upgradedVersion = $this->_runUpgrades(false);

        $this->_helper->FlashMessenger->addMessage($this->view->translate('Upgrade was successful. You are now on version %s', $upgradedVersion));

        // we need to logout user in case the user table changed
        Zend_Auth::getInstance()->clearIdentity();
        Zend_Session::forgetMe();

        $this->_redirect('/');
    }

    private function _runUpgrades($onlyCheckFiles = true)
    {
        require 'setup/versions.php';

        $includeFiles = false;
        $db = Zend_Registry::get('db');
        foreach ($versions as $version) {
            if ($version == $this->_getDbVersion()) {
                $includeFiles = true;
                continue;
            }

            if (!$includeFiles) {
                continue;
            }

            $fileName = APP_DIR . '/setup/upgrade_'.$version.'.sql';

            if ($onlyCheckFiles) {
                if (!file_exists($fileName)) {
                    $this->_helper->FlashMessenger->addMessage($this->view->translate('Correct before upgrading: File %s is required to proceed', $fileName));
                    $this->_redirect('index');
                    return;
                }
                continue;
            }

            $query = '';
            $lines = file($fileName);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line != '') {
                    $query .= $line;
                }
                if (substr($line, -1) == ';') {
                    try {
                        $db->query($query);
                    } catch (Zend_Db_Statement_Mysqli_Exception $e) {
                        Zend_Registry::get('logger')->log("Error in this query: $query", Zend_Log::ERR);
                        throw $e;
                    }
                    $query = '';
                }
            }
        }

        return $version;
    }
}
