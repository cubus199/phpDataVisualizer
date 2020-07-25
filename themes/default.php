<?php
	$defaultConfig=array(
		// container div
		"containerWidth" => 1280,					// width of the whole graphing space
		"containerHeight" => 720,					// height of the whole graphing space
		"containerBackgroundColor" => "#FFFFFF",	// background-color of the whole graphing space

		// grid
		"gridColor" => "#202020",					// color of the background gridColor
		"gridEnabled" => true,						// enabling the background-grid
		
		// spacings
		"leftPadding" => 20,						// padding
		"rightPadding" => 20,
		"topPadding" => 20,
		"bottomPadding" => 20,
		"colLabelRotation" => 90,					// rotation of the column labels
		"graphComponentSpacing"=>30,				// spacing between graphing-elements like bars of a bar-graph
		"graphSupComponentSpacing"=>15,				// spacing of sub-components of graphing-elements

		// legend
		"showLegend" => false,						// displaying the graphs legend
		"legendPosition" => "topRight",				// position, where the Legend should be placed
		"legendOpacity" => 0.7,						// opacity of the legend's background
		'legendBorderColor' => '#dbdbdb',			// color of the legend's boder

		// axes
		"xAxisLabel" => "",							// label of the x-axis
		"yAxisLabel" => "",							// label of the y-axis
		"axisThickness" => 3,						// thickness of the axes
		"axisColor" => "#3F3F3F",					// color of the axes

		// title
		"graphTitleFontSize" => 30,					// fontsize of the graphs title
		"graphTitleColor" => "#303030", 			// color of the graphs title
		"graphTitlePosition" => "center",			// position of the graphs title (left,center,right)
		"graphTitleSpacingFactor" => 1.4,			// Distance between Title and Graph

		// typography
		"generalFont"=>'FreeSans',					// font of everything
		"generalFontColor" => "#303030",			// font-color of all text except the title
		"generalFontSize" => 15,					// font-size of everything except the title

		// labels
		"xPrecision" => 1,							// number of decimal places on x-axis
		"xLabelCount" => 11,						// maximum number of labels at the x-axis
		"yPrecision" => 1,							// number of decimal places on y-axis
		"yLabelCount" => 5,							// maximum number of labels at the y-axis
		"xUnit" => '',								// Unit displayed besides the labels at the x-axis
		"yUnit" => '',								// Unit displayed besides the labels at the y-axis

		// graph
		"graphLineThickness"=>3,					// thickness of the line of line-graphs
		"symbolSize" => 10, 						// size of symbols e.g. scatter Graph
		"primaryColors" => array('#845EC2','#D65DB1','#FF6F91','#FF9671','#FFC75F','#F9F871'),
		"secondaryColors" => array('#845EC2','#D65DB1','#FF6F91','#FF9671','#FFC75F','#F9F871')
	);
?>