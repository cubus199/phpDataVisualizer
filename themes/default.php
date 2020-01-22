<?php
	$defaultConfig=array(
		"containerWidth" => 1280,					// width of the whole graphing space
		"containerHeight" => 720,					// height of the whole graphing space
		"containerBackgroundColor" => "#FFFFFF",	// background-color of the whole graphing space
		"graphTitleFontSize" => 30,					// fontsize of the graphs title
		"graphTitleColor" => "#303030", 			// color of the graphs title
		"graphTitlePosition" => "center",			// position of the graphs title (left,center,right)
		"graphTitleSpacingFactor" => 1.4,			// Distance between Title and Graph
		"graphComponentSpacing"=>30,				// spacing between graphing-elements like bars of a bar-graph
		"graphSupComponentSpacing"=>15,				// spacing of sub-components of graphing-elements
		"graphLineThickness"=>3,					// thickness of the line of line-graphs
		"gridColor" => "#202020",					// color of the background gridColor
		"gridEnabled" => true,						// enabling the background-grid
		"axisThickness" => 3,						// thickness of the axes
		"axisColor" => "#3F3F3F",					// color of the axes
		"leftPadding" => 20,							// padding
		"rightPadding" => 20,
		"topPadding" => 20,
		"bottomPadding" => 20,
		"colLabelRotation" => 90,					// rotation of the column labels
		"showLegend" => false,						// displaying the graphs legend
		"xAxisLabel" => "",							// label of the x-axis
		"yAxisLabel" => "",							// label of the y-axis
		"generalFont"=>'FreeSans',					// font of everything
		"generalFontColor" => "#303030",			// font-color of all text except the title
		"generalFontSize" => 15,					// font-size of everything except the title
		"xPrecision" => 1,							// number of decimal places on x-axis
		"xLabelCount" => 11,						// maximum number of labels at the x-axis
		"yPrecision" => 1,							// number of decimal places on y-axis
		"yLabelCount" => 5,							// maximum number of labels at the y-axis
		"symbolSize" => 10, 						// size of symbols e.g. scatter Graph
		"xUnit" => '',								// Unit displayed besides the labels at the x-axis
		"yUnit" => '',								// Unit displayed besides the labels at the y-axis
		"primaryColors" => array('#845EC2','#D65DB1','#FF6F91','#FF9671','#FFC75F','#F9F871'),
		"secondaryColors" => array('#845EC2','#D65DB1','#FF6F91','#FF9671','#FFC75F','#F9F871')
	);
?>