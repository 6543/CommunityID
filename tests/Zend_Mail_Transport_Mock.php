<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/


class Zend_Mail_Transport_Mock extends Zend_Mail_Transport_Abstract
{
    /**
     * @var Zend_Mail
     */
    public $mail       = null;
    public $returnPath = null;
    public $subject    = null;
    public $from       = null;
    public $headers    = null;
    public $called     = false;

    public function _sendMail()
    {
        $this->mail       = $this->_mail;
        $this->subject    = $this->_mail->getSubject();
        $this->from       = $this->_mail->getFrom();
        $this->returnPath = $this->_mail->getReturnPath();
        $this->headers    = $this->_headers;
        $this->called     = true;
    }
}
