<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class Setup
{
    const VERSION = '1.0.0_RC2';

    public static $config;
    public static $logger;
    public static $mockLogger;
    public static $acl;
    public static $front;

    /**
    * Used in unit tests
    */
    public static function cleanUp()
    {
        Zend_Registry::_unsetInstance();
        Zend_Layout::resetMvcInstance();
        Zend_Controller_Action_HelperBroker::resetHelpers();
    }

    public static function setIncludePath()
    {
        $pathList = array(
            get_include_path(),
            APP_DIR,
            APP_DIR.'/libs',
            APP_DIR.'/modules/default/models',
            APP_DIR.'/modules/default/forms',
            APP_DIR.'/modules/users/models',
            APP_DIR.'/modules/users/forms',
            APP_DIR.'/modules/stats/models',
            APP_DIR.'/modules/install/forms',
        );
        if (!set_include_path(implode(PATH_SEPARATOR, $pathList))) {
            die('ERROR: couldn\'t execute PHP\'s set_include_path() function in your system.'
                .' Please ask your system admin to enable that functionality.');
        }
    }

    public static function setAutoLoader()
    {
        require_once 'Zend/Loader.php';
        Zend_Loader::registerAutoload();
    }

    public static function setConfig()
    {
        if (file_exists(APP_DIR . DIRECTORY_SEPARATOR . 'config.php')) {
            $configFile = APP_DIR . DIRECTORY_SEPARATOR . 'config.php';
        } else {
            $configFile = APP_DIR . DIRECTORY_SEPARATOR . 'config.default.php';
        }

        $config = array();
        require $configFile;
        self::$config = new Zend_Config($config, array('allowModifications' => true));
        if(self::$config->environment->installed === null) {
            $configFile = APP_DIR . DIRECTORY_SEPARATOR . 'config.default.php';
            require $configFile;
            self::$config = new Zend_Config($config, array('allowModifications' => true));
        }

        // @todo: remove this when all interconnected apps use the same LDAP source
        self::$config->environment->app = 'communityid';

        Zend_Registry::set('config', self::$config);
    }

    public static function setErrorReporting()
    {
        ini_set('log_errors', 'Off');
        if (self::$config->environment->production) {
            error_reporting(E_ALL & E_NOTICE);
            ini_set('display_errors', 'Off');
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors', 'On');
        }
    }

    public static function setLogger($addMockWriter = false)
    {
        self::$logger = new Zend_Log();
        if (self::$config->logging->level == 0) {
            self::$logger->addWriter(new Zend_Log_Writer_Null(APP_DIR . '/log.txt'));
        } else {
            if (is_writable(self::$config->logging->location)) {
                $file = self::$config->logging->location;
            } else if (!is_writable(APP_DIR . DIRECTORY_SEPARATOR . self::$config->logging->location)) {
                throw new Exception('Couldn\'t find log file, or maybe it\'s not writable');
            } else {
                $file = APP_DIR . DIRECTORY_SEPARATOR . self::$config->logging->location;
            }

            self::$logger->addWriter(new Zend_Log_Writer_Stream($file));
            if ($addMockWriter) {
                self::$mockLogger = new Zend_Log_Writer_Mock();
                self::$logger->addWriter(self::$mockLogger);
            }
        }
        self::$logger->addFilter(new Zend_Log_Filter_Priority((int)self::$config->logging->level));
        Zend_Registry::set('logger', self::$logger);
    }

    public static function logRequest()
    {
        if (isset($_SERVER['REQUEST_URI'])) {
            self::$logger->log('REQUESTED URI: ' . $_SERVER['REQUEST_URI'], Zend_Log::INFO);
        } else {
            self::$logger->log('REQUESTED THROUGH CLI: ' . $GLOBALS['argv'][0], Zend_Log::INFO);
        }

        if (isset($_POST) && $_POST) {
            self::$logger->log('POST payload: ' . print_r($_POST, 1), Zend_Log::INFO);
        }
    }

    public static function setDatabase()
    {
        self::$config->database->params->driver_options = array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true);
        $db = Zend_Db::factory(self::$config->database);
        if (self::$config->logging->level == Zend_Log::DEBUG) {
            $profiler = new Monkeys_Db_Profiler();
            $db->setProfiler($profiler);
        }
        Zend_Db_Table_Abstract::setDefaultAdapter($db);
        Zend_Registry::set('db', $db);

        try {
            $db->getConnection();
            return true;
        } catch (Zend_Db_Adapter_Exception $e) {
            return false;
        }
    }

    public static function setSession()
    {
        // The framework doesn't provide yet a clean way of doing this
        if (isset($_POST['rememberme'])) {
            Zend_Session::rememberMe();
        }

        // ZF still doesn't have facilities for session_name().
        session_name(self::$config->environment->session_name);

        $appSession = new Zend_Session_Namespace('Default');
        if (is_null($appSession->messages)) {
            $appSession->messages = array();
        }
        Zend_Registry::set('appSession', $appSession);
    }

    public static function setAcl()
    {
        self::$acl = new Zend_Acl();
        require 'Acl.php';

        foreach ($privileges as $module => $moduleConfig) {
            foreach ($moduleConfig as $controller => $controllerConfig) {
                self::$acl->add(new Zend_Acl_Resource($module . '_' . $controller));
                foreach ($controllerConfig as $action => $role) {
                    self::$acl->allow($role, $module . '_' . $controller, $action);
                }
            }
        }
        Zend_Registry::set('acl', self::$acl);
    }

    public static function setI18N()
    {
        if (self::$config->environment->locale == 'auto') {
            $locale = new Zend_Locale(Zend_Locale::BROWSER);
        } else {
            $locale = new Zend_Locale(self::$config->environment->locale);
        }
        Zend_Registry::set('Zend_Locale', $locale);
        $translate = new Zend_Translate('gettext',
                                        APP_DIR . '/languages',
                                        $locale->toString(),
                                        array(
                                            'scan'              => Zend_Translate::LOCALE_DIRECTORY,
                                            'disableNotices'    => true));
        Zend_Registry::set('Zend_Translate', $translate);

        return $translate;
    }

    public static function setLayout()
    {
        $template = self::$config->environment->template;

        // Hack: Explicitly add the ViewRenderer, so that when an exception is thrown,
        // the layout is not shown (should be better handled in ZF 1.6)
        // @see http://framework.zend.com/issues/browse/ZF-2993?focusedCommentId=23121#action_23121
        Zend_Controller_Action_HelperBroker::addHelper(new Zend_Controller_Action_Helper_ViewRenderer());

        Zend_Layout::startMvc(array(
            'layoutPath'    => $template == 'default'? APP_DIR.'/views/layouts' : APP_DIR."/views/layouts_$template",
        ));
    }

    public static function setFrontController()
    {
        self::$front = Zend_Controller_Front::getInstance();
        self::$front->registerPlugin(new Monkeys_Controller_Plugin_Auth(self::$acl));
        self::$front->addModuleDirectory(APP_DIR.'/modules');

        $router = self::$front->getRouter();

        if (self::$config->subdomain->enabled) {
            if (self::$config->subdomain->use_www) {
                $reqs = array('username' => '([^w]|w[^w][^w]|ww[^w]|www.+).*');
            } else {
                $reqs = array();
            }
            $hostNameRoute = new Zend_Controller_Router_Route_Hostname(
                ':username.' . self::$config->subdomain->hostname,
                array(
                    'module'        => 'default',
                    'controller'    => 'identity',
                    'action'        => 'id',
                ),
                $reqs
            );
            $router->addRoute('hostNameRoute', $hostNameRoute);
        }

        $route = new Zend_Controller_Router_Route(
            'identity/:userid',
            array(
                'module'        => 'default', 
                'controller'    => 'identity', 
                'action'        => 'id', 
            ),
            array('userid' => '[\w-]*')
        );
        $router->addRoute('identityRoute', $route);
    }

    public static function dispatch()
    {
        self::$front->dispatch();
    }
}

/**
* this is just a global function used to mark translations
*/
function translate() {}
