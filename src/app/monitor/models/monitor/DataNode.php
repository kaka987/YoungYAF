<?php
class Model_Monitor_DataNode
{
    public function get() {
        $returns = array();

        $beginTime = time() - 60;

        $mongoDao = new Dao_Mongo('accesslog');

        $collections = $mongoDao->db->getCollectionNames();

        $colSize = count($collections);

        for ($i = 0; $i < $colSize; $i++) {
            $mongoDao->whereGt("time", new MongoDate($beginTime));
            $mongoDao->whereLte("time", new MongoDate($beginTime + 60));
            $returns[$collections[$i]] = $mongoDao->count($collections[$i]);
        }

        return $returns;
    }
} 