<?php
class Action_ProduceConfig extends Controller_Application {
    # 数据格式
//        <match mongo.*.*>
//          type mongo
//          host 10.2.255.30
//          port 27017
//          database lbnginxlog
//          collection test3
//          flush_interval 1s
//        </match>
//        <source>
//            type tail
//            path /tmp/error.log
//            pos_file /tmp/log.pos
//            tag mongo.1
//            format none
//        </source>
    public function run()
    {
        $alarmConfig = new Model_Alarm_Log();
        $configData = $alarmConfig->selectdata();
        $alarmpath = '/root/ypp/conf/alarmconf/';
        $ips = array();
        foreach($configData as $v){
            if(in_array(long2ip($v['monitor_ip']),$ips)){
                $conf = "<source>\r\n";
                $conf .= "type tail\r\n";
                $conf .= "path ".$v['monitor_logpath']."\r\n";
                $conf .= "pos_file /tmp/log.pos\r\n";
                $conf .= "tag mongo.1\r\n";
                $conf .= "format none\r\n";
                $conf .= "</source>\r\n";
                $num = file_put_contents($alarmpath.long2ip($v['monitor_ip']).'.conf', $conf,FILE_APPEND);
            }else{
                array_push($ips,long2ip($v['monitor_ip']));
                $conf = "<match mongo.*.*>\r\n";
                $conf .= "type mongo\r\n";
                $conf .= "host ".long2ip($v['monitor_ip'])."\r\n";
                $conf .= "port 27017\r\n";
                $conf .= "database lbnginxlog\r\n";
                $conf .= "collection test3\r\n";
                $conf .= "flush_interval 1s\r\n";
                $conf .= "</match>\r\n";
                $conf .= "<source>\r\n";
                $conf .= "type tail\r\n";
                $conf .= "path ".$v['monitor_logpath']."\r\n";
                $conf .= "pos_file /tmp/log.pos\r\n";
                $conf .= "tag mongo.1\r\n";
                $conf .= "format none\r\n";
                $conf .= "</source>\r\n";
                $num = file_put_contents($alarmpath.long2ip($v['monitor_ip']).'.conf', $conf);
            }
        }

        if($num){
            static::output(1,'成功生成conf.ini文件', '字节数为:'.$num);
        }else{
            static::output(0,'生成conf.ini文件失败',array());
        }
    }
}