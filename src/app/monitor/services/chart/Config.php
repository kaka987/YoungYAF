<?php
class Service_Chart_Config
{
    public $modelChartConfig;

    const TYPE_ACCESS_TREND = "access_trend";
    const TYPE_DATA_NODE    = "data_node";

    public function __construct() {
        $this->modelChartConfig = new Model_Chart_Config();
    }

    public function get($type, $hosts, $option) {
        $output = null;

        switch ($type) {
            case self::TYPE_ACCESS_TREND:
                $output = $this->getAccessTrend($hosts, $option);
                break;
            case self::TYPE_DATA_NODE:
                $output = $this->getDataNode();
                break;
        }

        return $output;
    }

    private function getAccessTrend($hosts, $option) {
        $returns = $this->modelChartConfig->getAccessTrend($hosts, $option);

        return $returns;
    }

    private function getDataNode() {
        $returns = $this->modelChartConfig->getDataNode();

        return $returns;
    }
}