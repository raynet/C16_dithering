<?php

$im = new Imagick();
$im->readImage('palette.png');

$width = $im->getImageWidth();
$height = $im->getImageHeight();

$step = $width / 16;

$rgb = array();

for ($x=0; $x<16; $x++) {
    for ($y=0; $y<8; $y++) {
        $pixel = $im->getImagePixelColor($x*$step, $y*$step);
        $c = $pixel->getColor();
        $r = $c['r'];
        $g = $c['g'];
        $b = $c['b'];
        $i = round(0.2126 * $r + 0.7152 * $g + 0.0722 * $b);
        $rgb[] = array($r,$g,$b);        
    }
}

$yuv = array();

foreach ($rgb as $c) {
    $r = $c[0];
    $g = $c[1];
    $b = $c[2];
    $y = floor($r * .299000 + $g * .587000 + $b * .114000);
    $u = floor($r * -.168736 + $g * -.331264 + $b * .500000 + 128);
    $v = floor($r * .500000 + $g * -.418688 + $b * -.081312 + 128);    
    $yuv[] = array($y,$u,$v);    
}


$json = array();

$json['rgb'] = $rgb;
$json['yuv'] = $yuv;

echo json_encode($json);
echo "\n";