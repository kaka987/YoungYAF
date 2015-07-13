<?php
class Service_System_Region
{
    public $regionModel;

    public function __construct() {
        $this->regionModel = new Model_System_Region();
    }
    public function getAllRegion() {
        $returns = $this->regionModel->getList();

        return $returns;
    }

    public function getRegion($region) {
        $returns = $this->regionModel->getOne($region);

        return $returns;
    }

}