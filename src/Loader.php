<?php
namespace QKPHP\Common;

class Loader {
  const NS_SEPARATOR     = '\\';
  const NAME_SEPARATOR = '_';

  public static $classMap = array();

  public static function setIncludePath (array $paths) {
    $paths = array_unique(array_merge(explode(PATH_SEPARATOR, get_include_path()), $paths));
    set_include_path(implode(PATH_SEPARATOR, $paths));
  }

  public static function load() {
    spl_autoload_register(function ($class) {
      if (isset(Loader::$classMap[$class])) {
        include(Loader::$classMap[$class]);
      } else {
        // replace '\' and '_' to DIRECTORY_SEPARATOR
        $file = str_replace(Loader::NAME_SEPARATOR, DIRECTORY_SEPARATOR, $class);
        if (false !== strpos($class, Loader::NS_SEPARATOR)) {
          $file = str_replace(Loader::NS_SEPARATOR, DIRECTORY_SEPARATOR, $file);
        }
        $file = $file . ".php";
        Loader::$classMap[$class] = $file;
        include $file;
      }
    });
  }
}
