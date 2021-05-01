<?php   
include('settings.php');
header('Content-Type: text/html; charset=utf-8');
$image = false;


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
        $imagick->resizeImage($width, $height, $filterType, $blur, $bestFit);
        $imagick->writeImage("images/$md5.png");
        $imagick->resizeImage($width / $dx, $height / $dy, $filterType, $blur, $bestFit);
        $imagick->writeImage("images/$md5.scaled.png");
        header("Location: yuv.php?image=$md5");
        exit;
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
    <div id="menu">
        <form action="yuv.php" method="post" enctype="multipart/form-data">
            Select image to upload:
            <input type="file" name="upload" id="upload">
            <input type="submit" value="Upload Image" name="submit">
        </form>
    </div>
    <?php
        $images = glob('images/????????????????????????????????.png');
        foreach ($images as $img) {
            list($xo, $yo, $type, $attr) = getimagesize($img);
            $xs = $xo * 1;
            $ys = $yo * 1;
            $image = substr($img,7,32);
            echo '<div class="card"><p>[ <a href="rgb.php?image='.$image.'">RGB</a> ] [ <a href="four.php?image='.$image.'">Four</a> ] [ <a href="gray.php?image='.$image.'">Gray</a> ] [ <a href="yuv.php?image='.$image.'">YUV</a> ]</p><img width="'.$xs.'" height="'.$ys.'" src="'.$img.'"></div>'."\n";
        }
    ?>
</body>
</html>
