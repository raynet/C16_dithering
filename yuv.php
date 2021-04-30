<?php   
header('Content-Type: text/html; charset=utf-8');
$image = false;

class RGB
{
	public $R;
	public $G;
	public $B;
}

class YUV
{
	public $Y;
	public $U;
	public $V;
}


$cx = 320;
$dx = 2;
$cy = 200;
$dy = 1;

$pal = json_decode(file_get_contents('palette.json'),true);

if (isset($_FILES['upload'])) {
    $filename = $_FILES['upload']['tmp_name'];
    list($x, $y, $type, $attr) = getimagesize($filename);
    $md5 = md5_file($filename);
    if ($x>0 && $y>0) {
        $imagick = new \Imagick($filename);
        $filterType = imagick::FILTER_LANCZOS;
        $blur = 0.8;
        $bestFit = false;
        if ($x>=$y) {
            $height = $cy;
            $width = round($x / ($y / $cy)  );
            if ($width < $cx) {
                $height = round( $height / ( $width/$cx ) );
                $width = $cx;
            } 
        } else {
            $width = $cx;
            $height =  round($y / ($x / $cx) );
            if ($height < $cy) {
                $width = round( $width / ( $height/$cy) );
                $height = $cy;
            }
        }
        //echo "($width, $height, $filterType, $blur, $bestFit)";
        $imagick->resizeImage($width, $height, $filterType, $blur, $bestFit);
        $imagick->writeImage("images/$md5.png");
        $imagick->resizeImage($width / $dx, $height / $dy, $filterType, $blur, $bestFit);
        $imagick->writeImage("images/$md5.scaled.png");
        header("Location: yuv.php?image=$md5");
        exit;
    }
}

if (isset($_REQUEST['image'])) {
    $images = glob('images/????????????????????????????????.png');
    foreach ($images as $img) {
        if (strpos($img,$_REQUEST['image'])!==false) {
            $image = $_REQUEST['image'];
        }
    }
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>C16 image converter</title>
    <link rel="stylesheet" type="text/css" href="css/style.css?<?php echo time(); ?>">
    <script src="js/jquery.js"></script>
    <script>
        $(document).ready(function(){
        });
</script>
</head>
<body>
    <form action="yuv.php" method="post" enctype="multipart/form-data">
        Select image to upload:
        <input type="file" name="upload" id="upload">
        <input type="submit" value="Upload Image" name="submit">
    </form>
    <?php
    if ($image) {
        list($xo, $yo, $type, $attr) = getimagesize("images/$image.png");
        $xs = $xo * 1;
        $ys = $yo * 1;
        
        echo '<div class="card" id="original"><p>Original:</p><img width="'.$xs.'" height="'.$ys.'" src="images/'.$image.'.png"></div>'."\n";
        
        echo '<div class="card" id="scaled"><p>Scaled:</p><img width="'.$xs.'" height="'.$ys.'" src="images/'.$image.'.scaled.png"></div>'."\n";
        
        if (!file_exists("images/$image.yuv.png")) {
            $im = imagecreatefrompng("images/$image.scaled.png");
            for ($py=0; $py<imagesy($im); $py++) {
                for ($px=0; $px<imagesx($im); $px++) {
                    $pixel = imagecolorat($im, $px, $py);
                    
                    $rgb = new RGB();
                    $rgb->R = ($pixel >> 16) & 0xFF;
                    $rgb->G = ($pixel >> 8) & 0xFF;
                    $rgb->B = $pixel & 0xFF;
                    
                    $yuv = new YUV();
                    $yuv->Y = floor($rgb->R * .299000 + $rgb->G * .587000 + $rgb->B * .114000);
                    $yuv->U = floor($rgb->R * -.168736 + $rgb->G * -.331264 + $rgb->B * .500000 + 128);
                    $yuv->V = floor($rgb->R * .500000 + $rgb->G * -.418688 + $rgb->B * -.081312 + 128);
                    
                    $col = imageColorAllocate($im, $yuv->Y, $yuv->U, $yuv->V);
                    imagesetpixel($im,$px,$py,$col);
                }
            }
            imagepng($im,"images/$image.yuv.png",9);
        }
        echo '<div class="card" id="yuv"><p>YUV:</p><img width="'.$xs.'" height="'.$ys.'" src="images/'.$image.'.yuv.png"></div>'."\n";
        
        if (!file_exists("images/$image.rgb.png")) {
            $im = imagecreatefrompng("images/$image.yuv.png");
            for ($py=0; $py<imagesy($im); $py++) {
                for ($px=0; $px<imagesx($im); $px++) {
                    $pixel = imagecolorat($im, $px, $py);
                    
                    $yuv = new YUV();
                    $yuv->Y = ($pixel >> 16) & 0xFF;
                    $yuv->U = ($pixel >> 8) & 0xFF;
                    $yuv->V = $pixel & 0xFF;
                    
                    $rgb = new RGB();
                    $rgb->R = floor($yuv->Y + 1.4075 * ($yuv->V - 128));
                    $rgb->G = floor($yuv->Y - 0.3455 * ($yuv->U - 128) - (0.7169 * ($yuv->V - 128)));
                    $rgb->B = floor($yuv->Y + 1.7790 * ($yuv->U - 128));                    
                    
                    $col = imageColorAllocate($im, $rgb->R, $rgb->G, $rgb->B);
                    imagesetpixel($im,$px,$py,$col);
                }
            }
            imagepng($im,"images/$image.rgb.png",9);
        }
        echo '<div class="card" id="rgb"><p>RGB:</p><img width="'.$xs.'" height="'.$ys.'" src="images/'.$image.'.rgb.png"></div>'."\n";
        
        if (!file_exists("images/$image.nearest.yuv.png")) {
            $im = imagecreatefrompng("images/$image.yuv.png");
            $used = array();
            for ($py=0; $py<imagesy($im); $py++) {
                for ($px=0; $px<imagesx($im); $px++) {
                    $pixel = imagecolorat($im, $px, $py);
                    
                    $y = ($pixel >> 16) & 0xFF;
                    $u = ($pixel >> 8) & 0xFF;
                    $v = $pixel & 0xFF;
                    
                    $d = 65535;
                    $i = 0;
                    
                    foreach ($pal['yuv'] as $key=>$c) {
                        $diff = abs($y-$c[0])+abs($u-$c[1])+abs($v-c[2]);
                        if ($diff<$d) {
                            $d = $diff;
                            $i = $key;
                        }
                    }
                    @$used[$i]++;
                    $col = imageColorAllocate($im, $pal['yuv'][$i][0],$pal['yuv'][$i][1],$pal['yuv'][$i][2]);
                    imagesetpixel($im,$px,$py,$col);
                }
            }
            imagepng($im,"images/$image.nearest.yuv.png",9);
            arsort($used);
            file_put_contents("images/$image.nearest.json",json_encode($used));
        }
        echo '<div class="card" id="yuv"><p>YUV nearest:</p><img width="'.$xs.'" height="'.$ys.'" src="images/'.$image.'.nearest.yuv.png"></div>'."\n";
        
        if (!file_exists("images/$image.nearest.rgb.png")) {
            $im = imagecreatefrompng("images/$image.nearest.yuv.png");
            for ($py=0; $py<imagesy($im); $py++) {
                for ($px=0; $px<imagesx($im); $px++) {
                    $pixel = imagecolorat($im, $px, $py);
                    
                    $yuv = new YUV();
                    $yuv->Y = ($pixel >> 16) & 0xFF;
                    $yuv->U = ($pixel >> 8) & 0xFF;
                    $yuv->V = $pixel & 0xFF;
                    
                    $rgb = new RGB();
                    $rgb->R = floor($yuv->Y + 1.4075 * ($yuv->V - 128));
                    $rgb->G = floor($yuv->Y - 0.3455 * ($yuv->U - 128) - (0.7169 * ($yuv->V - 128)));
                    $rgb->B = floor($yuv->Y + 1.7790 * ($yuv->U - 128));                    
                    
                    $col = imageColorAllocate($im, $rgb->R, $rgb->G, $rgb->B);
                    imagesetpixel($im,$px,$py,$col);
                }
            }
            imagepng($im,"images/$image.nearest.rgb.png",9);
        }
        echo '<div class="card" id="rgb"><p>RGB nearest:</p><img width="'.$xs.'" height="'.$ys.'" src="images/'.$image.'.nearest.rgb.png"></div>'."\n";
        
        
        if (!file_exists("images/$image.dither.yuv.png")) {
            $im = imagecreatefrompng("images/$image.yuv.png");
            $used = array();
            $error = array();
            for ($py=0; $py<imagesy($im); $py++) {
                for ($px=0; $px<imagesx($im); $px++) {
                    $pixel = imagecolorat($im, $px, $py);
                    
                    $y = ($pixel >> 16) & 0xFF + $error[$px][$py][0];
                    $u = ($pixel >> 8) & 0xFF + $error[$px][$py][1];
                    $v = $pixel & 0xFF + $error[$px][$py][2];
                    
                    $d = 65535;
                    $i = 0;
                    
                    foreach ($pal['yuv'] as $key=>$c) {
                        $diff = abs($y-$c[0])+abs($u-$c[1])+abs($v-c[2]);
                        if ($diff<$d) {
                            $d = $diff;
                            $i = $key;
                        }
                    }
                    
                    $err[0] = $y - $pal['yuv'][$i][0];
                    $err[1] = $y - $pal['yuv'][$i][1];
                    $err[2] = $y - $pal['yuv'][$i][2];
                    
                    $error[$px+1][$py][0] += $err[0]/8; 
                    $error[$px+1][$py][1] += $err[1]/8; 
                    $error[$px+1][$py][2] += $err[2]/8; 
                    
                    $error[$px+2][$py][0] += $err[0]/8; 
                    $error[$px+2][$py][1] += $err[1]/8; 
                    $error[$px+2][$py][2] += $err[2]/8; 
                    
                    $error[$px+1][$py+1][0] += $err[0]/8; 
                    $error[$px+1][$py+1][1] += $err[1]/8; 
                    $error[$px+1][$py+1][2] += $err[2]/8; 
                    
                    $error[$px-1][$py+1][0] += $err[0]/8; 
                    $error[$px-1][$py+1][1] += $err[1]/8; 
                    $error[$px-1][$py+1][2] += $err[2]/8; 
                    
                    $error[$px][$py+1][0] += $err[0]/8; 
                    $error[$px][$py+1][1] += $err[1]/8; 
                    $error[$px][$py+1][2] += $err[2]/8; 
                    
                    $error[$px][$py+2][0] += $err[0]/8; 
                    $error[$px][$py+2][1] += $err[1]/8; 
                    $error[$px][$py+2][2] += $err[2]/8; 
                    
                    $xx = floor($px/4);
                    $yy = floor($py/8);
                    @$used[$xx][$yy][$i]++;
                    $col = imageColorAllocate($im, $pal['yuv'][$i][0],$pal['yuv'][$i][1],$pal['yuv'][$i][2]);
                    imagesetpixel($im,$px,$py,$col);
                }
            }
            imagepng($im,"images/$image.dither.yuv.png",9);
            file_put_contents("images/$image.dither.json",json_encode($used));
        }
        echo '<div class="card" id="yuv"><p>YUV dither:</p><img width="'.$xs.'" height="'.$ys.'" src="images/'.$image.'.dither.yuv.png"></div>'."\n";
        
        if (!file_exists("images/$image.dither.rgb.png")) {
            $im = imagecreatefrompng("images/$image.dither.yuv.png");
            for ($py=0; $py<imagesy($im); $py++) {
                for ($px=0; $px<imagesx($im); $px++) {
                    $pixel = imagecolorat($im, $px, $py);
                    
                    $yuv = new YUV();
                    $yuv->Y = ($pixel >> 16) & 0xFF;
                    $yuv->U = ($pixel >> 8) & 0xFF;
                    $yuv->V = $pixel & 0xFF;
                    
                    $rgb = new RGB();
                    $rgb->R = floor($yuv->Y + 1.4075 * ($yuv->V - 128));
                    $rgb->G = floor($yuv->Y - 0.3455 * ($yuv->U - 128) - (0.7169 * ($yuv->V - 128)));
                    $rgb->B = floor($yuv->Y + 1.7790 * ($yuv->U - 128));                    
                    
                    $col = imageColorAllocate($im, $rgb->R, $rgb->G, $rgb->B);
                    imagesetpixel($im,$px,$py,$col);
                }
            }
            imagepng($im,"images/$image.dither.rgb.png",9);
        }
        echo '<div class="card" id="rgb"><p>RGB dither:</p><img width="'.$xs.'" height="'.$ys.'" src="images/'.$image.'.dither.rgb.png"></div>'."\n";
        
                
        
    }
    
    ?>
</body>
</html>
