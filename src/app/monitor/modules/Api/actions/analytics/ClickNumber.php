<?php
class Action_ClickNumber extends Yaf_Action_Abstract
{
    public function execute() {
        $index    = $this->getRequest()->getQuery("index");
        //$host     = $this->getRequest()->getQuery('host');
        //$path     = $this->getRequest()->getQuery('path');

        $o = new Model_Alarm_DataNode();

        $from     = strtotime($this->getRequest()->getQuery('from', date('Y-m-d')));
        $to       = strtotime($this->getRequest()->getQuery('to'));
        $to       = empty($to) ? $from + 86400 : $to;
        $output   = array();

        $r = $o->getClickNum($from, $to);
        $d = $o->getConvNum($from, $to);

        $output['series']['pv'] = $r ? $r['num'] : 0;
        $output['series']['error'] = $d ? $d['num'] : 0;
        Sys_Common::output(true, '', $output);
    }
} 