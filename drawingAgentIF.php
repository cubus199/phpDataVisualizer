<?php

// constants for alignment
define('LEFT', 0);
define('RIGHT', 1);
define('CENTER', 2);
define('TOP', 3);
define('BOTTOM', 4);

// constants for symbols; can also be retrieved by constant('name')
define('CIRCLE', 0);
define('SQUARE', 1);
define('TRIANGLE', 2);
define('CROSS', 3);
define('DIAMOND', 4);

interface drawingAgent{
    public function reinit(); //resets drawingAgent
    public function finish(); //finishes graph-drawing and returns product

    public function drawLine(float $x1, float $y1, float $x2, float $y2, color $color, bool $dashed = false); // drawing a straight line from point to point
    public function drawRectangle(float $x1, float $y1, float $x2, float $y2, color $color, bool $filled = true); // drawing a rectangle
    public function drawText(float $x, float $y, string $text, font $font, float $size, color $color, int $xAlign = LEFT, int $yAlign = BOTTOM, float $angle = 0); // draw text at the given position the anchor is specified with xAlign and yAlign
    public function drawSymbol(float $x, float $y, int $type, color $color, float $size); // draw a symbol selected by providing a constant
    public function drawPolyLine(); // to be specified
    public function drawPolygon(); // to be specified
}
?>