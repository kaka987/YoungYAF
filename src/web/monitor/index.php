<?php
date_default_timezone_set('Asia/Shanghai');
$app = Ym_Init::init('monitor');
$app->run();

// run info to record
$runInfo = 'cost : '. Ym_Timer::getResult('cost') . ' s , mem  : '. Ym_Timer::getResult('mem'). ' KB';
if (YPP_APP_ENV != 'pro') echo $runInfo, PHP_EOL;
Ym_Logger::info($runInfo);