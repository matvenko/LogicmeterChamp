function add_formated_text(module, type, parameters, focusItem){
	$("#add_rich_text").attr('disabled', true);
	$.post( "post.php?module="+module, {'formated_text' : 1, 'parameters': parameters })
	.done(function(data){
		formated_text_code = parameters.edit_id == 0 ? '['+type+']'+data+'[/'+type+']' : '';
		$('#'+focusItem).insertAtCaret(formated_text_code);
		var magnificPopup = $.magnificPopup.instance; 
		magnificPopup.close();
	})
}

function draw_canvas(parameters){
	if(parameters.dynamic === 1){
		width = roundNumber(parameters.x_finish) - roundNumber(parameters.x_start);
		height = roundNumber(parameters.y_finish) - roundNumber(parameters.y_start);
		$("#width").val(width);
		$("#height").val(height);
		x_finish = roundNumber(parameters.x_finish);
		y_finish = roundNumber(parameters.y_finish);
	}
	else{
		width = roundNumber(parameters.width);
		height = roundNumber(parameters.height);
		x_finish = roundNumber(parameters.x_start) + width;
		y_finish = roundNumber(parameters.y_start) + height;
		$("#x_finish").val(x_finish);
		$("#y_finish").val(y_finish);
	}
	
	if(x_finish == roundNumber(parameters.x_start) && y_finish == roundNumber(parameters.y_start)) return;
	
	background = parameters.background == '' ? 'transparent' : parameters.background;
	stroke_color = parameters.color == '' ? 'black' : parameters.color;
	switch (parameters.type){
	case "rectangle": //otkutxedi
		$('#'+parameters.id).drawRect({
			 strokeStyle: stroke_color,
			 fillStyle: background,
			 strokeWidth: 2,
			 x: parseInt(parameters.x_start) + width/2, y: parseInt(parameters.y_start) + height / 2,
			 width: width,
			 height: height,
			 rotate: parameters.rotate
			});
		break;
	case "triangle": //samkutxedi
		triangle_type = parameters.triangle_type == "" ? "isosceles" : parameters.triangle_type;
		if(triangle_type == "isosceles"){//tolferda
			x_1 = parseInt(parameters.x_start) + width / 2;			
		}
		if(triangle_type == "rectangular"){
			x_1 = x_finish;
		}
		if(triangle_type == "common"){
			x_1 = parseInt(parameters.x_start) + width / 3;
		}
		y_1 = parseInt(parameters.y_start);
		x_2 = parseInt(parameters.x_start);
		y_2 = y_finish;
		x_3 = x_finish;
		y_3 = y_finish;
		
		if(triangle_type == "rounded"){
			x_1 = parseInt(parameters.x_start) + width / 2;
			$('#'+parameters.id).rotateCanvas({
				rotate: parameters.rotate,
				  x: parseInt(parameters.x_start) + width / 2, y: parseInt(parameters.y_start) + height / 2
			}).drawLine({
				strokeStyle: stroke_color,
				fillStyle: background,
				strokeWidth: 2,
				x1: x_1, y1: y_1,
				x2: x_2, y2: y_2,
				x3: x_3, y3: y_3
				}).restoreCanvas();
		}
		else{
			$('#'+parameters.id).rotateCanvas({
				rotate: parameters.rotate,
				  x: parseInt(parameters.x_start) + width / 2, y: parseInt(parameters.y_start) + height / 2
			}).drawLine({
				strokeStyle: stroke_color,
				fillStyle: background,
				strokeWidth: 2,
				x1: x_1, y1: y_1,
				x2: x_2, y2: y_2,
				x3: x_3, y3: y_3,
				x4: x_1, y4: y_1
				}).restoreCanvas();
		}
		break;
	case "ellipse":
		$('#'+parameters.id).drawEllipse({
			strokeStyle: stroke_color,
			fillStyle: background,
			strokeWidth: 2,
			x: parseInt(parameters.x_start) + width/2, y: parseInt(parameters.y_start) + height / 2,
			width: width, height: height,
			rotate: parameters.rotate
			});
		break;
	case "arc":
		start_degree = parameters.start_degree == '' ? 0 : parameters.start_degree;
		end_degree = parameters.end_degree == '' ? 90 : parameters.end_degree;
		if(parameters.start_degree == ''){
			$("#start_degree").val(start_degree);
		}
		if(parameters.end_degree == ''){
			$("#end_degree").val(end_degree);
		}
		$('#'+parameters.id).drawArc({
			strokeStyle: stroke_color,
			fillStyle: background,
			strokeWidth: 2,
			x: parseInt(parameters.x_start), y: y_finish,
			radius: Math.abs(height),
			start: start_degree, end: end_degree,
			rotate: parameters.rotate
			});
		break;
	case "slice":
		background = parameters.background == '' ? 'black' : parameters.background;
		start_degree = parameters.start_degree == '' ? 0 : parameters.start_degree;
		end_degree = parameters.end_degree == '' ? 90 : parameters.end_degree;
		if(parameters.start_degree == ''){
			$("#start_degree").val(start_degree);
		}
		if(parameters.end_degree == ''){
			$("#end_degree").val(end_degree);
		}
		if(parameters.background == ''){
			$("#background").val(1);
		}
		$('#'+parameters.id).drawSlice({
			  fillStyle: background,
			  x: parseInt(parameters.x_start), y: y_finish,
			  radius: Math.abs(height),
			  // start and end angles in degrees
			  start: start_degree, end: end_degree
			});
		break;
	case "polygon":
		sides = parameters.sides == '' ? 5 : parameters.sides;
		if(parameters.sides == ''){
			$("#sides").val(sides);
		}
		$('#'+parameters.id).drawPolygon({
			strokeStyle: stroke_color,
			fillStyle: background,
			strokeWidth: 2,
			x: parseInt(parameters.x_start) + width, y: parseInt(parameters.y_start) + height,
			radius: height,
			sides: sides,
			rotate: parameters.rotate
			});
		break;
	case "line":
		stroke_dash = parseInt(parameters.stroke_dash) == 1 ? [5] : false;
		$('#'+parameters.id).drawLine({
			strokeStyle: stroke_color,
			strokeWidth: 2,
			strokeDash: stroke_dash,
			x1: parseInt(parameters.x_start), y1: parseInt(parameters.y_start),
			x2: x_finish, y2: y_finish,
			rotate: parameters.rotate
			});
		break;
	case "text":
		font_size = parameters.font_size == '' ? 16 : parameters.font_size;
		text = parameters.text == '' ? 'Sample' : parameters.text;
		if(parameters.font_size == ''){
			$("#font_size").val(font_size);
		}
		$('#'+parameters.id).drawText({
			strokeStyle: stroke_color,
			fillStyle: stroke_color,
			strokeWidth: 1,
			x: roundNumber(parameters.x_start), y: roundNumber(parameters.y_start),
			fontSize: font_size+'px',
			fontFamily: 'Sylfaen',
			align: 'left',
			respectAlign: true,
			text: text,
			rotate: parameters.rotate
			});
		break;
	case "arrow":
		stroke_width = parameters.stroke_width == '' ? 5 : parameters.stroke_width;
		if(parameters.stroke_width == ''){
			$("#stroke_width").val(stroke_width);
		}
		if(parameters.arrow_type !== '1'){
			$('#'+parameters.id).drawLine({
				strokeStyle: stroke_color,
				strokeWidth: stroke_width,
				rounded: false,
				startArrow: true,			
				arrowRadius: parseInt(stroke_width) * 2,
				arrowAngle: 90,
				x1: parseInt(parameters.x_start), y1: parseInt(parameters.y_start),
				x2: x_finish, y2: y_finish,
				rotate: parameters.rotate
			});
		}
		else{
			height = height || 1;
			cx_1 = parseInt(roundNumber(parameters.x_start) + roundNumber(width)/ 4);
			cy_1 = parseInt(roundNumber(parameters.y_start) - roundNumber(height) * 10);
			cx_2 = parseInt(roundNumber(parameters.x_start) + roundNumber(width) * 3 / 4);
			cy_2 = parseInt(roundNumber(parameters.y_start) - roundNumber(height) * 4);
			
			$('#'+parameters.id).drawBezier({
				strokeStyle: stroke_color,
				strokeWidth: stroke_width,
				rounded: false,
				startArrow: true,			
				arrowRadius: parseInt(stroke_width) * 2,
				arrowAngle: 90,
				x1: parseInt(parameters.x_start), y1: parseInt(parameters.y_start),
				cx1: cx_1, cy1: cy_1,
				cx2: cx_2, cy2: cy_2,
				x2: x_finish, y2: y_finish,
				rotate: parameters.rotate
			});
		}
		break;
	case "cross":
	case "x":
	stroke_width = parameters.stroke_width == '' ? 3 : parameters.stroke_width;
	$('#'+parameters.id).drawLine({
		strokeStyle: stroke_color,
		strokeWidth: stroke_width,
		x1: parseInt(parameters.x_start), y1: parseInt(parameters.y_start),
		x2: x_finish, y2: y_finish,
		rotate: parameters.rotate
		});
	$('#'+parameters.id).drawLine({
		strokeStyle: stroke_color,
		strokeWidth: stroke_width,
		x1: parseInt(parameters.x_start) + width, y1: parseInt(parameters.y_start),
		x2: parseInt(parameters.x_start), y2: y_finish,
		rotate: parameters.rotate
		});
	break;
	case "coordinate_flat":
		negative = parseInt(parameters.negative) == 1 ? 1 : 0;
		size = parameters.size == '' ? 6 : parameters.size;
		start_point = negative == 1 ? -size / 2 : 0;
		dimention = size * 40;
		x_start = negative == 1 ? 20 + size / 2 * 40 : 20;
		y_start = negative == 1 ? dimention + 20 - size / 2 * 40 : dimention + 20;
		$("#canvas_preview").css('height', (dimention + 40) +'px');
		$("#canvas_preview").css('width', (dimention + 40) +'px');
		$("#canvas_height").val((dimention + 40));
		$("#canvas_width").val((dimention + 40));
		
		step = roundNumber(dimention / size);
		var n = 0;
		for(i = 0; i <= dimention; i += step){			
			$('#'+parameters.id).drawLine({
				strokeStyle: '#c0c0c0',
				strokeWidth: 1,	
				x1: 20, y1: (20 + dimention) - i,
				x2: (20 + dimention), y2: (20 + dimention) - i
			});
			$('#'+parameters.id).drawLine({
				strokeStyle: '#c0c0c0',
				strokeWidth: 1,	
				x1: 20 + i, y1: 20,
				x2: 20 + i, y2: (20 + dimention)
			});
			
			//**** numbers
			if(start_point + n !== 0){
				$('#'+parameters.id).drawText({
					fillStyle: stroke_color,
					strokeWidth: 0,
					x: x_start - 10, y: (20 + dimention) - i,
					fontSize: '12px',
					align: 'right',
					text: start_point + n
					});
				$('#'+parameters.id).drawText({
					fillStyle: stroke_color,
					strokeWidth: 0,
					x: (20 + i), y: (y_start + 10),
					fontSize: '12px',
					align: 'center',
					text: start_point + n
					});
			}
			n++;
		}
		$('#'+parameters.id).drawText({
			fillStyle: stroke_color,
			strokeWidth: 0,
			x: x_start - 10, y: (y_start + 10),
			fontSize: '12px',
			align: 'right',
			text: '0'
			});
		
		$('#'+parameters.id).drawLine({
			strokeStyle: stroke_color,
			strokeWidth: 2,
			startArrow: true,
			endArrow: negative == 1 ? true : false,
			arrowRadius: 6,
			arrowAngle: 60,
			x1: x_start, y1: 10,
			x2: x_start, y2: negative == 1 ? dimention + 30 : dimention + 20,
		});
		$('#'+parameters.id).drawLine({
			strokeStyle: stroke_color,
			strokeWidth: 2,
			startArrow: true,
			endArrow: negative == 1 ? true : false,
			arrowRadius: 6,
			arrowAngle: 60,
			x1: (30 + dimention), y1: (y_start),
			x2: negative == 1 ? 10 : 20, y2: (y_start)
		});
		
	break;
	}	
	
	
}

function coordinate_line(parameters){
	step = parseInt(parameters.step) == 0 ? 1 : roundNumber(1 / parseInt(parameters.step));
	$('#coordinate_line_'+parameters.n).drawLine({
		strokeStyle: parameters.line_color,
		strokeWidth: 4,
		rounded: true,
		startArrow: true,
		endArrow: true,
		arrowRadius: 15,
		arrowAngle: 50,
		x1: 10, y1: 17,
		x2: 51 * roundNumber(parameters.size) + 35, y2: 17
	});
	var x_start = 0;
	var numbers = '';
	var n = 0;
	var answer_n = 0;ones = 0; n = roundNumber(parameters.start_point);
	for(i = roundNumber(parameters.start_point); roundNumber(i) <= roundNumber(roundNumber(parameters.start_point) + roundNumber(parameters.size) * step - step) ; i += step){
		x_start += 51;
		i = roundNumber(i);
		$('#coordinate_line_'+parameters.n).drawLine({
			strokeStyle: parameters.arrow_color,
			strokeWidth: 4,
			rounded: true,
			x1: x_start, y1: 10,
			x2: x_start, y2: 24
		});
		left_position = i < 10 ? roundNumber(x_start - 4) : roundNumber(x_start - 7);
		if(roundNumber(i % 1) == 0 && parseInt(parameters.step) <= 1){
			point = i;
		}
		else{
			//ones = parseInt(i);
			ones = n > parseInt(parameters.step) ? roundNumber(ones) + 1 : ones;
			ones = ones == 0 ? '' : ones;
			n = n > parseInt(parameters.step) ? 1 : n;
			point = n == parseInt(parameters.step) ? ones + 1 : '$$'+ones+'\\frac{'+n+'}{'+parseInt(parameters.step)+'}$$'; 
		}
		n ++;
		if($.isArray(parameters.answers)){
			if($.inArray(point, parameters.answers) !== -1){
				point = parameters.answer_values[answer_n];
				answer_n ++;
			}
		}
		numbers += '<span style="position: absolute; left: '+(left_position - 21)+'px; top: 30px; width: 50px; text-align: center">' + point + '</span>';
	}
	$("#coordinate_line_numbers_"+parameters.n).html(numbers);
}

function show_added_test_amount(parameters){
	$("#add_algorithm_main").html('');
	$("#added_tests_info").css('display', "block");
	$("#added_tests_amount").html(parameters.test_amount);
}