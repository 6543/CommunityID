<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD Licensese
* @author Keyboard Monkeys Ltd.
* @since Textroller 0.9
* @package TextRoller
* @packager Keyboard Monkeys
*/

class Monkeys_Lucene
{
    const LUCENE_DIR = '/lucene';

    /**
    * @throws Zend_Search_Lucene_exception
    */
    public static function getIndex()
    {
        try {
            $index = Zend_Search_Lucene::open(APP_DIR . self::LUCENE_DIR);
        } catch (Zend_Search_Lucene_Exception $e) {
            $index = Zend_Search_Lucene::create(APP_DIR . self::LUCENE_DIR);
            Zend_Registry::get('logger')->log('Created Lucene index file', Zend_Log::INFO);
        }

        return $index;
    }

    /**
    * @throws Zend_Search_Lucene_exception
    * @return void
    */
    public static function indexArticle(Model_Blog $blog, Zend_Db_Table_Rowset $tagsSet, $isNew)
    {
        if ($blog->draft || !$blog->hasBeenPublished()) {
            return;
        }

        $tags = array();
        foreach ($tagsSet as $tag) {
            $tags[] = $tag->tag;
        }
        $tags = implode(' ', $tags);

        $index = self::getIndex();

        if (!$isNew) {
            $existingDocIds = $index->termDocs(new Zend_Search_Lucene_Index_Term($blog->id, 'blog_id'));
            if ($existingDocIds) {
                $index->delete($existingDocIds[0]);
            }
        }

        // I won't be using Zend_Search_Lucene_Document_HTML 'cause articles are not full HTML documents
        $doc = new Zend_Search_Lucene_Document();

        $doc->addField(Zend_Search_Lucene_Field::Keyword('blog_id', $blog->id));

        $doc->addField(Zend_Search_Lucene_Field::Text('title', $blog->title, 'utf-8'));
        $doc->addField(Zend_Search_Lucene_Field::Text('excerpt', $blog->excerpt, 'utf-8'));
        $doc->addField(Zend_Search_Lucene_Field::Unstored('tag', $tags, 'utf-8'));
        $doc->addField(Zend_Search_Lucene_Field::Unstored('contents', $blog->getContentWithoutTags(), 'utf-8'));
        $index->addDocument($doc);
        $index->commit();
    }

    public static function unIndexArticle(Blog $blog)
    {
        try {
            $index = self::getIndex();
        } catch (Zend_Search_Lucene_Exception $e) {
            return;
        }

        $existingDocIds = $index->termDocs(new Zend_Search_Lucene_Index_Term($blog->id, 'blog_id'));

        if ($existingDocIds) {
            $index->delete($existingDocIds[0]);
        }
    }

    public static function optimizeIndex()
    {
        $index = self::getIndex();
        $index->optimize();
    }
}
