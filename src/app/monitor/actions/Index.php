<?php                                                                                                                                                                                           
    /**
     * 分离出来 action 的实现
     * 此文件只包含一个类，类名：Action_actionname , 必须继承自 Yaf_Action_Abstract
     * 类 Action_actionname 必须实现  execute() 方法, 方法内具体操作和controller 类里面action 操作一样
     */
	class Action_Index extends Yaf_Action_Abstract
	{
	   public function execute(){
		 echo 'test index';
		 exit;
		}
	}
