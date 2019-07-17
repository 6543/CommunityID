<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

define('APP_DIR', realpath(dirname(__FILE__) . '/../..'));
require APP_DIR . '/Application.php';

Application::setIncludePath();
Application::setAutoLoader();
Application::setConfig();
Application::setErrorReporting();
Application::setLogger();
$translate = Application::setI18N();

?>

YAHOO.namespace("commid");
COMMID = YAHOO.commid;

// WARNING: DO NOT PUT A COMMA AFTER THE LAST ELEMENT (breaks IE)

COMMID.lang = {
    "Name": "<?= $translate->translate('Name') ?>",
    "Registration": "<?= $translate->translate('Registration') ?>",
    "Status": "<?= $translate->translate('Status') ?>",
    "profile": "<?= $translate->translate('profile') ?>",
    "delete": "<?= $translate->translate('delete') ?>",
    "Site": "<?= $translate->translate('Site') ?>",
    "view info exchanged": "<?= $translate->translate('view info exchanged') ?>",
    "deny": "<?= $translate->translate('deny') ?>",
    "allow": "<?= $translate->translate('allow') ?>",
    "Are you sure you wish to send this message to ALL users?": "<?= $translate->translate('Are you sure you wish to send this message to ALL users?') ?>",
    "Are you sure you wish to deny trust to this site?": "<?= $translate->translate('Are you sure you wish to deny trust to this site?') ?>",
    "operation failed": "<?= $translate->translate('operation failed') ?>",
    "Trust to the following site has been granted:": "<?= $translate->translate('Trust to the following site has been granted:') ?>",
    "Trust the following site has been denied:": "<?= $translate->translate('Trust the following site has been denied:') ?>",
    "ERROR. The server returned:": "<?= $translate->translate('ERROR. The server returned:') ?>",
    "Your relationship with the following site has been deleted:": "<?= $translate->translate('Your relationship with the following site has been deleted:') ?>",
    "The history log has been cleared": "<?= $translate->translate('The history log has been cleared') ?>",
    "Are you sure you wish to allow access to this site?": "<?= $translate->translate('Are you sure you wish to allow access to this site?') ?>",
    "Are you sure you wish to delete your relationship with this site?": "<?= $translate->translate('Are you sure you wish to delete your relationship with this site?') ?>",
    "Are you sure you wish to delete all the History Log?": "<?= $translate->translate('Are you sure you wish to delete all the History Log?') ?>",
    "Are you sure you wish to delete the user": "<?= $translate->translate('Are you sure you wish to delete the user') ?>",
    "Are you sure you wish to delete all the unconfirmed accounts?": "<?= $translate->translate('Are you sure you wish to delete all the unconfirmed accounts?') ?>",
    "Date": "<?= $translate->translate('Date') ?>",
    "Result": "<?= $translate->translate('Result') ?>",
    "No records found.": "<?= $translate->translate('No records found.') ?>",
    "Loading...": "<?= $translate->translate('Loading...') ?>",
    "Data error.": "<?= $translate->translate('Data error.') ?>",
    "Click to sort ascending": "<?= $translate->translate('Click to sort ascending') ?>",
    "Click to sort descending": "<?= $translate->translate('Click to sort descending') ?>",
    "Authorized": "<?= $translate->translate('Authorized') ?>",
    "Denied": "<?= $translate->translate('Denied') ?>",
    "of": "<?= $translate->translate('of') ?>",
    "next": "<?= $translate->translate('next') ?>",
    "prev": "<?= $translate->translate('prev') ?>",
    "IP": "<?= $translate->translate('IP') ?>",
    "Delete unconfirmed accounts older than how many days?": "<?= $translate->translate('Delete unconfirmed accounts older than how many days?') ?>",
    "The value entered is incorrect": "<?= $translate->translate('The value entered is incorrect') ?>",
    "Send reminder to accounts older than how many days?": "<?= $translate->translate('Send reminder to accounts older than how many days?') ?>",
    "Are you sure you wish to delete this article?": "<?= $translate->translate('Are you sure you wish to delete this article?') ?>"
}
