<?php
global $out, $champ;

if($champ->is_participant() == false && post_int('answer_test') == 1){
	echo request_callback("ok_message", "", "reload");
	exit;
}
$champ->is_participant_end();

//***** uupdate_right_block ***
if(get('action') == "update_right_block"){
	$result = $champ->get_user_smart_point(get_int('skill_id'));
		
    echo json_encode($result);
    exit;
}

//****** finish test
if(get('action') == "finished_test"){
	$champ->set_finished_test_status();
	echo $champ->finished_test();
	exit;
}

//****** skip_test
if(get('action') == "skip_test"){
	$skip_test = current($_SESSION['champ']['skills']);
	array_shift($_SESSION['champ']['skills']);
	array_push($_SESSION['champ']['skills'], $skip_test);
	echo "ok";
	exit;
}

//****** answer the test *****
if(is_post('answer_test')){
    $test_id = $champ->test_id_decrypt(post('test_id'));
	$test_id = in_array($test_id, (array)$_SESSION['champ']['tests']) ? $test_id : 0;
	$test_info = $champ->test_info($test_id);
	$champ->set_spent_time();

	$query->where_vars['champ_date'] = $champ->config['start_date'];
	//**** check time and test amount
	$stage_info = $query->select_ar_sql("championship_children_stage_points", "*", "child_id = ".(int)$user_class->current_child_id." AND champ_date = '{{champ_date}}'");
    if((int)$stage_info['duration'] + $champ->spent_time >= (int)$champ->config['step_1_test_duration'] * 60 || (int)$stage_info['tests_amount'] == (int)$champ->config['tests_amount']){
		echo request_callback("ok", "", "finish_test", array());
    	exit;
	}
    
    if((int)$test_info['id'] == 0){
        echo request_callback("ok", "", "reload", array());
        exit;
    }

    //***** check if test exist
    if($query->record_exist("championship_children_tests", "champ_date = '{{champ_date}}' AND child_id = ".(int)$user_class->current_child_id." AND skill_id = ".(int)$test_info['skill_id']) == true){
    	unset($_SESSION['champ']['skills']);
    	echo request_callback("ok", "", "reload", array());
        exit;
    }

    //****** check answer
    $test_answers = $champ->test_answers($test_id, $test_info);
    $user_answers = $test_true_answers = array();
    
    $answer_amount = 0;
    foreach($test_answers as $i => $answer_info){
        if((int)$test_info['answer_type'] == 1){
            if((int)$test_info['multi_answer'] == 1){
                if(post_int('answer_'.$i) == 1){
                    $user_answers[$i] = $answer_info['answer']; //multi answer
                    $answer_amount = 1;
                }
            }
            elseif(post_int('answer') == (int)$i){
                $user_answers[$i] = $answer_info['answer']; //single answer
                $answer_amount = 1;
            }
            if($answer_info['true_answer'] == 1){
                $test_true_answers[$i] = $answer_info['answer'];
            }
        }
        else{
            $user_answers[$i] = post_admin('answer_'.$i);
            $answer_amount = post_admin('answer_'.$i) === false ? $answer_amount : $answer_amount + 1;
            $test_true_answers[$i] = $answer_info['answer'];
        }
    }
	
	if($answer_amount === 0 && post_int('no_answer') == 0){
        echo request_callback("ok", "", "popup_message", array("message_name" => "no_answer"));
        exit;
    }

    //**** decline multi click
    if((int)$_SESSION['champ']['tests'][$test_info['skill_id']] == 0) exit;
    unset($_SESSION['champ']['tests'][$test_info['skill_id']]);
   
    //****** check true answer ******* 
    $true_answer = $user_answers === $test_true_answers ? 1 : 0;   
    $point_info = $champ->set_user_smart_point($test_info['grade_id'], $test_info['skill_id'], $true_answer, $test_info['level']);

    $callback_func = "show_true_answer_animation";
    $out_data = array("n" => 1, "text" => _LOADING_NEXT_TEST);
    
    $champ->insert_user_test($test_info['grade_id'], $test_info['skill_id'], $test_id, $point_info, $user_answers, post('container'));
    
    //**** finish skill
    if((int)$point_info['tests_amount'] >= (int)$champ->config['tests_amount'] || (int)$point_info['duration'] >= (int)$champ->config['step_1_test_duration'] * 60){
    	$champ->set_finished_test_status();
		$callback_func = "finish_test";
    }
    
    //unset($_SESSION['math']['tests'][$test_info['skill_id']][$test_info['level']][$test_id]);
    array_shift($_SESSION['champ']['skills']);
    //********************
    
    $out_data['container'] = post('container');
    echo request_callback("ok", "", $callback_func, $out_data);
    exit;
}
//****************************

//***** javascript_functions
$replace_fields['javascript_functions'] = read_file("modules/".$module."/files/functions.js");

$replace_fields['module'] = $module;
$replace_fields['skill_id'] = get_int('skill_id');

//**** incorrect answer **
$replace_fields['_INCORRECT_ANSWER'] = _INCORRECT_ANSWER;
$replace_fields['_ANSWER_EXPLAIN'] = _ANSWER_EXPLAIN;
$replace_fields['_NEXT'] = _NEXT;
$replace_fields['_FINISHED_SKILL'] = _FINISHED_SKILL;
//************************

//****** right block
$smart_point = $champ->get_user_smart_point();
$replace_fields['champ_tests_amount'] = $champ->config['tests_amount'];
$replace_fields['_USED_TESTS'] = _USED_TESTS;
$replace_fields['_REMAINED_TIME'] = _REMAINED_TIME;
$replace_fields['_TESTS_AMOUNT'] = _TESTS_AMOUNT;
$replace_fields['_REMAINED_TEST'] = _REMAINED_TEST;
$replace_fields['_USED_TESTS'] = _USED_TESTS;
$replace_fields['used_tests_amount'] = (int)$smart_point['tests_amount'];
$replace_fields['remained_tests_amount'] = $replace_fields['champ_tests_amount'] - $replace_fields['used_tests_amount'];

$spent_time_info = $query->select_ar_sql("championship_children_stage_points", "duration, finished", "child_id = ".(int)$user_class->current_child_id." AND champ_date = '".$champ->config['start_date']."'");
$start_time = (int)$_SESSION['champ']['test_start_time'] == 0 ? time() : $_SESSION['champ']['test_start_time'];
$replace_fields['remained_seconds'] = $champ->config['step_1_test_duration'] * 60 - $spent_time_info['duration'] - time() + $start_time;
$replace_fields['remained_seconds'] = $replace_fields['remained_seconds'] < 0 || (int)$spent_time_info['finished'] == 1 ? 0 : $replace_fields['remained_seconds'];
$replace_fields['remained_time'] = gmdate("H:i:s", $replace_fields['remained_seconds']);

$replace_fields['test_max_spent_time'] = $champ->config['test_max_spent_time'];
//******************

$replace_fields['grade_tests_amount'] = 0;
		
//***** if skill finished
if((int)$user_class->current_user_id !== 0 && (int)$user_class->current_child_id !== 0 || (int)$user_class->current_user_id == 0){
	$templates->module_ignore_fields[] = "logicstar";
}
elseif((int)$user_class->current_user_id !== 0){
	$replace_fields['_FINISHED_SKILL'] = _CHILD_NOT_SELECTED;
}

if((int)$smart_point['smart_point'] == 100 || ((int)$user_class->current_user_id !== 0 && (int)$user_class->current_child_id == 0)){
	$replace_fields['active_skill_display'] =  "none";
	$replace_fields['finished_skill_display'] =  "block";
	$replace_fields['_CERTIFICATE'] = _CERTIFICATE;
	
	$award_info = $query->select_ar_sql("math_children_awards", "id", "award_id = 1 AND skill_id = ".get_int('skill_id')." AND child_id = ".(int)$user_class->current_child_id);
	$child_award_id = $award_info['id'];
	
	$replace_fields['child_award_id'] = $child_award_id;
}
else{
	$replace_fields['active_skill_display'] =  "block";
	$replace_fields['finished_skill_display'] =  "none";
}

//***** reset ****
$templates->add_ignore_field("math", "all", "reset");

$out = $templates->gen_module_html($replace_fields, "tests");
if((int)$_SESSION['champ']['test_start_time'] == 0){
	$champ->set_spent_time();
}