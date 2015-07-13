<?php

class Model_Chart_Config
{
    public function getAccessTrend($hosts, $option) {
        $path    = dirname(__FILE__) . "/config/accessTrend.json";
        $returns = json_decode(file_get_contents($path), true);

        $from = $option['from'];
        $to   = $option['to'];

        $day = ($to - $from) / 86400;

        $loghandlePersistentRecordModel    = new Model_Loghandle_Persistent_Record();
        $loghandlePersistentAccesslogModel = new Model_Loghandle_Persistent_Accesslog();

        $recordTime = $loghandlePersistentRecordModel->getRecordTime('accesslog', 60);

        for($i = $day; $i >= 1; $i--) {
            $to     = $option['from'] + ($i * 86400);
            $from   = $to - 86400;

            $name   = date('m/d', $from);
            $to     = $i == $day && $to > $recordTime ? $to = $recordTime - 60 : $to;

            $result = $loghandlePersistentAccesslogModel->getAccessTrend($hosts, $from, $to);

            $data = array();

            foreach ($result as $value) {
                $value['time'] = $i == 1 ? $value['time'] : $value['time'] - (($i - 1) * 86400);
                $data[] = array('x' => $value['time'] * 1000, 'y' => intval($value['num']));
            }

            $returns['series'][] = array(
                'name'  => $name,
                'data'  => $data,
            );
        }

        return $returns;
    }

    public function getDataNode() {
        $path    = dirname(__FILE__) . "/config/dataNode.json";
        $returns = json_decode(file_get_contents($path), true);

        $monitorDataNodeModel = new Model_Monitor_DataNode();

        $dataNode = $monitorDataNodeModel->get();

        foreach ($dataNode AS $name => $number) {
            $returns['xAxis']['categories'][] = $name;
            $returns['series'][] = array(
                'data'  => array($number)
            );
        }

        return $returns;
    }
}