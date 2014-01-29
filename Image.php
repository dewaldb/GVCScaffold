<?php
/**
 * Description of Image
 *
 * @author dewald
 */
class Image {
    static private $image_folder = "uploads";
    
    static private $presets = array(
        '16pxCrop'=>array('width'=>16,'height'=>16,'type'=>'crop'),
        '24pxCrop'=>array('width'=>24,'height'=>24,'type'=>'crop'),
        '32pxCrop'=>array('width'=>32,'height'=>32,'type'=>'crop'),
        '48pxCrop'=>array('width'=>48,'height'=>48,'type'=>'crop'),
        '64pxCrop'=>array('width'=>64,'height'=>64,'type'=>'crop'),
        'shop-banner'=>array('width'=>730,'height'=>100,'type'=>'crop'),
        'shop-logo'=>array('width'=>88,'height'=>88,'type'=>'fit'),
        'shop-list-item'=>array('width'=>226,'height'=>226,'type'=>'fit-width'),
        'cart-item'=>array('width'=>64,'height'=>64,'type'=>'fit-width'),
        'product-full-mini'=>array('width'=>46,'height'=>46,'type'=>'crop'),
        'image-viewer-full'=>array('width'=>720,'height'=>480,'type'=>'fit'),
        'home-slider'=>array('width'=>550,'height'=>250,'type'=>'fit'),
    );
    
    /*
     * Resizes an image according to the specified preset and saves it. If the resized file 
     * already exists it is loaded instead.
     */
    static function get($src,$size_preset) {
        if(isset(Image::$presets[$size_preset]) && $src!="") {
            //print "</br>src: ".$src."</br>";
            $fullFilename = substr($src,strripos($src, "/")+1);
            //print "full filename: ".$fullFilename."</br>";
            $filename = substr($fullFilename,0,strripos($fullFilename, "."));
            //print "filename: ".$filename."</br>";
            $extension = substr($fullFilename,strripos($fullFilename, ".")+1);
            
            if(stripos($src,"http://")!==false) {
                // check the file header first
                $headers = get_headers($src,1);
                if(stripos($headers["Content-Type"],"image")===false)
                    return ""; // bad image
                
                $folder = Image::$image_folder."/external";
                if(!file_exists($folder)) {
                    mkdir($folder,0755);
                }
            } else {
                $folder = substr($src,0,strripos($src, "/"));
            }
            //print $folder."</br>";
            $presetFolder = $folder."/".$size_preset;
            $newFilename = $presetFolder."/".$filename.".".$extension;
            
            if(file_exists($newFilename)) {
                return $newFilename;
            }
            
            if(!file_exists($presetFolder)) {
                mkdir($presetFolder,0755);
            }
            
            if(!list($width, $height) = getimagesize($src)) return "Unsupported picture type!";
            
            $widthOffset = 0;
            $heightOffset = 0;
            
            $finalWidth = Image::$presets[$size_preset]['width'];
            $finalHeight = Image::$presets[$size_preset]['height'];
            
            if(Image::$presets[$size_preset]['type']=='crop') {
                $widthOffset = ($width/2)-($finalWidth/2);
                $heightOffset = ($height/2)-($finalHeight/2);
                /*if($width > $height) {
                    $widthOffset = ($width - $height)/2;
                    $width = $height;
                } else {
                //if($height > $width) {
                    $heightOffset = ($height - $width)/2;
                    $height = $width;
                }*/
            }
            
            if(Image::$presets[$size_preset]['type']=='fit') {
                $a = $finalWidth/$finalHeight;
                $b = $width/$height;
                
                //print $a. "x". $b. "</br>";
                
                if($a >= $b) {
                    if($finalWidth >= $finalHeight) {
                        $finalWidth = ((1/$height)*$finalHeight)*$width;
                    } else {
                        $finalHeight = ((1/$height)*$finalWidth)*$width;
                    }
                } else {
                    if($finalWidth >= $finalHeight) {
                        $finalHeight = ((1/$width)*$finalWidth)*$height;
                    } else {
                        $finalWidth = ((1/$width)*$finalHeight)*$height;
                    }
                }
                /*
                if($width >= $height && $finalWidth >= $finalHeight) {
                    $scale = (1/$height)*$finalHeight;

                    $finalWidth = $width * $scale;
                    $finalHeight = $height * $scale;
                }*/
                //print $filename.": ".$finalWidth. "x". $finalHeight."</br>";
                /*
                if($width > $height) {
                    $scale = (1/$width)*$smallest;

                    $finalWidth = $width * $scale;
                    $finalHeight = $height * $scale;
                    die(print $filename.": ".$scale. "*".$finalWidth. "x". $finalHeight);
                }*/
            }
            
            if(Image::$presets[$size_preset]['type']=='fit-width') {
                $a = $finalWidth/$finalHeight;
                $b = $width/$height;
                
                //print $a. "x". $b. "</br>";
                
                //if($a >= $b) {
                    //if($finalWidth >= $finalHeight) {
                        $finalWidth = ((1/$width)*$finalWidth)*$width;
                        $finalHeight = ((1/$width)*$finalWidth)*$height;
                    //} else {
                        //$finalHeight = ((1/$height)*$finalWidth)*$width;
                    //}
                /*} else {
                    if($finalWidth >= $finalHeight) {
                        $finalHeight = ((1/$width)*$finalWidth)*$height;
                    } else {
                        $finalWidth = ((1/$width)*$finalHeight)*$height;
                    }
                }*/
                /*
                if($width >= $height && $finalWidth >= $finalHeight) {
                    $scale = (1/$height)*$finalHeight;

                    $finalWidth = $width * $scale;
                    $finalHeight = $height * $scale;
                }*/
                //print $filename.": ".$finalWidth. "x". $finalHeight."</br>";
                /*
                if($width > $height) {
                    $scale = (1/$width)*$smallest;

                    $finalWidth = $width * $scale;
                    $finalHeight = $height * $scale;
                    die(print $filename.": ".$scale. "*".$finalWidth. "x". $finalHeight);
                }*/
            }
            
            $type = strtolower($extension);
            if($type == 'jpeg') $type = 'jpg';
            switch($type){
                case 'gif': $image = imagecreatefromgif($src); break;
                case 'jpg': $image = imagecreatefromjpeg($src); break;
                case 'png': $image = imagecreatefrompng($src); break;
                default : return "Unsupported picture type!";
            }
            
            $new = imagecreatetruecolor($finalWidth, $finalHeight);
            
            // preserve transparency
            if($type == "gif" or $type == "png"){
                imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
                imagealphablending($new, false);
                imagesavealpha($new, true);
            }
            
            if(Image::$presets[$size_preset]['type']=='crop') {
                imagecopyresampled($new, $image, 0, 0, $widthOffset, $heightOffset, $finalWidth, $finalHeight, $finalWidth, $finalHeight);
            } else {
                imagecopyresampled($new, $image, 0, 0, $widthOffset, $heightOffset, $finalWidth, $finalHeight, $width, $height);
            }
            
            switch($type){
                case 'gif': imagegif($new, $newFilename); break;
                case 'jpg': imagejpeg($new, $newFilename,90); break;
                case 'png': imagepng($new, $newFilename); break;
            }
            
            return $newFilename;
        }
    }
    
    static function colorPalette($src, $numColors, $granularity = 2) {
        $granularity = max(1, abs((int)$granularity)); 
        $colors = array(); 
        $size = @getimagesize($src); 
        if($size === false) {
            user_error("Unable to get image size data"); 
            return false; 
        }

        // Get the width and height.
        $width = $size[0];
        $height = $size[1];

        $img = imagecreatefromstring(file_get_contents($src));

        if(!$img) {
           user_error("Unable to open image file"); 
           return false; 
        }

        // Create a white background, the same size as the original.
        $bg = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($bg, 255, 255, 255);
        imagefill($bg, 0, 0, $white);

        // Merge the two images.
        imagecopyresampled(
            $bg, $img,
            0, 0, 0, 0,
            $width, $height,
            $width, $height);

        imagefilter($img, IMG_FILTER_GRAYSCALE);

        for($x = 0; $x < $size[0]; $x += $granularity) {
           for($y = 0; $y < $size[1]; $y += $granularity) {
              $thisColor = imagecolorat($img, $x, $y); 
              $rgb = imagecolorsforindex($img, $thisColor); 
              $red = round(round(($rgb['red'] / 0x33)) * 0x33); 
              $green = round(round(($rgb['green'] / 0x33)) * 0x33); 
              $blue = round(round(($rgb['blue'] / 0x33)) * 0x33); 
              $thisRGB = sprintf('%02X%02X%02X', $red, $green, $blue); 
              if(array_key_exists($thisRGB, $colors)) {
                 $colors[$thisRGB]++;
              } else {
                 $colors[$thisRGB] = 1; 
              }
           }
        }

        arsort($colors);

        return array_slice(array_keys($colors), 0, $numColors);
    }
    
    /*
     * Invert an HTML color code
     */
    static function colorInverse($color,$multi=1){
        $color = str_replace('#', '', $color);
        if (strlen($color) != 6){ return '#000000'; }
        $rgb = '';
        for ($x=0;$x<3;$x++){
            $c = 255 - (hexdec(substr($color,(2*$x),2))*$multi);
            $c = ($c < 0) ? 0 : dechex($c);
            $rgb .= (strlen($c) < 2) ? '0'.$c : $c;
        }
        return '#'.$rgb;
    }
    
    /*
     * Multiply an HTML color code
     */
    static function colorMultiply($color,$multi){
        $color = str_replace('#', '', $color);
        if (strlen($color) != 6){ return '#000000'; }
        $rgb = '';
        for ($x=0;$x<3;$x++){
            $c = hexdec(substr($color,(2*$x),2)) * $multi;
            $c = dechex(max(0,min(255,$c)));
            $rgb .= (strlen($c) < 2) ? '0'.$c : $c;
        }
        return '#'.$rgb;
    }
}
?>