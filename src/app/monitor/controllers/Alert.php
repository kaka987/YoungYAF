<?php
class Controller_Alert extends Yaf_Controller_Abstract {

    public $actions = array(
        'alarm'  		=> 'actions/alert/Alarm.php',
    	'data'	 	    => 'actions/alert/Data.php',
    	'template'	 	=> 'actions/alert/Template.php',
    	'subscribe'		=> 'actions/alert/Subscribe.php',
    	'users'	        => 'actions/alert/Users.php',
    	'groups'    	=> 'actions/alert/Groups.php',
    );
}