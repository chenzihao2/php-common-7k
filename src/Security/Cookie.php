<?php

namespace QKPHP\Common\Security;

class Cookie {
  private static $path = '/';
  private static $domain = '';

  public static function getCookie($name) {
    if(isset($_COOKIE[$name])) {
      return $_COOKIE[$name];
    } else {
      return null;
    }
  }

  public static function setCookie($name, $value, $expire=0, $domain="", $path="/") {
    if(empty($domain)) {
      $domain = self::$domain;
    }
    if ($expire == -1) { // del cookie
      $expire = -1;
    } else if($expire == 0) { // session cookie
      $expire = 0;
    } else {
      $expire = time() + $expire;
    }
    if(empty($path)) {
      $path = self::$path;
    }
    setcookie($name, $value, $expire, $path, $domain);
  }

  public static function delCookie($name, $path="", $domain="") {
    $expire = time()-1;
    if(empty($domain)) {
      $domain = self::$domain;
    }
    if(empty($path)) {
      $path  = self::$path;
    }
    setcookie($name, "", $expire, $path, $domain);
  }

}
