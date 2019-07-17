<?php

class Monkeys_Db_Profiler extends Zend_Db_Profiler
{
    public function Monkeys_Db_Profiler()
    {
        parent::__construct(true);
    }

    public function queryStart($queryText, $queryType = null)
    {
        Zend_Registry::get('logger')->log("DB QUERY: $queryText", Zend_Log::DEBUG);
        return parent::queryStart($queryText, $queryType);
    }
}
