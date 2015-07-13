<?php
class Action_AccessNumber extends Yaf_Action_Abstract
{
    public function execute() {

        $index    = $this->getRequest()->getQuery("index");
        $host     = $this->getRequest()->getQuery('host');
        $path     = $this->getRequest()->getQuery('path');
        $to = $hostList = $hostLists = $_path =$re = $hostIds = NULL;

        $o = new Model_Alarm_DataNode();
        
        // Get the host's ids
        if ($host) {

            $theHost  = explode(',',$host);
            foreach($theHost as $h) {
                if (is_numeric($h)) {
                   $hid = $o->getHostName($h);
                   $h = $hid['host'];
                }
                $hostList .= "'".$h."',";
            }
            $hostIds = $o->getHostId( rtrim($hostList,',') );
            //foreach($host_ids as $v) {
            //    $hostLists .= "'".$v['host_id']."',";
            //}

            if ($path) {

                foreach($theHost as $h) {
                    $re[] = $o->getPathId($h, $path);
                }  
                $hostIds = $re;
            }
            //$_path = isset($path_id['path_id']) ? $path_id['path_id'] : NULL ;
        }

        $from     = strtotime($this->getRequest()->getQuery('from', date('Y-m-d')));
        $to       = strtotime($this->getRequest()->getQuery('to'));
        $output   = array();

        $startId  = $o->getTimeId($from);
        $endId    = $to ? $o->getTimeId($to) : $to;

        $startId = isset($startId['id']) ? $startId['id'] : 0;
        $endId = isset($endId['id']) ? $endId['id'] : 0;

        $index_tmp  = explode(",", $index);
        $indexes  = $index_tmp ? $index_tmp : array('pv','error');

        $peakTMP = $troughTMP = array();

        if (in_array('peak',$indexes)) {

            foreach($theHost as $h) {

                $hostIdArr = $o->getHostId("'".$h."'");
                
                if (empty($path)) $key = $from.'_'.$hostIdArr[0]['host_id'].'_0';
                else {
                    $pathArr = $o->getPathId($h, $path);
                    $key = $from.'_'.$pathArr['host_id'].'_'.$pathArr['path_id'];
                    //echo 123;
                    //var_dump($pathArr);
                }
                //echo $key;echo $path;
                $dataJSON = $o->getIndexFromCached($key);
                //var_dump($dataJSON);

                if($dataJSON) {

                    $dataCache = json_decode($dataJSON['value'],TRUE);
                    
                    isset($data['sum'])   ?  $data['sum'] += $dataCache['sum'] : $data['sum'] = $dataCache['sum'];
                    isset($data['error']) ?  $data['error'] += $dataCache['error'] : $data['error'] = $dataCache['error'];
                    
                    $peakTMP[]   = $dataCache['peak'];
                    $troughTMP[] = $dataCache['trough'];
                }
            } 

            switch (count($theHost)) {
                
                case 1:
                    $peak   = array('time'=>$peakTMP[0][1],'num'=>$peakTMP[0][0]);
                    $trough = array('time'=>$troughTMP[0][1],'num'=>$troughTMP[0][0]);
                    break;

                case 2:
                    $peakMAX    = isset($peakTMP[0],$peakTMP[1]) ? max($peakTMP[0],$peakTMP[1]) : (isset($peakTMP[0])?$peakTMP[0]:0);
                    $troughMIN  = isset($troughTMP[0],$troughTMP[1]) ? min($troughTMP[0],$troughTMP[1]) : (isset($troughTMP[0])?$troughTMP[0]:0);
                    $peak       = array('time'=>$peakMAX[1],'num'=>$peakMAX[0]);
                    $trough     = array('time'=>$troughMIN[1],'num'=>$troughMIN[0]);
                    break;

                case 3:
                    $peakMAX    = max($peakTMP[0],$peakTMP[1],$peakTMP[2]);
                    $troughMIN  = min($troughTMP[0],$troughTMP[1],$peakTMP[2]);
                    $peak       = array('time'=>$peakMAX[1],'num'=>$peakMAX[0]);
                    $trough     = array('time'=>$troughMIN[1],'num'=>$troughMIN[0]);
                    break;
                 
                default:
                    $peak   = array('time'=>0,'num'=>0);
                    $trough = array('time'=>0,'num'=>0);
                    break;
            }

            $output['series']['pv']     = isset($data['sum']) ? $data['sum'] : 0;
            $output['series']['error']  = isset($data['error']) ? $data['error'] : 0;
            $output['series']['peak']   = $peak;
            $output['series']['trough'] = $trough;
            
            Sys_Common::output(true, '', $output);exit;
        }

        if (in_array('pv',$indexes)) {

            if ($host) 
                $r = $o->getIndex($startId, $endId, $hostIds);
            else 
                $r = $o->getIndex($from, $to, $hostIds);
            $output['series']['pv'] = isset($r['num']) ? $r['num'] : 0;
        }
            
        if (in_array('error',$indexes)) {

            $d = $o->getIndexError($startId, $endId, $hostIds);
            $output['series']['error'] = isset($d['num']) ? $d['num'] : 0;
        }
            
        /*if (in_array('peak',$indexes)) {

            $max = $o->getIndexPeak($startId, $endId, $hostIds,'max');
            $output['series']['peak'] = isset($max['num']) ? array('time'=>$max['time'],'num'=>$max['num']) : 0;//array('time'=>1429501793,'num'=>99);//
        }
            
        if (in_array('trough',$indexes)) {

            $min = $o->getIndexPeak($startId, $endId, $hostIds,'min');
            $output['series']['trough'] = isset($min['num']) ? array('time'=>$min['time'],'num'=>$min['num']) : 0;//array('time'=>1429501793,'num'=>99);//
        }*/
            
        Sys_Common::output(true, '', $output);
    }
} 
