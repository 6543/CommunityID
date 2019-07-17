<?php

#
#  -------  ENVIRONMENT ------------
#
$config['environment']['installed']         = {environment.installed};
$config['environment']['session_name']      = '{environment.session_name}';
$config['environment']['production']        = {environment.production};
$config['environment']['YDN']               = {environment.YDN};
$config['environment']['ajax_slowdown']     = {environment.ajax_slowdown};
$config['environment']['keep_history_days'] = {environment.keep_history_days};

# Enable / Disable account self-registration.
$config['environment']['registrations_enabled'] = {environment.registrations_enabled};

# use auto to use the browser's language
$config['environment']['locale']            = '{environment.locale}';

$config['environment']['template']          = '{environment.template}';


#
#  -------  LOGGING ------------
#
# Enter a path relative to the installation's root dir, or an absolute path.
# The file must exist, and be writable by the web server user
$config['logging']['location']              = '{logging.location}';

# Log level. You can use any of these constants or numbers:
# Zend_Log::EMERG   = 0;  // Emergency: system is unusable
# Zend_Log::ALERT   = 1;  // Alert: action must be taken immediately
# Zend_Log::CRIT    = 2;  // Critical: critical conditions
# Zend_Log::ERR     = 3;  // Error: error conditions
# Zend_Log::WARN    = 4;  // Warning: warning conditions
# Zend_Log::NOTICE  = 5;  // Notice: normal but significant condition
# Zend_Log::INFO    = 6;  // Informational: informational messages (requested URL, POST payloads)
# Zend_Log::DEBUG   = 7;  // Debug: debug messages (database queries)
$config['logging']['level']                 = {logging.level};


#
#  -------  Subdomain openid URL configuration ------------
#
# Set to true for the OpenID URL identifying the user to have the form username.hostname
# All other URLs for non-OpenID transactions will be handled under the domain name, without a subdomain.
# Take a look at the wiki for more instructions on how to set this up.
# Warning: if you change this, all current OpenId credentials will become invalid.
$config['subdomain']['enabled']             = {subdomain.enabled};
# Enter your server's hostname (without www and without an ending slash)
# Community-id must be installed directly at this hostname's root web dir
$config['subdomain']['hostname']            = '{subdomain.hostname}';
# Set to true if your regular non-OpenId URLs are prepended with www
$config['subdomain']['use_www']             = {subdomain.use_www};


#
#  -------  SSL ------------
#
# enable_mixed_mode: Set to true when you want to have the user authentication and all OpenID transactions
# to occur under SSL, and the rest to remain under a regular non-encrypted connection.
# Warning: if you change this, all current OpenId credentials will become invalid
$config['SSL']['enable_mixed_mode']         = {SSL.enable_mixed_mode};


#
#  -------  DATABASE ------------
#
$config['database']['adapter']              = '{database.adapter}';
$config['database']['params']['host']       = '{database.params.host}';
$config['database']['params']['dbname']     = '{database.params.dbname}';
$config['database']['params']['username']   = '{database.params.username}';
$config['database']['params']['password']   = '{database.params.password}';


#
#  -------  E-MAIL ------------
#
$config['email']['supportemail']            = '{email.supportemail}';

# this email will receive any error notification
$config['email']['adminemail']              = '{email.adminemail}';

$config['email']['transport']               = '{email.transport}';
$config['email']['host']                    = '{email.host}';
$config['email']['auth']                    = '{email.auth}';
$config['email']['username']                = '{email.username}';
$config['email']['password']                = '{email.password}';
