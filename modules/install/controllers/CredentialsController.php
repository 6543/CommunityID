<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class Install_CredentialsController extends CommunityID_Controller_Action
{
    protected $_numCols = 1;

    public function indexAction()
    {
        if ($errors = $this->_getErrors()) {
            return $this->_forward('index', 'permissions', null, array('errors' => $errors));
        }

        $appSession = Zend_Registry::get('appSession');
        if (isset($appSession->installForm)) {
            $this->view->form = $appSession->installForm;
            unset($appSession->installForm);
        } else {
            $this->view->form = new Install_Form_Install();
        }
    }
    
    public function saveAction()
    {
        $form = new Install_Form_Install();
        $formData = $this->_request->getPost();

        $form->populate($formData);
        if (!$form->isValid($formData)) {
            return $this->_forwardFormError($form);
        }

        if (!$this->_connectToDbEngine($form)) {
            $this->_helper->FlashMessenger->addMessage('We couldn\'t connect to the database using those credentials.');
            $this->_helper->FlashMessenger->addMessage('Please verify and try again.');
            return $this->_forwardFormError($form);
        }

        if (!$this->_createDbIfMissing($form)) {
            $this->_helper->FlashMessenger->addMessage(
                'The connection to the database engine worked, but the database "' . $form->getValue('dbname') . '" doesn\'t exist or the provided user doesn\'t have access to it. An attempt was made to create it, but the provided user doesn\'t have permissions to do so either. Please create it yourself and try again.');
            return $this->_forwardFormError($form);
        }

        $this->_importDb();

        if (!$this->_writeConfig($form)) {
            throw new Exception('Couldn\'t write to config file ' . APP_DIR . DIRECTORY_SEPARATOR . 'config.php');
        }

        $this->_forward('index', 'complete');
    }

    private function _connectToDbEngine(Install_Form_Install $form)
    {
        $this->_config->database->params->host      = $form->getValue('hostname');
        $this->_config->database->params->username  = $form->getValue('dbusername');
        $this->_config->database->params->password  = $form->getValue('dbpassword');

        // setting dbname to null makes Zend_Db::getConnection() try to connect to the db engine
        // without attempting to connect to the dbname
        $this->_config->database->params->dbname    = null;

        return Application::setDatabase();
    }

    private function _createDbIfMissing(Install_Form_Install $form)
    {
        $this->_config->database->params->host      = $form->getValue('hostname');
        $this->_config->database->params->username  = $form->getValue('dbusername');
        $this->_config->database->params->password  = $form->getValue('dbpassword');

        $this->_config->database->params->dbname    = $form->getValue('dbname');

        if (!Application::setDatabase()) {
            try {
                $this->_config->database->params->dbname    = null;
                Application::setDatabase();

                // binding doesn't work here for some reason
                Zend_Registry::get('db')->getConnection()->query("CREATE DATABASE `" . $form->getValue('dbname') . "`");
                $this->_config->database->params->dbname = $form->getValue('dbname');
                Application::setDatabase();
            } catch (PDOException $e) {    // when using PDO, it throws this exception, not Zend's
                return false;
            }
        }

        return true;
    }

    private function _writeConfig(Install_Form_Install $form)
    {
        $this->_config->environment->installed = true;
        $this->_config->email->supportemail = $form->getValue('supportemail');

        $configTemplate = file_get_contents(APP_DIR . DIRECTORY_SEPARATOR . 'config.template.php');
        $replace = array(
            '{environment.installed}'             => $this->_config->environment->installed? 'true' : 'false',
            '{environment.session_name}'          => $this->_config->environment->session_name,
            '{environment.production}'            => $this->_config->environment->production? 'true' : 'false',
            '{environment.YDN}'                   => $this->_config->environment->YDN? 'true' : 'false',
            '{environment.ajax_slowdown}'         => $this->_config->environment->ajax_slowdown,
            '{environment.keep_history_days}'     => $this->_config->environment->keep_history_days,
            '{environment.registrations_enabled}' => $this->_config->environment->registrations_enabled? 'true' : 'false',
            '{environment.locale}'                => $this->_config->environment->locale,
            '{environment.template}'              => $this->_config->environment->template,
            '{logging.location}'                  => $this->_config->logging->location,
            '{logging.level}'                     => $this->_config->logging->level,
            '{subdomain.enabled}'                 => $this->_config->subdomain->enabled? 'true' : 'false',
            '{subdomain.hostname}'                => $this->_config->subdomain->hostname,
            '{subdomain.use_www}'                 => $this->_config->subdomain->use_www? 'true' : 'false',
            '{SSL.enable_mixed_mode}'             => $this->_config->SSL->enable_mixed_mode? 'true' : 'false',
            '{database.adapter}'                  => $this->_config->database->adapter,
            '{database.params.host}'              => $this->_config->database->params->host,
            '{database.params.dbname}'            => $this->_config->database->params->dbname,
            '{database.params.username}'          => $this->_config->database->params->username,
            '{database.params.password}'          => $this->_config->database->params->password,
            '{email.supportemail}'                => $this->_config->email->supportemail,
            '{email.adminemail}'                  => $this->_config->email->adminemail,
            '{email.transport}'                   => $this->_config->email->transport,
            '{email.host}'                        => $this->_config->email->host,
            '{email.auth}'                        => $this->_config->email->auth,
            '{email.username}'                    => $this->_config->email->username,
            '{email.password}'                    => $this->_config->email->password,
        );
        $configTemplate = str_replace(array_keys($replace), array_values($replace), $configTemplate);

        return @file_put_contents(APP_DIR . DIRECTORY_SEPARATOR . 'config.php', $configTemplate);
    }

    private function _importDb()
    {
        $this->_runSqlFILE('final.sql');
    }

    function _runSqlFile($fileName) {
        $fp = fopen(APP_DIR . DIRECTORY_SEPARATOR . "/setup/$fileName", 'r');
        $query = '';
        $db = Zend_Registry::get('db');
        while (!feof($fp)) {
           $line = trim(fgets($fp)); 

           // skip SQL comments
           if (substr($line, 0, 2) == '--') {
               continue;
           }

           $query .= $line;
           if ((substr($line, -1, 1) == ';' || feof($fp)) && $query != '') {
               // I had to resort to a direct call because regexes inside the Zend Framework are segfaulting with the long queries in sampleData.sql
               //$this->db->query($query);
               $db->getConnection()->query($query);

               $query = '';
           }
        }
        fclose($fp);
    }

    private function _forwardFormError(Install_Form_Install $form)
    {
        $appSession = Zend_Registry::get('appSession');
        $appSession->installForm = $form;
        $this->_redirect('/install/credentials');
        return;
    }

    private function _getErrors()
    {
        $errors = array();
        $webServerUser = $this->_getProcessUser();

        if (!is_writable(APP_DIR) && !is_writable(APP_DIR . DIRECTORY_SEPARATOR . 'config.php')) {
            $errors[] = $this->view->translate('The directory where Community-ID is installed must be writable by the web server user (%s). Another option is to create an EMPTY config.php file that is writable by that user.', $webServerUser);
        }
        if (!is_writable(WEB_DIR . '/captchas')) {
            $errors[] = $this->view->translate('The directory "captchas" under the web directory for Community-ID must be writable by the web server user (%s)', $webServerUser);
        }
        if (!extension_loaded('mysqli')) {
            $errors[] = $this->view->translate('You need to have the %s extension installed', '<a href="http://www.php.net/manual/en/mysqli.installation.php">MySQLi</a>');
        }

        return $errors;
    }

    private function _getProcessUser()
    {
        if (!function_exists('posix_getpwuid')) {
            // we're on windows
            return getenv('USERNAME');
        }

        $processUser = posix_getpwuid(posix_geteuid());

        return $processUser['name'];
    }
}
