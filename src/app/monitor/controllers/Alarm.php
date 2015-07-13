<?php
class Controller_Alarm extends Yaf_Controller_Abstract {

    public $actions = array(
        'set'  		=> 'actions/alarm/Set.php',
    	'add'	 	=> 'actions/alarm/Add.php',
    	'edit'	 	=> 'actions/alarm/Edit.php',
    	'save'		=> 'actions/alarm/Save.php',
    	'notify'	=> 'actions/alarm/Notify.php',
    	'list'   	=> 'actions/alarm/List.php',
    	'logedit'   => 'actions/alarm/Logedit.php',
    	'logeditok'   => 'actions/alarm/Logeditok.php',
    	'logdeleteok'   => 'actions/alarm/Logdeleteok.php',
    	'log'		=> 'actions/alarm/Loglist.php',
    	'user'		=> 'actions/alarm/User.php',
    	'group'		=> 'actions/alarm/Group.php',
        'worker'	=> 'actions/alarm/Worker.php',
        'action'	=> 'actions/alarm/Action.php',
        'monitor'	=> 'actions/alarm/Monitor.php',
    );

}