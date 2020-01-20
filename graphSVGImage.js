function pushBarValues(graphId, x_name, values, colors){
	var graph = document.getElementById(graphId);
	var xLabels = graph.getElementsByClassName('xLabel');
	var bars = graph.getElementsByClassName('bar');
	var presets = graph.getAttribute('graphframe').split(';');
	var i;
	for(i = 0; i < xLabels.length - 1; i++){
		xLabels[i].innerHTML = xLabels[i + 1].innerHTML;
	}
	xLabels[i].innerHTML = x_name;
	for(i = 0; i < bars.length - values.length; i += values.length){
		for(var j = 0; j < values.length; j++){
			bars[i+j].setAttribute('y', bars[i+j+values.length].getAttribute('y'));
			bars[i+j].setAttribute('height', bars[i+j+values.length].getAttribute('height'));
		}
	}
	var init = presets[3];
	var gHeight = presets[3]-presets[1];
	var yMax = presets[5];
	for(var j = 0; j < values.length; j++){
		var height = (values[j] / yMax) * gHeight;
		bars[i+j].setAttribute('y',  init -= height);
		bars[i+j].setAttribute('height', height);
		if(colors != null){
			bars[i+j].style.fill = colors[j];
		}
	}
}

function createDataset(x, values, colors = null, symbols = null){
	return {"x_name": x, "values": values, "colors": colors, "symbols": symbols};
}

function shiftDataset(graphId, dataset){
	var graph = document.getElementById(graphId);
	var frame = graph.getAttribute('graphframe').split(';');
	var graphConfig = JSON.parse(graph.getAttribute('graphconfig').replace(/'/g, '"'));
	var graphType = graph.getAttribute('graphtype');
	var presets = graph.getAttribute('presets').replace(/'/g, '"').split(';');
	var datasets = JSON.parse(graph.getAttribute('datasets').replace(/'/g, '"'));

	var xLabels = graph.getElementsByClassName('xLabel');
	var i;
	for(i = 0; i < xLabels.length - 1; i++){
		xLabels[i].innerHTML = xLabels[i + 1].innerHTML;
	}
	xLabels[i].innerHTML = dataset.x_name;

	switch(graphType){
		case 'lineGraph':
		case 'scatterGraph':
			var size = graphConfig.symbolSize;

			if(graphConfig.xType == 'string'){
				var points = graph.getElementsByClassName('scatterPoint');
				var lines = graph.getElementsByClassName('graphLine');
				for(var i = 0; i < points.length;){
					points[i].parentNode.removeChild(points[i]);
				}
				for(var i = 0; i < lines.length;){
					lines[i].parentNode.removeChild(lines[i]);
				}
				datasets.shift();
				datasets.push(dataset);

				var min = getMinValue(datasets);
				var max = getMaxValue(datasets);

				var yLabels = graph.getElementsByClassName('yLabel');
				for(var i = 0; i < yLabels.length; i++){
					yLabels[i].innerHTML = (min + i * (max - min) / (yLabels.length - 1)).toFixed(graphConfig.yPrecision);
				}

				var gHeight = frame[3]-frame[1];
				var gWidth = frame[2]-frame[0];

				var yScale = gHeight / calcLimits(min, max)[1];
				var xScale = 0;
				if(graphConfig.xType == 'numeric'){
					xScale = gWidth / calcLimits(min, max)[1];
				}else{
					xScale = gWidth / (datasets.length - 1);
				}

				for(var i = 0; i < datasets.length; i++){
					var x = 0;
					if(graphConfig.xType == 'numeric'){
						x = xScale * parseFloat(datasets[i].x_name) + parseFloat(frame[0]);
					}else{
						x = xScale * i + parseFloat(frame[0]);
					}
					for(var j = 0; j < datasets[i].values.length; j++){
						var y = frame[3] - yScale * parseFloat(datasets[i].values[j]);
						var color = presets[0].split(',')[j];
						var type = presets[2].split(',')[j];
						if(datasets[i].colors != null){
							color = datasets[i].colors[j];
						}
						if(datasets[i].symbols != null){
							type = datasets[i].symbols[j];
						}
						if(i < datasets.length - 1){
							drawLine(graphId, x, y, (graphConfig.xType == 'numeric'? xScale * parseFloat(datasets[i + 1].x_name) + parseFloat(frame[0]):xScale * (i+1) + parseFloat(frame[0])), frame[3] - yScale * parseFloat(datasets[i + 1].values[j]), presets[1].split(',')[j], graphConfig.graphLineThickness);
						}
						createPoint(graphId, x, y, color, size, type, datasets[i].x_name, datasets[i].values[j]);
					}
				}
			}
			writeDatasets(graphId, datasets);
			break;
		default:
			break;
	}
}

function getMinValue(datasets, stacked = false){
	min = 2147483647;
	const reducer = (accumulator, currentValue) => accumulator + currentValue;
	for(var i = 0; i < datasets.length; i++){
		if(stacked){
			if(datasets[i].values.reduce(reducer) < min){
				min = datasets[i].values.reduce(reducer);
			}
		}else{
			for(var j = 0; j < datasets[i].values.length; j++){
				if(datasets[i].values[j] < min){
					min = datasets[i].values[j];
				}
			}
		}
	}
	return min;
}

function getMaxValue(datasets, stacked = false){
	max = -2147483647;
	const reducer = (accumulator, currentValue) => accumulator + currentValue;
	for(var i = 0; i < datasets.length; i++){
		if(stacked){
			if(datasets[i].values.reduce(reducer) > max){
				max = datasets[i].values.reduce(reducer);
			}
		}else{
			for(var j = 0; j < datasets[i].values.length; j++){
				if(datasets[i].values[j] > max){
					max = datasets[i].values[j];
				}
			}
		}
	}
	return max;
}

function getDatasets(graphId){
	var graph = document.getElementById(graphId);
	return JSON.parse(graph.getAttribute('datasets').replace(/'/g, '"'));
}

function writeDatasets(graphId, datasets){
	var graph = document.getElementById(graphId);
	graph.setAttribute('datasets', JSON.stringify(datasets).replace(/"/g, '\''));
}

function yReScale(graphId, minValue, maxValue){
	var graph = document.getElementById(graphId);
	var graphConfig = JSON.parse(graph.getAttribute('graphconfig').replace(/'/g, '"'));
	var yLabels = graph.getElementsByClassName('yLabel');
	var presets = graph.getAttribute('graphframe').split(';');
	if(minValue < presets[4] || maxValue > presets[5]){ //only work if value is outside of current range
		var range = calcLimits(minValue, maxValue);
		presets[4] = range[0];
		presets[5] = range[1];
		for(var i = 0; i < yLabels.length; i++){
			yLabels[i].innerHTML = (range[0] + i * (range[1] - range[0]) / (yLabels.length - 1)).toFixed(graphConfig.yPrecision);
		}
		graph.setAttribute('graphframe', presets.join(';'));
		var graphType = graph.getAttribute('graphtype');
		switch(graphType){
			case 'scatterGraph':
			case 'lineGraph':
				var points = graph.getElementsByClassName('scatterPoint');
				for(var i = 0; i < points.length; i){
					if(graphConfig.xType == 'numeric'){
						createPoint(graphId, points[i].getAttribute('xval'), points[i].getAttribute('yval'), points[i].getAttribute('fill'), graphConfig.symbolSize, points[i].getAttribute('pointtype'));
					}else{
						createPoint(graphId, getPointPosition(points[i])[0], points[i].getAttribute('yval'), points[i].getAttribute('fill'), graphConfig.symbolSize, points[i].getAttribute('pointtype'), points[i].getAttribute('xval'));
					}
					points[i].parentNode.removeChild(points[i]);
				}
				break;
			defaut:
				break;
		}
	}
}

function getPointPosition(point){
	var pointType = point.getAttribute('pointtype');
	switch(pointType){
		case 'circle':
			return [parseFloat(point.getAttribute('cx')), parseFloat(point.getAttribute('cy'))];
			break;
		case 'square':
			console.log(point.getAttribute('x'));
			return [parseFloat(point.getAttribute('x')) + (point.getAttribute('width')/2), point.getAttribute('y')-(point.getAttribute('height') / 2)];
			break;
		case 'triangle':
			var polygon = point.getAttribute('points').split(' ');
			return[polygon[0].split(',')[0], polygon[1].split(',')[1] - polygon[0].split(',')[1]];
			break;
		case 'cross':
			var polygon = point.getAttribute('points').split(' ');
			return [polygon[0].split(',')[0], polygon[3].split(',')[1]];
			break;
	}
}

function drawLine(graphId, x1, y1, x2, y2, color, width){
	var graph = document.getElementById(graphId);
	var graphType = graph.getAttribute('graphtype');
	if(graphType == 'lineGraph'){
		var line = document.createElementNS("http://www.w3.org/2000/svg",'line');
		line.classList.add('graphLine');
		line.setAttribute('x1', x1);
		line.setAttribute('y1', y1);
		line.setAttribute('x2', x2);
		line.setAttribute('y2', y2);
		line.setAttribute('stroke-linecap', 'round');
		line.setAttribute('style', 'stroke: '+color+'; stroke-width: '+width);
		graph.appendChild(line);
	}
}

function createPoint(graphId, x, y, color, size, type = 'circle', xVal, yVal){
	var graph = document.getElementById(graphId);
	var graphType = graph.getAttribute('graphtype');
	if(graphType == 'scatterGraph' || graphType == 'lineGraph'){
		var xPos = x;
		var yPos = y;
		var point;
		switch(type){
			default:
			case 'circle':
				var point = document.createElementNS("http://www.w3.org/2000/svg",'circle');
				point.setAttribute('cx', xPos);
				point.setAttribute('cy', yPos);
				point.setAttribute('r', size / 2);
				point.setAttribute('pointtype', 'circle');
				break;
			case 'square':
				var point = document.createElementNS("http://www.w3.org/2000/svg",'rect');
				point.setAttribute('pointtype', 'square');
				point.setAttribute('width', size);
				point.setAttribute('height', size);
				point.setAttribute('x', xPos - (size / 2));
				point.setAttribute('y', yPos - (size / 2));
				break;
			case 'triangle':
				var point = document.createElementNS("http://www.w3.org/2000/svg",'polygon');
				point.setAttribute('pointtype', 'triangle');
				var triangle = [];	
				triangle[0] = xPos+','+(yPos - size / 4 * Math.sqrt(3));
				triangle[1] = (xPos - size / 2)+','+(yPos + size / 4 * Math.sqrt(3));
				triangle[2] = (xPos + size / 2)+','+(yPos + size / 4 * Math.sqrt(3));
				point.setAttribute('points', triangle.join(' '));
				break;
			case 'cross':
				var point = document.createElementNS("http://www.w3.org/2000/svg",'polygon');
				point.setAttribute('pointtype', 'cross');
				var c = 0.25 * size;
				var b = c * Math.sqrt(0.5);
				var d = size / 2;
				var a = d - b;
				var cross = [];	
				cross[0] = xPos+','+(yPos - b);
				cross[1] = (xPos + a)+','+(yPos - d);
				cross[2] = (xPos + d)+','+(yPos - a);
				cross[3] = (xPos + b)+','+yPos;
				cross[4] = (xPos + d)+','+(yPos + a);
				cross[5] = (xPos + a)+','+(yPos + d);
				cross[6] = xPos+','+(yPos + b);
				cross[7] = (xPos - a)+','+(yPos + d);
				cross[8] = (xPos - d)+','+(yPos + a);
				cross[9] = (xPos - b)+','+yPos;
				cross[10] = (xPos - d)+','+(yPos - a);
				cross[11] = (xPos - a)+','+(yPos - d);
				point.setAttribute('points', cross.join(' '));
				break;
		}
		point.setAttribute('fill', color);
		point.setAttribute('xval', x);
		point.setAttribute('yval', y);
		point.classList.add('scatterPoint');
		graph.appendChild(point);
	}
}

function calcLimits(min, max, shiftAxes = false){
	minVal = 0;
	maxVal = 0;
	if(max > 0 || shiftAxes){
		maxVal = max;
	}
	if(min < 0 || shiftAxes){
		minVal = min;
	}


	scope = Math.min(getDecimalShift(minVal), getDecimalShift(maxVal));
	minVal = Math.floor(minVal * Math.pow(10, scope));
	maxVal = Math.ceil(maxVal * Math.pow(10, scope));

	while(!(minVal == -1 || minVal % 10 == 0 || minVal % 5 == 0)){
		minVal--;
	}
	while(!(maxVal == 1|| maxVal % 10 == 0 || maxVal % 5 == 0)){
		maxVal++;
	}
	return [minVal / Math.pow(10, scope), maxVal / Math.pow(10, scope)];
}

function getDecimalShift(number){
	number = Math.abs(number);
	shift = 0;
	if(number < 1 && number > 0){
		while(number < 1){
			number *= 10;
			shift++;
		}
	}
	return shift;
}

function pad(num, size) {
    var s = num+"";
    while (s.length < size) s = "0" + s;
    return s;
}