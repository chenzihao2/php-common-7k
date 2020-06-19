<?php
namespace QKPHP\Common\Security;

class Utils {

  const ALPHABETS    = "abcdefghijklmnopqrstuvwxyz";
  const DIGITAL      = "0123456789";
  const SPECIALCHARS = '%_$~|#.?;:@';

  /**
   * $len 生成的salt长度
   * $level 位运算: 100:字母, 010:数字, 001:特殊字符
   */
  public static function getSalt($len, $level=111) {
    $chars = "";
    if($level & 100) {
      $chars .= self::ALPHABETS;
    }
    if($level & 010) {
      $chars .= self::DIGITAL;
    }
    if($level & 001) {
      $chars .= self::SPECIALCHARS;
    }

    $charArr = array();
    for($i = 0; $i < strlen($chars); $i++) {
      $charArr[] = $chars[$i];
    }
    $charArr = str_split( $chars );

    shuffle($charArr);

    return implode("", array_slice($charArr, 0, $len));
  }

  public static function makeStoragePasswd($passwd, $salt) {
    return md5($passwd . $salt);
  }

}
