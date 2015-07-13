<?php
/**
 * 单元测试的基类
 * 
 * @author 胡峰 <monkee@yeahmobi.com>
 * @version 1.0.0
 * @package ym
 * @category script
 * @copyright 2014-2015 Yeahmobi@inc
 */

class Ym_Test
{
	public function __construct(){
		
	}
	public function runTest($class){
		//Ym_Init => Ym_Test_Init
		$classItems = explode('_', $class);
		$classname = 'Test_' . $class;
		
		if(!class_exists($classname)){
			
		}
		$reflector = new ReflectionClass($class);
	}
	
	public function output($msg){
		printf("%s\n", $msg);
	}
}
