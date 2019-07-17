<?php

class RandomText
{
    private static $_words;
    private static $_text;
    private static $_tags = array(
        'business', 'software', 'statistics', 'technology', 'finance', 'tips',
        'tools', 'tutorial', 'videos', 'web', 'guide', 'management', 'blueprints',
        'usability', 'accounting', 'humanresources', 'fun', 'travel', 'information',
        'designs', 'tasks', 'staff', 'strategy', 'bestpractices', 'troubleshooting',
        'activities', 'ideas', 'random', 'important', 'critical', 'processes', 'data',
        'legal', 'corporate', 'industry', 'company', 'future', 'statements', 'plans',
        'marketing', 'firm', 'organization', 'economy', 'academic', 'liabilities', 'risks',
        'oportunities', 'politics', 'services', 'support', 'claims', 'problems', 'assets',
        'goods', 'operations', 'production', 'business_intelligence', 'commercial', 'taxes',
        'capital', 'securities', 'properties', 'advertising', 'ethics', 'costs', 'insurance',
        'franchising', 'government', 'investments', 'trade', 'international', 'manufacturing',
        'partnerships', 'real_estate', 'revenue', 'profit'
    );
    public static function getRandomWord()
    {
        if (!isset(self::$_words)) {
            self::$_words = file(dirname(__FILE__).'/../libs/Monkeys/tests/words.txt');
            $numWords = count(self::$_words);
            for ($i = 0; $i < $numWords; $i++) {
                self::$_words[$i] = trim(self::$_words[$i]);
            }
        }

        return self::$_words[array_rand(self::$_words)];
    }

    public static function getRandomText($len)
    {
        if (!isset(self::$_text)) {
            self::$_text = file_get_contents(dirname(__FILE__) . '/../libs/Monkeys/tests/sampletext.txt');
        }

        return substr(self::$_text, rand(0, strlen(self::$_text) - $len), $len);
    }

    public static function getRandomTag()
    {
        return self::$_tags[array_rand(self::$_tags)];
    }
}
