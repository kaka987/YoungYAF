<?php

/**
 * YPP Config Class
 *
 * It just load and parse the config file.
 *
 * Ym_Config::getAppItem('dir/file:a.b.c');
 * Ym_Config::getItem('a.b.c');
 *
 * @author         zhangy<Young@yeahmobi.com>
 * @package       ym
 * @category Libraries
 * @since       Version 1.0.3 @20140508
 * @copyright  Copyright (c) 2014, Yeahmobi, Inc.
 */
class Ym_Config
{
    
    /**
     * config path
     * 
     * @var string
     */
    public static $baseDir = NULL;

    /**
     * config file
     * 
     * @var string
     */
    public static $configFile = NULL;

    /**
     * save the config data
     * 
     * @var array
     */
    public static $configData = array(); 

    /**
     * save the items data
     * 
     * @var array
     */
    public static $itemPool = array();

    /**
     * the config index
     * 
     * @var string
     */
    public static $index = '';

    public static $fileIndex = '';

    //---------------------------------------------
    
    /**
     * init the config
     * 
     * @return void
     */
    public static function init() {

        if (self::$baseDir === NULL) {
            $rootDir = defined('YPP_DIR_CONF') ? YPP_DIR_CONF : './';
            self::$baseDir = rtrim($rootDir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        }
    }

    public static function getAppItem($index='') {

        $app = defined('YPP_APP') ?  YPP_APP : '';
        return self::getItem($index, $app);
    }

    /**
     * get item from config file
     * 
     * @param  string $index
     * @param  string $app
     * @return array
     */
    public static function getItem($index='', $app='') {

        $configData = self::load($index, $app);
        if ($configData === FALSE) throw new Exception("Load config file error!");
        
        $keyarr = self::getKeys($index);
        $fileIndex = self::getfileIndex($index);

        $item = self::getItemFromCache($keyarr, $fileIndex);

        if (empty($item)) {
            self::parseIni($configData);
            return self::getItemFromCache($keyarr, $fileIndex);
        }

        return $item;
    }

    /**
     * get item from cache
     * 
     * @param  string $index
     * @param  string $app
     * @return array
     */
    public static function getItemFromCache($keyarr, $fileIndex) {

        switch (count($keyarr)) {
            case 0:
                if (isset(static::$itemPool[$fileIndex]))
                    return static::$itemPool[$fileIndex];
                return array();
                break;
            
            case 1:
                if (isset(static::$itemPool[$fileIndex][$keyarr[0]]))
                    return static::$itemPool[$fileIndex][$keyarr[0]];
                return array();
                break;

            case 2:
                if (isset(static::$itemPool[$fileIndex][$keyarr[0]][$keyarr[1]]))
                    return static::$itemPool[$fileIndex][$keyarr[0]][$keyarr[1]];
                return array();
                break;

            case 3:
                if (isset(static::$itemPool[$fileIndex][$keyarr[0]][$keyarr[1]][$keyarr[2]]))
                    return static::$itemPool[$fileIndex][$keyarr[0]][$keyarr[1]][$keyarr[2]];
                return array();
                break;

            default:
                return array();
                break;
        }
    }

    /**
     * getKeys description
     * 
     * @return array
     */
    public static function getKeys($index) {

        $keystr = strpos($index, ':') ? substr($index, strrpos($index, ':')+1) : '';
        if (empty($keystr)) return array();
        return explode('.', trim($keystr,' .'));
    }

    /**
     * getfileIndex description
     * 
     * @return array
     */
    public static function getfileIndex($index) {

        return isset(self::$fileIndex) ? self::$fileIndex : NULL;
    }

    /**
     * Get the full file 
     * 
     * @param  string $fileName 
     * @return string/bool
     */
    protected static function getFile($index='',$app='') {
        
        $fileName = strpos($index, ':') ? trim(substr($index, 0, strrpos($index, ':')), '/').'.ini' : $index.'.ini';

        if (empty($index)) $fileName = 'config.ini';

        if ( ! empty($app)) $app = 'app/'.$app.DIRECTORY_SEPARATOR;
        $file = self::$baseDir.$app.$fileName;
        if (file_exists($file)) {
            self::$fileIndex = $file;
            return $file;
        }

        return FALSE;
    }

    /**
     * Load the config file to request data
     * 
     * @param  string $fileName [the config file name]
     * @return array           
     */
    protected static function load($index,$app) {
        
        $file = static::getFile($index,$app);
        if ( $file === FALSE) return FALSE;

        if (isset(static::$configData[self::$fileIndex]))
            return static::$configData[self::$fileIndex];
        return static::$configData[self::$fileIndex] = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }



    /**
     * Parse the config data to items
     * 
     * @return void
     */
    protected static function parseIni($configData=array()) {

        if (empty($configData)) throw new Exception("Config file is empty or not exist!");

        $fileIndex = self::$fileIndex ? self::$fileIndex : 'config';

        // Remove the notes as '#' or ';'
        $pattern = '/^(#|;|\[).*$/';

        // foreach the every line data
        foreach ($configData as $key=>$item) {//start foreach
            $itemKeys = $items = array();

            // unset the description data
            if (preg_match($pattern, $item)) {
                unset(static::$configData[$key]);
                continue;
            }

            // parse the item to key and value
            $items = explode('=', $item, 2);

            if ( ! isset($items[0]) AND ! isset($items[1])) {
                return FALSE;
            }

            // parse the key by the "."
            $itemKeys = explode('.', $items[0]);
            foreach ($itemKeys as $key=>$value) {
                $itemKeys[$key] = trim($value);
                if (empty($value)) continue 2;
            }

            $len=count($itemKeys);

            if ($len==1) {
                if (isset($itemKeys[0])) {
                    if ( ! isset(static::$itemPool[$fileIndex][$itemKeys[0]]) ) {
                        static::$itemPool[$fileIndex][$itemKeys[0]] = trim($items[1]);
                    }
                } 
            }

            if ($len==2) {

                if (isset($itemKeys[0])) {
                    if ( ! isset(static::$itemPool[$fileIndex][$itemKeys[0]]) ) {
                        static::$itemPool[$fileIndex][$itemKeys[0]] = array();
                    }
                } 

                if (isset($itemKeys[1])) {
                    if ( ! isset(static::$itemPool[$fileIndex][$itemKeys[0]][$itemKeys[1]]) ) {
                        static::$itemPool[$fileIndex][$itemKeys[0]][$itemKeys[1]] = trim($items[1]);
                    }
                }
            }

            if ($len==3) {

                if (isset($itemKeys[0])) {
                    if ( ! isset(static::$itemPool[$fileIndex][$itemKeys[0]]) ) {
                        static::$itemPool[$fileIndex][$itemKeys[0]] = array();
                    }
                } 

                if (isset($itemKeys[1])) {
                    if ( ! isset(static::$itemPool[$fileIndex][$itemKeys[0]][$itemKeys[1]]) ) {
                        static::$itemPool[$fileIndex][$itemKeys[0]][$itemKeys[1]] = array();
                    }
                }

                if (isset($itemKeys[2])) {
                    if ( ! isset(static::$itemPool[$fileIndex][$itemKeys[0]][$itemKeys[1]][$itemKeys[2]]) ) {
                        static::$itemPool[$fileIndex][$itemKeys[0]][$itemKeys[1]][$itemKeys[2]] = trim($items[1]);
                    }
                }
            }
        }//end foreach
    }

    private function __construct(){}
    private function __clone(){}
}
// END Ym_Config class

/* End of file Config.php */
/* Location: ./ypp/ym/Config.php */
/*class Config_test {
    
        public function __construct() {
            
                    Ym_Config::init();
                    
                            var_dump(Ym_Config::getAppItem('config/test:log.ifCacheHandler'));
                    
                            var_dump(Ym_Config::getAppItem('config/config:log.ifCacheHandler'));
                            
                                    var_dump(Ym_Config::getItem('config/test:test'));
                                }
}
new Config_test();*/
