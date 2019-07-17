<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

abstract class Monkeys_Controller_Action extends Zend_Controller_Action
{
    /**
    * not prepended with "_" because their view counterparts can't have "_" prepended
    */
    protected $user;
    protected $targetUser;

    protected $_config;
    protected $_numCols = 2;
    protected $underMaintenance = false;

    public function init()
    {
        if (!Zend_Registry::isRegistered('user')) {
            // guest user
            $users = new Users();
            $user = $users->createRow();
            Zend_Registry::set('user', $user);
        }

        $this->_config = Zend_Registry::get('config');

        $this->user = Zend_Registry::get('user');
        $this->view->user = $this->user;

        $this->_validateTargetUser();
        $this->_checkMaintenanceMode();

        $this->view->controller = $this;

        $this->view->addHelperPath('libs/Monkeys/View/Helper', 'Monkeys_View_Helper');
        $this->_setScriptPaths();
        $this->_setBase();
        $this->view->numCols = $this->_numCols;

        if ($this->getRequest()->isXmlHttpRequest()) {
            $slowdown = $this->_config->environment->ajax_slowdown;
            if ($slowdown > 0) {
                sleep($slowdown);
            }
            $this->_helper->layout->disableLayout();
        } else {
            $this->view->version = Setup::VERSION;
            $this->view->messages = $this->_helper->FlashMessenger->getMessages();
            $this->view->loaderCombine = $this->_config->environment->YDN? 'true' : 'false';
            $this->view->loaderBase = $this->_config->environment->YDN?
                                        'http://yui.yahooapis.com/2.6.0/build/'
                                        : $this->view->base . '/javascript/yui/';
        }
    }

    private function _setScriptPaths()
    {
        if (($template = $this->_config->environment->template) == 'default') {
            return;
        }

        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $view = $viewRenderer->view;
        $scriptPaths = $view->getScriptPaths();
        $oldPath = $scriptPaths[0];
        $newPath = substr($oldPath, 0, strrpos($oldPath, DIRECTORY_SEPARATOR, -2) + 1) . "scripts_$template" . DIRECTORY_SEPARATOR;
        $view->addScriptPath($newPath);
    }

    private function _setBase()
    {
        if ($this->_config->subdomain->enabled) {
            $protocol = $this->_getProtocol();

            $this->view->base = "$protocol://"
                                   . ($this->_config->subdomain->use_www? 'www.' : '')
                                   . $this->_config->subdomain->hostname;
        } else {
            $this->view->base = $this->view->getBase();
        }
    }

    private function _validateTargetUser()
    {
        if (Zend_Registry::isRegistered('targetUser')) {
            // used by unit tests to inject the target user
            $this->targetUser = Zend_Registry::get('targetUser');
        } else {
            $userId = $this->_getParam('userid');

            if (is_null($userId)) {
                $this->targetUser = $this->user;
            } elseif ($this->_getParam('userid') == 0) {
                $users = new Users();
                $this->targetUser = $users->createRow();
            } else {
                if ($userId != $this->user->id && $this->user->role != User::ROLE_ADMIN) {
                    $this->_helper->FlashMessenger->addMessage('Error: Invalid user id');
                    $this->_redirect('profile/edit');
                }
                $users = new Users();
                $this->targetUser = $users->getRowInstance($userId);
            }
        }

        $this->view->targetUser = $this->targetUser;
    }

    protected function _checkMaintenanceMode()
    {
        if (!$this->_config->environment->installed) {
            $this->underMaintenance = true;
            $this->view->underMaintenance = false;
            return;
        }

        $settings = new Settings();
        $this->underMaintenance = $settings->isMaintenanceMode();
        $this->view->underMaintenance = $this->underMaintenance;
    }

    protected function _redirectToNormalConnection()
    {
        if ($this->_config->SSL->enable_mixed_mode) {
            $this->_redirect('http://' . $_SERVER['HTTP_HOST'] . $this->view->base);
        } else {
            $this->_redirect('');
        }
    }

    protected function _redirectForMaintenance($backToNormalConnection = false)
    {
        if ($backToNormalConnection) {
            $this->_redirectToNormalConnection('');
        } else {
            $this->_redirect('');
        }
    }

    protected function _redirect($url, $options = array())
    {
        Zend_Registry::get('logger')->log("redirected to '$url'", Zend_Log::DEBUG);

        return parent::_redirect($url, $options);
    }

    protected function _getProtocol()
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            return 'https';
        } else {
            return 'http';
        }
    }
}
