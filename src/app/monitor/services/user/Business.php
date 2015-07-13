<?php
class Service_User_Business
{
    /**
     * @param $uid
     * @return null|失败返回
     */
    public function getList($uid) {
        $returns = null;

        $userBusinessModel = new Model_User_Business();

        $returns = $userBusinessModel->getList($uid);

        return $returns;
    }
}