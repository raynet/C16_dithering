<?php   
include('settings.php');
include('actions.php');

$image = false;

$pal = json_decode(file_get_contents('palette.json'),true);

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
        <form action="index.php" method="post" enctype="multipart/form-data">
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
