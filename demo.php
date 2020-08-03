<?php
require_once 'svgDrawingAgent.php';

$da = new svgDrawingAgent(1280, 720, new color('#ffffff'));

$da->drawRectangle(10,10,1270,710,new color('#A6101050'));
$da->drawPolygon(array(640,200, 980,360, 640,520, 300,360),new color('#1010A650'));
$da->drawLine(10,10,1270,710,4,new color('#A61010'));
$da->drawLine(10,710,1270,10,4,new color('#A61010'));
$da->drawArc(200,120,80,180,270,new color('#10A61050'));
$da->drawArc(1080,120,80,270,0,new color('#10A61050'));
$da->drawArc(1080,600,80,0,90,new color('#10A61050'));
$da->drawArc(200,600,80,90,180,new color('#10A61050'));

$points = array();
for($i = 0; $i <= 100; $i++){
	array_push($points, 400 + $i * 480 / 100);
	array_push($points, 360 + sin($i / 100 * M_PI * 2) * 160);
}

$da->drawPolyLine($points, 2, new color('#6010A0'), true);

$da->drawText(640, 360, 'Test', new font('FreeSans.ttf', 'FreeSans'), 80, new color('#000000'), CENTER, CENTER);

print_r($da->finish());
?>