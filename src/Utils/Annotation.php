<?php
namespace QKPHP\Common\Utils;

class Annotation {

  public static function parse($className, $classFile=null) {
    $cref = new \ReflectionClass($className);
    $classInfo = array(
      'class'=>self::parseComment($cref->getDocComment()),
      'methods'=>array());

    $methods = $cref->getMethods();
    foreach($methods as $method) {
      if ($classFile && $method->getFileName() != $classFile) {
        continue;
      }
      $classInfo['methods'][$method->getName()]['anno'] = self::parseComment($method->getDocComment());
      $params = $method->getParameters();
      $paramsName = array();
      foreach($params as $param) {
        $paramsName[] = $param->getName();
      }
      $classInfo['methods'][$method->getName()]['method'] = array($method->isPublic(), $paramsName, count($paramsName));
    }
    return $classInfo;
  }

  private static function parseComment($comment) {
      $comment = trim($comment, '/**');
      $comment = trim($comment, '*/');
      //$comment = trim(preg_replace('/\*/', '', $comment));
      $startPos = strpos($comment, '@');
      $comment = mb_substr($comment, $startPos);
      $comment = explode("@", $comment);
      $comment0 = array();
      foreach($comment as $c) {
        $c = self::_parseComment($c);
        if (!empty($c)) {
          $comment0[] = $c;
        }
      }
      return $comment0;
  }

  private static function _parseComment($comment) {
    $comment = trim($comment);
    if (empty($comment)) {
      return null;
    }
    $pos0 = strpos($comment, '(');
    $pos1 = strrpos($comment, ')');
    if ($pos0 === false && $pos1 === false) {
      return array($comment, null);
    }
    if (($pos0 === false && $pos1 !== false) || ($pos0 !== false && $pos1 === false) || ($pos0 > $pos1)) {
      return null;
    }
    $name = trim(substr($comment, 0, $pos0));
    list($status, $comment) = self::parseSimpleValue(trim(substr($comment, $pos0+1, $pos1-$pos0-1)));
    if ($status) {
      return array($name, $comment);
    }
    return array($name, self::parseValue($comment));
  }

  private static function parseSimpleValue($comment, $execStr=false) {
    if (is_numeric($comment)) {
      return array(true, $comment - 0);
    }
    if ($comment === "false" || $comment === "true") {
      return array(true, boolval($comment));
    }
    if ($comment === 'null') {
      return array(true, null);
    }
    $cs = substr($comment, 0, 1);
    $ce = substr($comment, -1, 1);
    if ($execStr) {
      $newfunc = function ($comment) {
        return eval('return '.$comment.';');
      };
      return array(true, $newfunc($comment));
    }
    return array(false, $comment);
  }

  private static function parseValue($comment) {
    $pairs = array('default'=> array());
    $name = array();
    $value = array();
    $startQuotation = '';
    $stage = 0;
    for ($i=0; $i<mb_strlen($comment); $i++) {
      $char = $comment[$i];
      $ascii = ord($char);
      // 0~9 A-Z _ a-z \ / : $ .
      if (($ascii >= 48 && $ascii <=57) || ($ascii >= 65 && $ascii <= 90) || ($ascii == 95) || ($ascii >= 97 && $ascii <=122) 
          || $ascii == 92 || $ascii == 47 || $ascii == 58 || $ascii == 36 || $ascii == 46) {
        if ($stage == 0) {
          $name[] = $char;
        } else {
          $value[] = $char;
        }
      } else if ($char == '=') {
        if ($stage == 0) {
          // name stop
          $startQuotation = '';
          $value = array();
          $stage = 1;
        }
      } else if ($char == '"' || $char == "'") {
        $value[] = $char;
        if ($stage == 1) {
          if ($char == $startQuotation) {
            // value stop
            if (count($name)>0) {
              if (empty($value)) {
                $pairs['default'][] = implode('', $name);
              } else {
                $pairs[implode('', $name)][] = implode('', $value);
              }
            } else if (!empty($value)) {
              $pairs['default'][] = implode('', $value);
            }
            $name = array();
            $value = array();
            $stage = 0;
            $startQuotation = '';
          } else {
            if (strlen($startQuotation) < 1) {
              $startQuotation = $char;
            }
          }
        } else {
          $startQuotation = $char;
          $name = array(); // 忽略name
          $stage = 1;
        }
      } else if ($char == ',') {
        if ($stage == 0) {
          if (count($name)>0) {
            if (empty($value)) {
              $pairs['default'][] = implode('', $name);
            } else {
              $pairs[implode('', $name)][] = implode('', $value);
            }
          } else if (!empty($value)) {
            $pairs['default'][] = implode('', $value);
          }
          $name = array();
          $value = array();
          $startQuotation = '';
        }
      } else {
        if ($stage == 1) {
          $value[] = $char;
        }
      }
    }
    if (!empty($name) && empty($value)) {
      $pairs['default'][] = implode('', $name);
    }
    if (empty($name) && !empty($value)) {
      $pairs['default'][] = implode('', $value);
    }
    if (!empty($name) && !empty($value)) {
      $pairs[implode('', $name)][] = implode('', $value);
    }
    foreach($pairs as $k=>$value) {
      if ($k == 'default') {
        for ($i=0; $i<count($value); $i++) {
          list($status, $v) = self::parseSimpleValue($value[$i], true);
          $pairs['default'][$i] = $v;
        }
      } else {
        list($status, $v) = self::parseSimpleValue($value, true);
        $pairs[$k] = $v;
      }
    }
    if (empty($pairs['default'])) {
      unset($pairs['default']);
    }
    if (count($pairs) == 1 && isset($pairs['default'])) {
      if (count($pairs['default']) == 1) {
        return $pairs['default'][0];
      } else {
        return $pairs['default'];
      }
    }
    return $pairs;
  }


}
