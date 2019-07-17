<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class IdentityController extends Monkeys_Controller_Action
{
    protected $_numCols = 1;

    public function indexAction()
    {
        throw new Monkeys_BadUrlException($this->getRequest()->getRequestUri());
    }

    public function idAction()
    {
        $currentUrl = Zend_OpenId::selfURL();

        if ($this->_config->subdomain->enabled) {
            $protocol = $this->_getProtocol();
            preg_match('#(.*)\.'.$this->_config->subdomain->hostname.'#', $currentUrl, $matches);

            $this->view->headLink()->headLink(array(
                        'rel'   => 'openid.server',
                        'href'  => "$protocol://"
                                   . ($this->_config->subdomain->use_www? 'www.' : '')
                                   . $this->_config->subdomain->hostname
                                   . '/openid/provider'
            ));
            $this->view->headLink()->headLink(array(
                        'rel'   => 'openid2.provider',
                        'href'  => "$protocol://"
                                   . ($this->_config->subdomain->use_www? 'www.' : '')
                                   . $this->_config->subdomain->hostname
                                   . '/openid/provider'
            ));
        } else {
            preg_match('#(.*)/identity/#', $currentUrl, $matches);

            $this->view->headLink()->headLink(array(
                        'rel'   => 'openid.server',
                        'href'  => $matches[1] . '/openid/provider',
            ));
            $this->view->headLink()->headLink(array(
                        'rel'   => 'openid2.provider',
                        'href'  => $matches[1] . '/openid/provider',
            ));
        }

        $this->view->idUrl = $currentUrl;
    }
}
