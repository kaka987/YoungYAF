<?php
/**
 * 进入访问详情
 *
 * 展示访问详情报表页面
 *
 * @author      chiak<Chiak@yeahmobi.com>
 * @package     actions/access
 * @since       Version 1.0 @2014-05-29
 * @copyright   Copyright (c) 2014, YeahMobi, Inc.
 */
class Action_Access extends Controller_Application {

    protected $layout = 'main';

    public function run()
    {
        // 查询访问历史所需要的参数
        // 周期，day为天，between为时间段
        $period = $this->getRequest()->getQuery('period');
        $date = $this->getRequest()->getQuery('date');

        /* 页面变量 */
        $this->getView()->assign('period', $period);
        $this->getView()->assign('date', $date);
    }
}