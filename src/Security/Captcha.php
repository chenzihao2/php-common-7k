<?php
/**
 * 验证码生成器
 */
namespace QKPHP\Common\Security;

class Captcha {

  //style
  private $width = 140;
  private $height = 50;
  private $font = "captcha.ttf";
  private $fontSize = 24;
  private $startImage;

  private $codeLen = 4;

  public static $V = array("a", "e", "i", "o", "u", "y");
  public static $VN = array("a", "e", "i", "o", "u", "y", "2", "3", "4", "5", "6", "7", "8", "9");
  public static $C = array("b", "c", "d", "f", "g", "h", "j", "k", "m", "n", "p", "q", "r", "s", "t", "u", "v", "w", "x", "z");
  public static $CN = array("b", "c", "d", "f", "g", "h", "j", "k", "m", "n", "p", "q", "r", "s", "t", "u", "v", "w", "x", "z", "2", "3", "4", "5", "6", "7", "8", "9");

  private $level = 2;
  private $dotNoiseLevel = 40;
  private $lineNoiseLevel = 6;

  private $code = 'wspd';
  private $image;

  public function __construct() {
    if(!extension_loaded("gd")) {
      echo("Image CAPTCHA requires GD extension");
      exit;
    }

    if(!function_exists("imagepng")) {
      echo("Image CAPTCHA requires PNG support");
      exit;
    }

    if(!function_exists("imageftbbox")) {
      echo("Image CAPTCHA requires FT fonts support");
      exit;
    }
  }

  public function setStyle($options) {
    //style
    $this->font       = (!empty($options['font'])) ? $options['font'] : __DIR__ . '/' . $this->font;
    $this->width      = (!empty($options['width'])) ? $options['width'] : $this->width;
    $this->height     = (!empty($options['height'])) ? $options['height'] : $this->height;
    $this->fontSize   = (!empty($options['fontSize'])) ? $options['fontSize'] : $this->fontSize;
    $this->startImage = (!empty($options['startImage'])) ? $options['startImage'] : $this->startImage;
    $this->codeLen    = (!empty($options['codeLen'])) ? $options['codeLen'] : $this->codeLen;
  }

  public function setStartImage($startImage) {
    $this->startImage = $startImage;
  }

  public function setLevel($level) {
    //level
    $this->level          = $level;
    $this->dotNoiseLevel  = rand($level*1, ($level)*2);
    $this->lineNoiseLevel = rand($level, $level+1);
  }

  public function generate() {
    $this->code  = $this->generateCode();
    $this->image = $this->generateImage();
  }

  public function getImage() {
    return $this->image;
  }

  public function getCode() {
    return $this->code;
  }

  private function generateCode() {
    $word       = '';
    $wordLen    = $this->codeLen;
    $vowels     = $this->level > 2 ? static::$VN : static::$V;
    $consonants = $this->level > 2 ? static::$CN : static::$C;

    for($i = 0; $i < $wordLen; $i = $i+2) {
      $consonant = $consonants[array_rand($consonants)];
      $vowel     = $vowels[array_rand($vowels)];
      $word .= $consonant . $vowel;
    }

    if(strlen($word) > $wordLen) {
      $word = substr($word, 0, $wordLen);
    }
    return $word;
  }

  private function generateImage() {
    if(empty($this->font)) {
      echo('Image CAPTCHA requires font');
      exit;
    }

    $w = $this->width;
    $h = $this->height;

    if(empty($this->startImage)) {
      $img = imagecreatetruecolor($w, $h);
    } else {
      $img = imagecreatefrompng($this->startImage);

      if(!$img) {
        echo("Can not load start image '{$this->startImage}'");
        exit;
      }
      $w = imagesx($img);
      $h = imagesy($img);
    }

    $textColor = imagecolorallocate($img, 0, 0, 0);
    $bgColor   = imagecolorallocate($img, 255, 255, 255);
    if(empty($this->startImage)) {
      imagefilledrectangle($img, 0, 0, $w-1, $h-1, $bgColor);
    }
    $textbox = imageftbbox($this->fontSize, 0, $this->font, $this->code);
    $x       = ($w-($textbox[2]-$textbox[0]))/2;
    $y       = ($h-($textbox[7]-$textbox[1]))/2;
    imagefttext($img, $this->fontSize, 0, $x, $y, $textColor, $this->font, $this->code);

    // generate noise
    for($i = 0; $i < $this->dotNoiseLevel; $i++) {
      imagefilledellipse($img, mt_rand(0, $w), mt_rand(0, $h), 2, 2, $textColor);
    }
    for($i = 0; $i < $this->lineNoiseLevel; $i++) {
      imageline($img, mt_rand(0, $w), mt_rand(0, $h), mt_rand(0, $w), mt_rand(0, $h), $textColor);
    }

    // transformed image
    $img2    = imagecreatetruecolor($w, $h);
    $bgColor = imagecolorallocate($img2, 255, 255, 255);
    imagefilledrectangle($img2, 0, 0, $w-1, $h-1, $bgColor);

    // apply wave transforms
    $freq1 = $this->randomFreq();
    $freq2 = $this->randomFreq();
    $freq3 = $this->randomFreq();
    $freq4 = $this->randomFreq();

    $ph1 = $this->randomPhase();
    $ph2 = $this->randomPhase();
    $ph3 = $this->randomPhase();
    $ph4 = $this->randomPhase();

    $szx = $this->randomSize();
    $szy = $this->randomSize();

    for($x = 0; $x < $w; $x++) {
      for($y = 0; $y < $h; $y++) {
        $sx = $x+(sin($x*$freq1+$ph1)+sin($y*$freq3+$ph3))*$szx;
        $sy = $y+(sin($x*$freq2+$ph2)+sin($y*$freq4+$ph4))*$szy;

        if($sx < 0 || $sy < 0 || $sx >= $w-1 || $sy >= $h-1) {
          continue;
        } else {
          $color   = (imagecolorat($img, $sx, $sy) >> 16) & 0xFF;
          $colorX  = (imagecolorat($img, $sx+1, $sy) >> 16) & 0xFF;
          $colorY  = (imagecolorat($img, $sx, $sy+1) >> 16) & 0xFF;
          $colorXY = (imagecolorat($img, $sx+1, $sy+1) >> 16) & 0xFF;
        }

        if($color == 255 && $colorX == 255 && $colorY == 255 && $colorXY == 255) {
          // ignore background
          continue;
        } elseif($color == 0 && $colorX == 0 && $colorY == 0 && $colorXY == 0) {
          // transfer inside of the image as-is
          $newcolor = 0;
        } else {
          // do antialiasing for border items
          $fracX  = $sx-floor($sx);
          $fracY  = $sy-floor($sy);
          $fracX1 = 1-$fracX;
          $fracY1 = 1-$fracY;

          $newcolor = $color*$fracX1*$fracY1
            +$colorX*$fracX*$fracY1
            +$colorY*$fracX1*$fracY
            +$colorXY*$fracX*$fracY;
        }

        imagesetpixel($img2, $x, $y, imagecolorallocate($img2, $newcolor, $newcolor, $newcolor));
      }
    }

    // generate noise
    for($i = 0; $i < $this->dotNoiseLevel; $i++) {
      imagefilledellipse($img2, mt_rand(0, $w), mt_rand(0, $h), 2, 2, $textColor);
    }

    for($i = 0; $i < $this->lineNoiseLevel; $i++) {
      imageline($img2, mt_rand(0, $w), mt_rand(0, $h), mt_rand(0, $w), mt_rand(0, $h), $textColor);
    }
    imagedestroy($img);
    return $img2;
  }

  private function randomFreq() {
    return mt_rand(700000, 1000000)/15000000;
  }

  private function randomPhase() {
    return mt_rand(0, 3141592)/1000000;
  }

  private function randomSize() {
    return mt_rand(300, 700)/100;
  }
}
