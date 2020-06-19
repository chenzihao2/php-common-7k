<?php

/**
 * curl code, refer: http://curl.haxx.se/libcurl/c/libcurl-errors.html
 * CURLE_OK (0) 
 * CURLE_UNSUPPORTED_PROTOCOL (1) 
 * CURLE_FAILED_INIT (2) 
 * CURLE_URL_MALFORMAT (3) 
 * CURLE_NOT_BUILT_IN (4) 
 * CURLE_COULDNT_RESOLVE_PROXY (5) 
 * CURLE_COULDNT_RESOLVE_HOST (6) 
 * CURLE_COULDNT_CONNECT (7) 
 * CURLE_FTP_WEIRD_SERVER_REPLY (8) 
 * CURLE_REMOTE_ACCESS_DENIED (9) 
 * CURLE_FTP_ACCEPT_FAILED (10) 
 * CURLE_FTP_WEIRD_PASS_REPLY (11) 
 * CURLE_FTP_ACCEPT_TIMEOUT (12) 
 * CURLE_FTP_WEIRD_PASV_REPLY (13) 
 * CURLE_FTP_WEIRD_227_FORMAT (14) 
 * CURLE_FTP_CANT_GET_HOST (15) 
 * CURLE_FTP_COULDNT_SET_TYPE (17) 
 * CURLE_PARTIAL_FILE (18) 
 * CURLE_FTP_COULDNT_RETR_FILE (19) 
 * CURLE_QUOTE_ERROR (21) 
 * CURLE_HTTP_RETURNED_ERROR (22) 
 * CURLE_WRITE_ERROR (23) 
 * CURLE_UPLOAD_FAILED (25) 
 * CURLE_READ_ERROR (26) 
 * CURLE_OUT_OF_MEMORY (27) 
 * CURLE_OPERATION_TIMEDOUT (28) 
 * CURLE_FTP_PORT_FAILED (30) 
 * CURLE_FTP_COULDNT_USE_REST (31) 
 * CURLE_RANGE_ERROR (33) 
 * CURLE_HTTP_POST_ERROR (34) 
 * CURLE_SSL_CONNECT_ERROR (35) 
 * CURLE_BAD_DOWNLOAD_RESUME (36) 
 * CURLE_FILE_COULDNT_READ_FILE (37) 
 * CURLE_LDAP_CANNOT_BIND (38) 
 * CURLE_LDAP_SEARCH_FAILED (39) 
 * CURLE_FUNCTION_NOT_FOUND (41) 
 * CURLE_ABORTED_BY_CALLBACK (42) 
 * CURLE_BAD_FUNCTION_ARGUMENT (43) 
 * CURLE_INTERFACE_FAILED (45) 
 * CURLE_TOO_MANY_REDIRECTS (47) 
 * CURLE_UNKNOWN_OPTION (48) 
 * CURLE_TELNET_OPTION_SYNTAX (49) 
 * CURLE_PEER_FAILED_VERIFICATION (51) 
 * CURLE_GOT_NOTHING (52) 
 * CURLE_SSL_ENGINE_NOTFOUND (53) 
 * CURLE_SSL_ENGINE_SETFAILED (54) 
 * CURLE_SEND_ERROR (55) 
 * CURLE_RECV_ERROR (56) 
 * CURLE_SSL_CERTPROBLEM (58) 
 * CURLE_SSL_CIPHER (59) 
 * CURLE_SSL_CACERT (60) 
 * CURLE_BAD_CONTENT_ENCODING (61) 
 * CURLE_LDAP_INVALID_URL (62) 
 * CURLE_FILESIZE_EXCEEDED (63) 
 * CURLE_USE_SSL_FAILED (64) 
 * CURLE_SEND_FAIL_REWIND (65) 
 * CURLE_SSL_ENGINE_INITFAILED (66) 
 * CURLE_LOGIN_DENIED (67) 
 * CURLE_TFTP_NOTFOUND (68) 
 * CURLE_TFTP_PERM (69) 
 * CURLE_REMOTE_DISK_FULL (70) 
 * CURLE_TFTP_ILLEGAL (71) 
 * CURLE_TFTP_UNKNOWNID (72) 
 * CURLE_REMOTE_FILE_EXISTS (73) 
 * CURLE_TFTP_NOSUCHUSER (74) 
 * CURLE_CONV_FAILED (75) 
 * CURLE_CONV_REQD (76) 
 * CURLE_SSL_CACERT_BADFILE (77) 
 * CURLE_REMOTE_FILE_NOT_FOUND (78) 
 * CURLE_SSH (79) 
 * CURLE_SSL_SHUTDOWN_FAILED (80) 
 * CURLE_AGAIN (81) 
 * CURLE_SSL_CRL_BADFILE (82) 
 * CURLE_SSL_ISSUER_ERROR (83) 
 * CURLE_FTP_PRET_FAILED (84) 
 * CURLE_RTSP_CSEQ_ERROR (85) 
 * CURLE_RTSP_SESSION_ERROR (86) 
 * CURLE_FTP_BAD_FILE_LIST (87) 
 * CURLE_CHUNK_FAILED (88) 
 * CURLE_NO_CONNECTION_AVAILABLE (89)
 *
 */

namespace QKPHP\Common\Utils;

class Http {

  private static $timeout = 30;

  private static function curlReturn($errorCode, $content) {
    return array($errorCode, $content);
  }

  private static function setOption($ch, array $options=null) {
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    if($options === null) {
      $options = array();
    }
    if(isset($options["cookie"]) && is_array($options["cookie"])) {
      $cookieArr = array();
      foreach($options["cookie"] as $key=>$value) {
        $cookieArr[] = "$key=$value";
      }
      $cookie = implode("; ", $cookieArr);
      curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    }
    $timeout = self::$timeout;
    if(isset($options["timeout"])) {
      $timeout = $options["timeout"];
    }
    if($timeout < self::$timeout) {
      $timeout = self::$timeout;
    }
    if(isset($options["ua"])) {
      curl_setopt($ch, CURLOPT_USERAGENT, $options["ua"]);
    }
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    if(isset($options['header'])) {
      curl_setopt($ch, CURLOPT_HEADER, false);
      $header = array();
      foreach($options['header'] as $k=>$v) {
        $header[] = $k.": ".$v;
      }
      curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }
    if(isset($options['ca'])) {
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
      curl_setopt($ch,CURLOPT_SSLCERTTYPE,$options['ca']['cert_type']);
      curl_setopt($ch,CURLOPT_SSLCERT, $options['ca']['cert_path']);
      curl_setopt($ch,CURLOPT_SSLKEYTYPE,$options['ca']['key_type']);
      curl_setopt($ch,CURLOPT_SSLKEY, $options['ca']['key_path']);
    }
  }

  public static function get($url, $params, array $options=null) {
    if(!empty($params)) {
      $p1 = array();
      foreach($params as $k=>$v) {
        $p1[] = $k."=".urlencode($v);
      }
      $url .= "?".implode("&", $p1);
    }
    $ch = curl_init($url);
    if(empty($options)) {
      $options = array();
    }
    self::setOption($ch, $options);
    $content = curl_exec($ch);
    $errorCode = curl_errno($ch);
    curl_close($ch);
    return self::curlReturn($errorCode, $content);
  }

  /**
   * @param $url 请求的链接
   * @param $params 参数，可以是字符串也可以是数组array('a'=>1,'b'=>2);
   * @param array $options 设置信息
   * @return array
   */
  public static function post($url, $params, array $options=null) {
    $ch = curl_init();
    self::setOption($ch, $options);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, !empty($params));
    $params = is_array($params) ? http_build_query($params) : $params;
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    $content = curl_exec($ch);
    $errorCode = curl_errno($ch);
    curl_close($ch);
    return self::curlReturn($errorCode, $content);
  }

  public static function getUserIp() {
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']) {
      $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
      $ip = $_SERVER["REMOTE_ADDR"];
    }
    return $ip;
  }

}
