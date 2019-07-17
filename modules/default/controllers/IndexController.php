<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class IndexController extends Monkeys_Controller_Action
{
    const NEWS_CONTENT_MAX_LENGTH = 100;

    public function indexAction()
    {
        $scriptsDir = $this->view->getScriptPaths();

        $locale = Zend_Registry::get('Zend_Locale');
        // render() changes _ to -
        $locale = str_replace('_', '-', $locale);
        $localeElements = explode('-', $locale);

        $view = false;
        foreach ($scriptsDir as $scriptDir) {
            if (file_exists($scriptDir."index/subheader-$locale.phtml")) {
                $view = "subheader-$locale";
                break;
            } else if (count($localeElements == 2)
                    && file_exists($scriptDir."index/subheader-".$localeElements[0].".phtml")) {
                $view = 'subheader-'.$localeElements[0];
                break;
            }
        }
        if (!$view) {
            $view = 'subheader-en';
        }

        $this->getResponse()->insert('subHeader', $this->view->render("index/$view.phtml"));

        $this->_helper->actionStack('index', 'login', 'users');

        try {
            $feed = Zend_Feed::import($this->_config->news_feed->url);
        } catch (Zend_Exception $e) {
            // feed import failed
            $obj = new StdClass();
            $obj->link = array('href' => '');
            $obj->title = $this->view->translate('Could not retrieve news items');
            $obj->updated = '';
            $obj->content = '';
            $feed = array($obj);
        }

        $this->view->news = array();
        $i = 0;
        foreach ($feed as $item) {
            if ($i++ >= $this->_config->news_feed->num_items) {
                break;
            }

            if (strlen($item->content) > self::NEWS_CONTENT_MAX_LENGTH) {
                $item->content = substr($item->content, 0, self::NEWS_CONTENT_MAX_LENGTH)
                               . '...<br /><a class="readMore" href="'.$item->link['href'].'">' . $this->view->translate('Read More') . '</a>';
            }
            $this->view->news[] = $item;
        }

        $view = false;
        foreach ($scriptsDir as $scriptDir) {
            if (file_exists($scriptDir."index/index-$locale.phtml")) {
                $view = "index-$locale";
                break;
            } else if (count($localeElements == 2)
                    && file_exists($scriptDir."index/index-".$localeElements[0].".phtml")) {
                $view = 'index-'.$localeElements[0];
                break;
            }
        }
        if (!$view) {
            $view = 'index-en';
        }

        $this->render($view);
    }
}
