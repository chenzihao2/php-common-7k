<?php

namespace QKPHP\Common\Config;

use \QKPHP\Common\Config\Parser;

class Config {

  public static $configDir;
  private static $configs = array();

  public static function setConfigDir ($configDir) {
    self::$configDir = $configDir;
  }

  public static function getAppConf($appName, $key=null, $confDir=null) {
    return self::getConf($appName, $key, 'app', $confDir);
  }

  public static function getDBConf($appName, $key=null, $confDir=null) {
    return self::getConf($appName, $key, 'db', $confDir);
  }

  public static function getServiceConf($appName, $key=null, $confDir=null) {
    return self::getConf($appName, $key, 'service', $confDir);
  }

  public static function getConf($appName, $key=null, $type=null, $confDir=null) {
    if (empty($confDir)) {
      $confDir = self::$configDir;
    }
    if (empty($confDir)) {
      return null;
    }
    if (empty($type)) {
      $type = '';
    }
    if (!isset(self::$configs[$type][$appName])) {
      $conf = require($confDir . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $appName . '.php');
      self::$configs[$type][$appName] = $conf;
    }
    if (empty($key)) {
      return self::$configs[$type][$appName];
    }
    return self::$configs[$type][$appName][$key];
  }

}
