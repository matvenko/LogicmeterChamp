var blink = 0;

function show_true_answer_animation(parameters){
	update_right_block();
	$("#test_conditions").html('');
	$("#show_true_answer_animation").css('display', 'block');
	$("#show_true_answer_animation_text").html(parameters.text);
	$("#show_true_answer_animation").animate({opacity: 1}, 400);
	setTimeout(function(){
		//$("#show_true_answer_animation").animate({opacity: 0}, 400);
		//$("#show_true_answer_animation").css('display', 'none');
		setTimeout("show_test()", 300);			
	}, 1200);
}

function show_test(){
	//*** empty container
	$("#container").val('');
	
	$("#spent_time_limit").val(0);
		
	if($("#text_image").length){
		show_text_image();
	}
	$(".container").val('');
	$("#answer_explain_box").css('display', 'none');
	$(".wrong_answer_box").css('display', 'none');
	$.get('body.php?module={{:module:}}&page=test_conditions&skill_id={{:skill_id:}}&text_id={{:text_id:}}')
		.done(function(data){
			//***** hide animation
			$("#show_true_answer_animation").animate({opacity: 0}, 400, function(){
				$("#show_true_answer_animation").css('display', 'none');

				$("#test_conditions").html(data);
				MathJax.Hub.Typeset();
			})
			
		})
}

function update_right_block(){
	$.get('body.php?module={{:module:}}&page=tests&skill_id={{:skill_id:}}&text_id={{:text_id:}}&action=update_right_block')
		.done(function(data){
			parameters = jQuery.parseJSON(data);
			
			$("#used_tests_amount").html(parameters.tests_amount);
			$("#remained_tests_amount").html(parseInt({{:champ_tests_amount:}}) - parseInt(parameters.tests_amount));
		
		})
}

function updateClock() {
	if(parseInt($("#spent_time_limit").val()) <= parseInt('{{:test_max_spent_time:}}') && parseInt($("#test_conditions").attr("data-finished")) !== 1){
		//***** blink ****
		$("#clock").removeClass('time_pause');
			
		var currentTime = parseInt($("#current_time").val());
		var currentHours = Math.floor(currentTime / 3600);
		var currentMinutes = Math.floor((currentTime - currentHours * 3600) / 60);
		var currentSeconds = (currentTime - currentHours * 3600) - (currentMinutes * 60);
			// Pad the minutes and seconds with leading zeros, if required
		currentMinutes = (currentMinutes < 10 ? "0" : "") + currentMinutes;
		currentSeconds = (currentSeconds < 10 ? "0" : "") + currentSeconds;
			// Convert the hours component to 12-hour format if needed
		currentHours = (currentHours > 24) ? currentHours - 24 : currentHours;
			// Convert an hours component of "0" to "12"
		currentHours = (currentHours < 10) ? '0'+currentHours : currentHours;
			// Compose the string for display
		var currentTimeString = currentHours + ":" + currentMinutes + ":"
				+ currentSeconds;
		
		//*** finish calback
		if(currentTime > 0){
			$("#current_time").val(currentTime - 1);
			$("#clock").html(currentTimeString);
			//**** spent_time
			$("#spent_time").val(parseInt($("#spent_time").val()) + 1);
			$("#spent_time_limit").val(parseInt($("#spent_time_limit").val()) + 1);
		}
		else if($("#current_time").val() == 0){
			$("#clock").html(currentTimeString);
			finish_test();
			$("#current_time").val(-1);
		}
	}
	else{
		if(parseInt(blink) == 0){
			$("#clock").addClass('time_pause');
			//blink = setInterval(function(){$('#clock').fadeOut(500).fadeIn(500)}, 1000);
		}		
	}
}
	

$("#answer_test_form").on('click', ".next_button", function(){
	$("html, body").animate({scrollTop: 0});
	show_test()
});

$("#answer_test_form").submit(function(){
	$("#answer_button").attr('disabled', 'disabled');
	post_call('answer_test', this);
});	

function popup_message(parameters){
	var magnificPopup = $.magnificPopup.instance;	
	$("input:submit").attr('disabled', false);
	$.magnificPopup.open({
		  items: {
		      src: 'body.php?module={{:module:}}&page=popup_message&type=popup&custom_style='+parameters.message_name+'_popup&message_name='+parameters.message_name+'&other='+parameters.other
		    } ,
		  type: 'ajax',
		  alignTop: true,
		  closeOnContentClick: false
		}, 0);
	
	if(parameters.message_name == 'go_and_register'){
		update_right_block();
	}
}

function finish_test(){
	if(parseInt($("#test_conditions").attr("data-finished")) !== 1){
		update_right_block();
		$.get('clear_post.php?module={{:module:}}&page=tests', {action: "finished_test"})
			.done(function(data){
				$("#test_conditions").html(data);
			})
	}
}

function finished_skill_right_block(){
	$("#active_skill").css('display', 'none');
	$("#finished_skill").css('display', 'block');
}