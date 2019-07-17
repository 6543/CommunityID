<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since Textroller 0.9
* @package TextRoller
* @packager Keyboard Monkeys
*/


class TestRequest extends Zend_Controller_Request_HttpTestCase
{
    public function __construct($actionStr)
    {
        parent::__construct('http://localhost/communityid' . $actionStr);

        $this->setBaseUrl('/communityid');
    }
}
