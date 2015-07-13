<?php
class View_Block
{
	public static function display($file, $ext = '.phtml')
	{
		include(YPP_APP_ROOT . '/views/block/' . $file . $ext);
	}
}