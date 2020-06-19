<?php
namespace QKPHP\Common\Utils;

class Utils {

  public static function toXML($params) {
    $params0 = array();
    foreach($params as $k=>$v) {
      $params0[] = '<'.$k.'>'.$v.'</'.$k.'>';
    }
    return '<xml>'.implode('', $params0).'</xml>';
  }

  public static function xmlToArr($xml) {
    if (empty($xml)) {
      return null;
    }
    return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
  }

  public static function rdir ($dir) {
    $files = array();
    if ($handle = opendir($dir)) {

      while (false !== ($file = readdir($handle))) {
        if ($file == '.' || $file == '..') {
          continue;
        }
        $fileDir = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($fileDir)) {
          $files = array_merge($files, self::rdir($fileDir));
        } else {
          $files[] = $fileDir;
        }
      }
    }
    return $files;
  }

  public static function delDir ($dir, $withCurrentDir=true) {
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
      $file0 = $dir .DIRECTORY_SEPARATOR . $file;
      (is_dir($file0)) ? self::delDir ($file0, true) : unlink($file0);
    }
    if ($withCurrentDir) {
      return rmdir($dir);
    } else {
      return true;
    }
  }

}
