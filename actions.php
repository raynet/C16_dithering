<?php   

if (isset($_FILES['upload'])) {
    $filename = $_FILES['upload']['tmp_name'];
    list($x, $y, $type, $attr) = getimagesize($filename);
    $md5 = md5_file($filename);
    if ($x>0 && $y>0) {
        $imagick = new \Imagick($filename);
        $filterType = imagick::FILTER_LANCZOS;
        $blur = 0.5;
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
        $height = ceil($height/8)*8;
        $width = ceil($width/8)*8;
        $imagick->resizeImage($width, $height, $filterType, $blur, $bestFit);
        $imagick->writeImage("images/$md5.png");
        $imagick->resizeImage($width / $dx, $height / $dy, $filterType, $blur, $bestFit);
        $imagick->writeImage("images/$md5.scaled.png");
        $base = $_SERVER['REQUEST_URI'];
        header("Location: $base?image=$md5");
        exit;
    }
}
