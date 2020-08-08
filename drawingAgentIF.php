<?php

require_once 'color.php';
require_once 'font.php';

// constants for alignment
define('LEFT', 0);
define('RIGHT', 1);
define('CENTER', 2);
define('TOP', 3);
define('BOTTOM', 4);

const RAW_OUTPUT = 1; //also png with imagick
const PNG_BASE64_OUTPUT = 2;
const JPG_BASE64_OUTPUT = 3;
const JPEG_BASE64_OUTPUT = 3;

interface drawingAgentIF{
	public function finish(); //finishes graph-drawing and returns product

    /**
     * drawing a straight line from point to point
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     * @param float $width
     * @param color $color
     * @param bool $dashed
     */
	public function drawLine(float $x1, float $y1, float $x2, float $y2, float $width, color $color, bool $dashed = false): void;

    /**
     * drawing a rectangle, width is only used if rect is not filled
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     * @param color $color
     * @param bool $filled
     * @param float|int $width
     */
	public function drawRectangle(float $x1, float $y1, float $x2, float $y2, color $color, bool $filled = true, float $width = 2): void;

    /**
     * draw text at the given position the anchor is specified with xAlign and yAlign
     * @param float $x
     * @param float $y
     * @param string $text
     * @param font $font
     * @param float $size
     * @param color $color
     * @param int $xAlign
     * @param int $yAlign
     * @param float|int $angle
     */
	public function drawText(float $x, float $y, string $text, font $font, float $size, color $color, int $xAlign = LEFT, int $yAlign = BOTTOM, float $angle = 0): void;

    /**
     * draws a circular arc from angle start to finish, width is only used if arc is not filled
     * @param float $x
     * @param float $y
     * @param float $radius
     * @param float $start
     * @param float $end
     * @param color $color
     * @param bool $filled
     * @param float|int $width
     */
	public function drawArc(float $x, float $y, float $radius, float $start, float $end, color $color, bool $filled = true, float $width = 2): void;

    /**
     * draws lines between the specified points
     * @param array $points
     * @param float $width
     * @param color $color
     * @param bool $dashed
     */
	public function drawPolyLine(array $points, float $width, color $color, bool $dashed = false): void;

    /**
     * draws a polygon by connecting the given points, width is only used if polygon is not filled
     * @param array $points
     * @param color $color
     * @param bool $filled
     * @param float|int $width
     */
	public function drawPolygon(array $points, color $color, bool $filled = true, float $width = 2): void;
}
?>