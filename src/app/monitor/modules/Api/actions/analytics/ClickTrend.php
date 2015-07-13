<?php
class Action_ClickTrend extends Yaf_Action_Abstract
{
    public function execute() {

        $type     = $this->getRequest()->getQuery('type','click');
        $hosts    = trim($this->getRequest()->getQuery('host'));
        $from     = strtotime($this->getRequest()->getQuery('from', date('Y-m-d')));
        //$to       = strtotime($this->getRequest()->getQuery('to'));
        $output   = array();

        $this->o = new Model_Alarm_DataNode();

        //$analyticsAccessService = new Service_Analytics_Access();
        //$systemHostService      = new Service_System_Host();

        //$hosts  = $systemHostService->getHostId($hosts);

        //$timeZero = strtotime(date('Y-m-d 00:00:00',time()));
        $c = time();
        //if ($type=='conv') $c=time()-1800; 
        $a = $b = $cache = array();

        $tag = date('Y-m-d',$from);
        if ($hosts) $tag = $tag.'_'.str_replace(',','_',$hosts);
        $cacheFile = "/tmp/".$type."_".$tag;

        if (file_exists($cacheFile)) {
        //if (0) {

            $cacheData = unserialize(file_get_contents($cacheFile));

            $t = $from = $cacheData['t'];
            for ($i=1;$i<=(($c-$from)/60);$i++) {

                $a[] = array("x"=>$t*1000,"y"=>$this->getNumByMinute($t,$hosts,$type));
                $t += 60;
            }

            $a = array_merge($cacheData['a'], $a);
            $b = $cacheData['b'];

        } else {

            $t = $from;
            for ($i=1;$i<=(($c-$from)/60);$i++) {

                $a[] = array("x"=>$t*1000,"y"=>$this->getNumByMinute($t,$hosts,$type));
                $t += 60;
            }

            $cache['a'] = $a;
            $cache['t'] = $t;

            $t = $from;

            for ($i=1;$i<=1440;$i++) {

                $b[] = array("x"=>$t*1000,"y"=>$this->getNumByMinute($t-86400,$hosts,$type));//rand(800,1100));
                $t += 60;
            }

            $cache['b'] = $b;
            file_put_contents($cacheFile, serialize($cache));
        }

        $output['series'] = array(
            array(
                "name"=>date('Y-m-d',time()),
                "data"=>$a,
            ),
            array(
                "name"=>date('Y-m-d',strtotime('-1 day')),
                "data"=>$b,
            )
        );
        //$analyticsAccessService->getAccessTrend($hosts, $from, $to);

        Sys_Common::output(true, '', $output);
    }

    public function getNumByMinute($time,$hosts='',$type='click') {

        $r = $this->o->getClickORConvByMinute($time,$hosts,$type);

        if ($r['num']) return (int)$r['num'];
        else return 0;
    }
} 
