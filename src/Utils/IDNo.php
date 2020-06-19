<?php
namespace QKPHP\Common\Utils;

class IDNo {

  public static $PROVINCE_CODE = array(
    '11' => '北京',
    '12' => '天津', 
    '13' => '河北',
    '14' => '山西',
    '15' => '内蒙古',
    '21' => '辽宁',
    '22' => '吉林',
    '23' => '黑龙江',
    '31' => '上海',
    '32' => '江苏',
    '33' => '浙江',
    '34' => '安徽',
    '35' => '福建',
    '36' => '江西',
    '37' => '山东',
    '41' => '河南',
    '42' => '湖北',
    '43' => '湖南',
    '44' => '广东',
    '45' => '广西',
    '46' => '海南',
    '50' => '重庆',
    '51' => '四川',
    '52' => '贵州',
    '53' => '云南',
    '54' => '西藏',
    '61' => '陕西',
    '62' => '甘肃',
    '63' => '青海',
    '64' => '宁夏',
    '65' => '新疆',
    '71' => '台湾',
    '81' => '香港', 
    '82' => '澳门',
    '91' => '国外'
  );

  public static function check ($id) {
    if (empty($id)) {
      return false;
    }
    $id = "$id";
    $sum = 0;
    $len = strlen($id);

    if(!preg_match('/^\d{17}(\d|x)$/i', $id) and !preg_match('/^\d{15}$/i', $id)) {
      return false;
    }

    if(!isset(self::$PROVINCE_CODE[substr($id, 0, 2)])) {
      return false;
    }

    if($len == 15) {
      $id = self::to18($id);
    }

    $birthday = substr($id, 6, 4) . '-' . substr($id, 10, 2) . '-' . substr($id, 12, 2);
    if ($birthday != date('Y-m-d', strtotime($birthday))) {
      return false;
    }

    if(strtoupper(substr($id, 17, 1)) != self::getVerifyBit(substr($id, 0, 17))) {
      return false;
    }
    return $id;
  }

  private static function to18($id) {
    if(array_search(substr($id, 12, 3), array('996', '997', '998', '999')) !== false) {
      $id = substr($id, 0, 6) . '18' . substr($id, 6, 9);
    } else {
      $id = substr($id, 0, 6) . '19' . substr($id, 6, 9);
    }
    return $id . self::getVerifyBit($id);
  }

  private static function getVerifyBit($base) {
    if(strlen($base) != 17) {
      return false;
    }

    $factor     = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
    $verifyList = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
    $checkSum   = 0;
    for($i = 0; $i < strlen($base); $i++) {
      $checkSum += substr($base, $i, 1) * $factor[$i];
    }
    $mod = $checkSum % 11;
    return $verifyList[$mod];
  }

}
