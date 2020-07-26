<!DOCTYPE html>
<html>
<head>
</head>
<body style="background-color: #e6e6e6">
<?php
require_once 'dataset.php';
require_once 'graphData.php';
require_once 'graphRenderedImage.php';
require_once 'graphSVGImage.php';
$config=array(
	"containerWidth" => 1280,
	"containerHeight" => 720,
	"leftPadding" => 10,
	"rightPadding" => 10,
	"topPadding" => 10,
	"bottomPadding" => 10,
	"colLabelRotation" => 45,
	"graphTitleFontSize" => 36,
	"symbolSize" => 14,
	"graphLineThickness"=>4,
	"generalFontSize" => 18,
	"yUnit" => '°C',
	"showLegend" => true
);
$graphData = new graphData('Rendered Image | vert. Bar - Graph (stacked)','test', null, 'darkNature.php', $config);
//$graphData->row_colors = array('#82F477','#96F7F2','#6196D7');
//$graphData->sec_row_colors = array('#82F477','#96F7F2','#6196D7');
$graphData->row_symbols = array('circle','triangle','square');
$graphData->addDataset(new dataset(0, array(26,15,10)));
$graphData->addDataset(new dataset(-1, array(40,5,7)));
$graphData->addDataset(new dataset(2, array(-10,6,10)));
$graphData->addDataset(new dataset(3, array(50,8.2,40)));
$graphData->addDataset(new dataset(12, array(80,6,15)));
$graphData->addDataset(new dataset(5, array(5,36,33)));
/*
 * vert. Bar - Graph (stacked)
 */
$graphImage = new graphRenderedImage($graphData);
$graphImage->drawVertBarGraph(true);
ob_start();
$graphImage->outputImg();
$binary = ob_get_clean();
echo '<center><table style=" table-layout: fixed; width: 100%" ><tr><td style="width: 50%;"><img src="data:image/png;base64,' . base64_encode($binary) . '" width="100%" height="auto"></td>';
$graphData->title = 'SVG | vert. Bar - Graph (stacked)';
$graphData->id = 'stackedVertBarTest';
$svgGraph = new graphSVGImage($graphData);
$svgGraph->drawVertBarGraph(true);
$svg = $svgGraph->getSVG();
echo '<td style="width: 50%;">'.$svg.'</td></tr>';
/*
 * vert. Bar - Graph (non stacked)
 */
$graphData->title = 'Rendered Image | vert. Bar - Graph (non stacked)';
$graphImage = new graphRenderedImage($graphData);
$graphImage->drawVertBarGraph();
ob_start();
$graphImage->outputImg();
$binary = ob_get_clean();
echo '<tr><td><img src="data:image/png;base64,'. base64_encode($binary) .'" width="100%" height="auto"></td>';
$graphData->title = 'SVG | vert. Bar - Graph (non stacked)';
$graphData->id = 'vertBarTest';
$svgGraph = new graphSVGImage($graphData);
$svgGraph->drawVertBarGraph();
$svg = $svgGraph->getSVG();
echo '<td>'.$svg.'</td></tr>';
/*
 * hor. Bar - Graph (stacked)
 */
$graphData->title = 'Rendered Image | hor. Bar - Graph (stacked)';
$graphImage = new graphRenderedImage($graphData);
$graphImage->drawHorBarGraph(true);
ob_start();
$graphImage->outputImg();
$binary = ob_get_clean();
echo '<tr><td><img src="data:image/png;base64,'. base64_encode($binary) .'" width="100%" height="auto"></td>';
$graphData->title = 'SVG | hor. Bar - Graph (stacked)';
$graphData->id = 'stackedHorBarTest';
$svgGraph = new graphSVGImage($graphData);
$svgGraph->drawHorBarGraph(true);
$svg = $svgGraph->getSVG();
echo '<td>'.$svg.'</td></tr>';
/*
 * hor. Bar - Graph (non stacked)
 */
$graphData->title = 'Rendered Image | hor. Bar - Graph (non stacked)';
$graphImage = new graphRenderedImage($graphData);
$graphImage->drawHorBarGraph();
ob_start();
$graphImage->outputImg();
$binary = ob_get_clean();
echo '<tr><td><img src="data:image/png;base64,'. base64_encode($binary) .'" width="100%" height="auto"></td>';
$graphData->title = 'SVG | hor. Bar - Graph (non stacked)';
$graphData->id = 'horBarTest';
$svgGraph = new graphSVGImage($graphData);
$svgGraph->drawHorBarGraph();
$svg = $svgGraph->getSVG();
echo '<td>'.$svg.'</td></tr>';
/*
 * Scatter - Graph
 */
$graphData->title = 'Rendered Image | Scatter - Graph';
$graphImage = new graphRenderedImage($graphData);
$graphImage->drawScatterGraph();
ob_start();
//header("Content-type: image/png");
$graphImage->outputImg();
$binary = ob_get_clean();
echo '<tr><td><img src="data:image/png;base64,'. base64_encode($binary) .'" width="100%" height="auto"></td>';
$graphData->title = 'SVG | Scatter - Graph';
$graphData->id = 'scatterTest';
$svgGraph = new graphSVGImage($graphData);
$svgGraph->drawScatterGraph();
$svg = $svgGraph->getSVG();
echo '<td>'.$svg.'</td></tr>';
/*
 * Line - Graph (not stacked)
 */
$graphData->title = 'Rendered Image | Line - Graph';
$graphImage = new graphRenderedImage($graphData);
$graphImage->drawLineGraph();
ob_start();
$graphImage->outputImg();
$binary = ob_get_clean();
echo '<tr><td><img src="data:image/png;base64,'. base64_encode($binary) .'" width="100%" height="auto"></td>';
$graphData->title = 'SVG | Line - Graph';
$graphData->id = 'lineTest';
$svgGraph = new graphSVGImage($graphData);
$svgGraph->drawLineGraph();
$svg = $svgGraph->getSVG();
echo '<td>'.$svg.'</td></tr>';

/*
 * Line - Graph (not stacked)
 */
$graphData->row_names = array('sun', 'kiwi', 'leaves');
$graphData->title = 'Rendered Image | Line - Graph + Legend';
$graphImage = new graphRenderedImage($graphData);
$graphImage->drawLineGraph(false,1);
ob_start();
$graphImage->outputImg();
$binary = ob_get_clean();
echo '<tr><td><img src="data:image/png;base64,'. base64_encode($binary) .'" width="100%" height="auto"></td>';
$graphData->title = 'SVG | Line - Graph + Legend';
$graphData->id = 'lineTest';
$svgGraph = new graphSVGImage($graphData);
$svgGraph->drawLineGraph(false, 1);
$svg = $svgGraph->getSVG();
echo '<td>'.$svg.'</td></tr>';

$graphData->row_names = null;
/*
 * Line - Graph (not stacked)
 */
$graphData->title = 'Rendered Image | Line - Graph';
$graphImage = new graphRenderedImage($graphData);
$graphImage->drawLineGraph(false,2);
ob_start();
$graphImage->outputImg();
$binary = ob_get_clean();
echo '<tr><td><img src="data:image/png;base64,'. base64_encode($binary) .'" width="100%" height="auto"></td>';
$graphData->title = 'SVG | Line - Graph';
$graphData->id = 'lineTest';
$svgGraph = new graphSVGImage($graphData);
$svgGraph->drawLineGraph(false, 2);
$svg = $svgGraph->getSVG();
echo '<td>'.$svg.'</td></tr>';

/*
 * Line - Graph (not stacked) (extra Y)
 */
$graphData->title = 'Rendered Image | Line - Graph (extra Y)';
$graphImage = new graphRenderedImage($graphData);
$graphImage->drawLineGraph(false,2, false,true);
ob_start();
$graphImage->outputImg();
$binary = ob_get_clean();
echo '<tr><td><img src="data:image/png;base64,'. base64_encode($binary) .'" width="100%" height="auto"></td>';
$graphData->title = 'SVG | Line - Graph';
$graphData->id = 'lineTest';
$svgGraph = new graphSVGImage($graphData);
$svgGraph->drawLineGraph();
$svg = $svgGraph->getSVG();
echo '<td>'.$svg.'</td></tr>';

/*
 * Line - Graph (count)
 */
$graphData->addDataset(new dataset("2019/12/19-16:37:39", array(80,6,15)));
$graphData->title = 'Rendered Image | Line - Graph (Y count)';
$graphImage = new graphRenderedImage($graphData);
$graphImage->drawLineGraph(false,0, true);
ob_start();
$graphImage->outputImg();
$binary = ob_get_clean();
echo '<tr><td><img src="data:image/png;base64,'. base64_encode($binary) .'" width="100%" height="auto"></td>';
$graphData->title = 'SVG | Line - Graph (Y Count)';

$graphData->id = 'stringLineTest';
$svgGraph = new graphSVGImage($graphData);
$svgGraph->drawLineGraph(false,0,true);
$svg = $svgGraph->getSVG();
echo '<td>'.$svg.'</td></tr>';

/*
 * Radar - Graph (not stacked)
 */

$graphData->datasets = array();
$graphData->addDataset(new dataset(0, array(26,15,10)));
$graphData->addDataset(new dataset(2, array(10,6,10)));
$graphData->addDataset(new dataset(3, array(50,8.2,40)));
$graphData->addDataset(new dataset(12, array(80,6,15)));
$graphData->addDataset(new dataset(5, array(5,36,33)));

$graphData->row_names = array('sun', 'kiwi', 'leaves');
$graphData->title = 'Rendered Image | Pie - Graph';
$graphImage = new graphRenderedImage($graphData);
$graphImage->drawVertBarGraph();
ob_start();
$graphImage->outputImg();
$binary = ob_get_clean();
echo '<tr><td><img src="data:image/png;base64,'. base64_encode($binary) .'" width="100%" height="auto"></td>';

$graphData->title = 'SVG | Netzdiagramm';
$graphData->id = 'radarTest';
$svgGraph = new graphSVGImage($graphData);
$svgGraph->drawRadarChart();
$svg = $svgGraph->getSVG();
echo '<td>'.$svg.'</td></tr>';

/*
 * Pie - Graph (not stacked)
 */
$graphData->clearDatasets();
$graphData->addDataset(new dataset(0, array(180,120,60),array('#FFCC68', '#999EFF','#FF6682')));
//$graphData->addDataset(new dataset(0, array(75),array('#999EFF')));
//$graphData->addDataset(new dataset(0, array(208),array()));

$graphData->title = 'Rendered Image | Pie - Graph';
$graphImage = new graphRenderedImage($graphData);
$graphImage->drawPieChart();
ob_start();
$graphImage->outputImg();
$binary = ob_get_clean();
echo '<tr><td><img src="data:image/png;base64,'. base64_encode($binary) .'" width="100%" height="auto"></td>';
$graphData->title = 'SVG | Pie - Graph';
$graphData->id = 'pieTest';
$svgGraph = new graphSVGImage($graphData);
$svgGraph->drawPieChart();
$svg = $svgGraph->getSVG();
echo '<td>'.$svg.'</td></tr>';

echo '</table></center>';
/*
 * Additional Info
 */
//print_r($graphData->getLimits());
//print_r($graphData->getXLabels(20));
//$graphImage->calcScaling();
?>
</body>
</html>