<?php   
include('settings.php');
include('actions.php');

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

$pal = json_decode(file_get_contents('palette.json'),true);

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
    <div id="menu">
        <form action="rgb.php" method="post" enctype="multipart/form-data">
            Select image to upload:
            <input type="file" name="upload" id="upload">
            <input type="submit" value="Upload Image" name="submit">
        </form>
        <p>[ <a href="index.php">Back</a> ]</p>
    </div>
    <?php
    if ($image) {
        list($xo, $yo, $type, $attr) = getimagesize("images/$image.png");
        $xs = $xo * 1;
        $ys = $yo * 1;
        
        echo '<div class="card" id="original"><p>Original:</p><img width="'.$xs.'" height="'.$ys.'" src="images/'.$image.'.png"></div>'."\n";
        
        if (!file_exists("images/$image.rgb.palette.png")) {
            $imagick = new \Imagick("images/$image.png");
            $palette = new \Imagick('palette.png');
            $imagick->remapImage($palette,  \Imagick::DITHERMETHOD_NO);
            //$imagick->remapImage($palette, \Imagick::DITHERMETHOD_RIEMERSMA);
            $imagick->writeImage("images/$image.rgb.palette.png");
        }
        echo '<div class="card" id="palette"><p>Palette:</p><img width="'.$xs.'" height="'.$ys.'" src="images/'.$image.'.rgb.palette.png"></div>'."\n";
                
        
        echo '<div class="card" id="scaled"><p>Scaled:</p><img width="'.$xs.'" height="'.$ys.'" src="images/'.$image.'.scaled.png"></div>'."\n";
        
        
                
        
    }
    
    ?>
</body>
</html>
