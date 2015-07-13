<?php

class Controller_LBS extends Yaf_Controller_Abstract
{
    public function indexAction() {

        $this->dao = new Ym_Dao('iad');
        $nodes = array(201=>0,203=>0,205=>0,207=>0,200=>0);
        $delay = 10;
        $len   = 60;

        $end = strtotime(date('Y-m-d H:i', (time() - $delay)));
        $from = $end - $len;
        echo date('Y-m-d H:i',$from);

        foreach($nodes as $node=>$num) {

            $sql = "select sum(num) as num from log_qps where time>".$from." and time<=".$end." and node=".$node;
            $nums = $this->dao->queryRow($sql, TRUE);
            $nodes[$node] = isset($nums['num']) ? (int)$nums['num'] : 0;
        }

        $this->getView()->assign('nodeData', json_encode($nodes));
    }
}