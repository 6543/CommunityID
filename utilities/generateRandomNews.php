<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

/**
* This scripts creates fake entries in the history table
*/

define('APP_DIR', dirname(__FILE__) . '/..');

/**
* Number of entries to create
*/
define('NUM_ENTRIES', 30);

require APP_DIR . '/Application.php';
require 'RandomText.php';

Application::setIncludePath();
Application::setAutoLoader();
Application::setConfig();
Application::setLogger();
Application::setDatabase();

class GenerateRandomNews
{
    public function generate()
    {
        $news = new News_Model_News();
        for ($i = 0; $i < NUM_ENTRIES; $i++) {
            $article = $news->createRow();
            $article->test = 1;
            $article->title = RandomText::getRandomWord() . ' '
                . RandomText::getRandomWord() . ' ' . RandomText::getRandomWord();
            $article->date = date('Y-m-d', time() - 24*60*60 * rand(0, 15));
            $article->excerpt = RandomText::getRandomText(50);
            $article->content = RandomText::getRandomText(700);
            $article->save();
        }
    }
}

$generate = new GenerateRandomNews();
$generate->generate();
