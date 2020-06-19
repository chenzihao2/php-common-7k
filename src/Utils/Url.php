<?php

namespace QKPHP\Common\Utils;

class Url {

  private static $httpStatusHeader = array(
    100 => "HTTP/1.1 100 Continue",
    101 => "HTTP/1.1 101 Switching Protocols",
    200 => "HTTP/1.1 200 OK",
    201 => "HTTP/1.1 201 Created",
    202 => "HTTP/1.1 202 Accepted",
    203 => "HTTP/1.1 203 Non-Authoritative Information",
    204 => "HTTP/1.1 204 No Content",
    205 => "HTTP/1.1 205 Reset Content",
    206 => "HTTP/1.1 206 Partial Content",
    300 => "HTTP/1.1 300 Multiple Choices",
    301 => "HTTP/1.1 301 Moved Permanently",
    302 => "HTTP/1.1 302 Found",
    303 => "HTTP/1.1 303 See Other",
    304 => "HTTP/1.1 304 Not Modified",
    305 => "HTTP/1.1 305 Use Proxy",
    307 => "HTTP/1.1 307 Temporary Redirect",
    400 => "HTTP/1.1 400 Bad Request",
    401 => "HTTP/1.1 401 Unauthorized",
    402 => "HTTP/1.1 402 Payment Required",
    403 => "HTTP/1.1 403 Forbidden",
    404 => "HTTP/1.1 404 Not Found",
    405 => "HTTP/1.1 405 Method Not Allowed",
    406 => "HTTP/1.1 406 Not Acceptable",
    407 => "HTTP/1.1 407 Proxy Authentication Required",
    408 => "HTTP/1.1 408 Request Time-out",
    409 => "HTTP/1.1 409 Conflict",
    410 => "HTTP/1.1 410 Gone",
    411 => "HTTP/1.1 411 Length Required",
    412 => "HTTP/1.1 412 Precondition Failed",
    413 => "HTTP/1.1 413 Request Entity Too Large",
    414 => "HTTP/1.1 414 Request-URI Too Large",
    415 => "HTTP/1.1 415 Unsupported Media Type",
    416 => "HTTP/1.1 416 Requested range not satisfiable",
    417 => "HTTP/1.1 417 Expectation Failed",
    500 => "HTTP/1.1 500 Internal Server Error",
    501 => "HTTP/1.1 501 Not Implemented",
    502 => "HTTP/1.1 502 Bad Gateway",
    503 => "HTTP/1.1 503 Service Unavailable",
    504 => "HTTP/1.1 504 Gateway Time-out"
  );

  public static function getServerName($withProtocol=false) {
    return ($withProtocol ? self::isHttps() ? 'https://' : 'http://' : '') . ( !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']);
  }

  public static function isHttps() {
    return isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on";
  }

  public static function getRequestUrl($withQuery=false) {
    $protocol = "http";
    if(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
      $protocol .= "s";
    }
    $serverName = self::getRealServerName();
    $path = self::getRequestPath();
    $url = $protocol."://".$serverName;
    if(!empty($path)) {
      $url .= "/".$path;
    }
    if($withQuery) {
      $url .= "?".$_SERVER['QUERY_STRING'];
    }
    return $url;
  }

  public static function getRequestPath() {
    $path = isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : $_SERVER['REQUEST_URI'];
    $path = filter_var($path, FILTER_SANITIZE_URL);

    $parts = explode("?", $path);
    return trim($parts[0], "/");
  }

  public static function issetParam($paramName) {
    return isset($_REQUEST[$paramName]);
  }

  public static function processRequestValue($value, $isNum=false) {
    if(!is_array($value)) {
      if(!$isNum) {
        return trim(htmlentities($value, ENT_NOQUOTES|ENT_IGNORE, 'UTF-8'));
      } else {
        return intval(trim($value));
      }
    } else {
      $ret = array();
      foreach($value as $k=>$v) {
        $ret[$k] = self::processRequestValue($v, $isNum);
      }
      return $ret;
    }
  }

  public static function getIntParam($paramName, $defaultValue = 0) {
    if(isset($_REQUEST[$paramName])) {
      return self::processRequestValue($_REQUEST[$paramName], true);
    } else {
      return $defaultValue;
    }
  }

  public static function getStringParam($paramName, $defaultValue = "") {
    if(isset($_REQUEST[$paramName])) {
      return self::processRequestValue($_REQUEST[$paramName]);
    } else {
      return $defaultValue;
    }
  }

  public static function getArrayParam($paramName, $index=-1) {
    if(!isset($_REQUEST[$paramName])) {
      return null;
    } else {
      $values = self::processRequestValue($_REQUEST[$paramName]);
      if($index == -1 || $index>=count($values)) {
        return $values;
      } else {
        return $values[$index];
      }
    }
  }

  public static function redirect($location, $status=303) {
    self::httpHeader($status);
    header("Location: $location");
    exit;
  }

  public static function httpHeader($status) {
    if(isset(self::$httpStatusHeader[$status])) {
      header(self::$httpStatusHeader[$status]);
    }
  }

  public static function getUserAgent() {
    return $_SERVER['HTTP_USER_AGENT'];
  }

  public static function getClientIp() {
    $ip = false;
    if(isset($_SERVER['HTTP_CLIENT_IP'])) {
      $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $ips = explode (',',  $_SERVER['HTTP_X_FORWARDED_FOR']);
      if ($ip) {
        array_unshift($ips, $ip);
        $ip = false;
      }
      for ($i=0; $i < count($ips); $i++) {
        $ips[$i] = trim($ips[$i]);
        if(!preg_match('/^(10)|(172\.16)|(192\.168)\./', $ips[$i])) {
          $ip = $ips[$i];
          break;
        }
      }
    }
    return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
  }

  public static function appendQuerysTo ($url, array $querys, array $hash=null) {
    if (empty($url)) {
      return $url;
    }
    $urlInfo = parse_url($url);
    if (isset($urlInfo['query'])) {
      parse_str($urlInfo['query'], $querys0);
      $querys = array_merge($querys0, $querys);
    }
    if (!empty($querys)) {
      $urlInfo['query'] = http_build_query($querys);
    }

    $scheme   = isset($urlInfo['scheme']) ? $urlInfo['scheme'] . '://' : '';
    $host     = isset($urlInfo['host']) ? $urlInfo['host'] : '';
    $port     = isset($urlInfo['port']) ? ':' . $urlInfo['port'] : '';
    $user     = isset($urlInfo['user']) ? $urlInfo['user'] : '';
    $pass     = isset($urlInfo['pass']) ? ':' . $urlInfo['pass']  : '';
    $pass     = ($user || $pass) ? "$pass@" : '';
    $path     = isset($urlInfo['path']) ? $urlInfo['path'] : '';
    $query    = isset($urlInfo['query']) ? '?' . $urlInfo['query'] : '';
    $fragment = isset($urlInfo['fragment']) ? '#' . $urlInfo['fragment'] : '';
    if (!empty($hash)) {
      $hash = http_build_query($hash);
      if (empty($fragment)) {
        $fragment = "#$hash";
      } else {
        $fragment .= "&$hash";
      }
    }
    return "$scheme$user$pass$host$port$path$query$fragment";
  } 

}
